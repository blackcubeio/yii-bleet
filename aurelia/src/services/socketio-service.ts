import {DI, ILogger, resolve} from 'aurelia';
import {IHttpRequest, IHttpResponse, ITransport} from '../interfaces/api';
import {Transport} from '../enums/api';
import {ISocketioManagerService} from './socketio-manager-service';

export interface ISocketioService extends SocketioService {}
export const ISocketioService = DI.createInterface<ISocketioService>(
    'ISocketioService',
    (x) => x.singleton(SocketioService)
);

/**
 * Transport WS de l'ApiService (requête/réponse via ack `method:url`). N'ouvre PAS son propre socket : il DÉLÈGUE
 * au SocketioManagerService — un seul socket socket.io par namespace, partagé avec le live (push), qui porte la
 * reconnexion et le jeton frais. Évite la double connexion (socket.io force une 2e connexion si le même namespace
 * est ré-ouvert via un autre Manager). Repris de Blips (3.6.0-beta4).
 */
export class SocketioService implements ITransport {
    public readonly type = Transport.Socketio;
    private namespace = '/';

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('SocketioService'),
        private readonly manager: ISocketioManagerService = resolve(ISocketioManagerService),
    ) {
        this.logger.trace('constructor');
    }

    public isConnected(): boolean {
        return this.manager.isConnected(this.namespace);
    }

    // Disponible si le socket est connecté OU si une connexion est EN VOL (on préfère attendre le WS). Sans
    // connexion demandée, rien n'est en vol → false → l'ApiService part direct en REST (fallback). Connexion voulue
    // mais WS échoué (ni connecté ni en vol) → false aussi → REST, sans relancer une connexion à chaque requête.
    public isAvailable(): boolean {
        return this.isConnected() || this.manager.isConnecting(this.namespace);
    }

    // Le WS matche le canal sur le TEMPLATE (`method:/url`, exact). On retire donc la query string de l'URL (sinon le
    // canal `get:/exchanges?tradable=1` ne matche jamais `get:/exchanges`) et on passe ses params dans le `data`
    // (body) — le serveur les y lit (ex. `body.tradable`). Les pathParams (`:id`) restent aussi dans le data.
    public prepareRequest(ctx: IHttpRequest): IHttpRequest {
        const [path, qs] = ctx.url.split('?');
        const query = qs
            ? Object.fromEntries(new URLSearchParams(qs))
            : {};
        return {
            ...ctx,
            url: path,
            data: {...ctx.data, ...ctx.pathParams, ...query}
        };
    }

    // Les options sont celles de socket.io-client (`auth` en fonction pour un jeton frais à chaque handshake) ;
    // le gestionnaire les garde et les rejoue à chaque reconnexion. Ne rejette jamais : le backoff prend la suite.
    public connect(namespace: string = '/', options?: Record<string, any>): Promise<void> {
        this.namespace = namespace;
        return this.manager.connect(namespace, options).then(() => undefined);
    }

    public disconnect(): void {
        this.manager.disconnect(this.namespace);
    }

    public execute<T>(ctx: IHttpRequest, _responseType?: string): Promise<IHttpResponse<T>> {
        this.logger.trace('execute', ctx.method, ctx.url);

        const channel = `${ctx.method.toLowerCase()}:${ctx.url}`;
        let data = ctx.data ?? {};

        // Socket.io ne peut pas transporter FormData → on aplatit en objet (les File sont ignorés).
        if (data instanceof FormData) {
            const obj: Record<string, any> = {};
            data.forEach((value, key) => {
                if (!(value instanceof File)) {
                    obj[key] = value;
                }
            });
            data = obj;
        }

        // Si la connexion est encore EN VOL (course shell/page au boot), on l'attend avant d'émettre — sinon on
        // partirait en REST alors qu'un WS arrive. Connexion échouée → reject → l'ApiService retombe sur REST.
        const ready = this.isConnected()
            ? Promise.resolve(true)
            : this.manager.connect(this.namespace);

        return ready.then((ok) =>
            ok === true
                ? this.manager.request<IHttpResponse<T>>(
                      this.namespace,
                      channel,
                      data,
                  )
                : Promise.reject(new Error('WS indisponible')),
        );
    }
}
