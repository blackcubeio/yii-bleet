import { DI, IContainer } from "aurelia";
import { InterfaceSymbol } from "@aurelia/kernel";
import {Transport, TransportConfig, TransportEntry, TransportWithConfig} from './enums/api';
import {ITransport} from './interfaces/api';

export interface CsrfConfig {
    enabled: boolean;
    metaName: string;
    headerName: string;
}

export interface ConfigInterface {
    transports?: TransportConfig;
    baseUrl?: string;
    csrf?: CsrfConfig;
}

export interface IBleetConfiguration extends Configure {}
export const IBleetConfiguration = DI.createInterface<IBleetConfiguration>(
    'IBleetConfiguration',
    x => x.singleton(Configure)
);

export class Configure {
    protected _config: ConfigInterface = {
        transports: Transport.Http,
        csrf: {
            enabled: true,
            metaName: 'csrf',
            headerName: 'X-CSRF-Token',
        }
    };

    private _container: IContainer | null = null;
    private _transportInterfaces = new Map<Transport, InterfaceSymbol<ITransport>>();

    setContainer(container: IContainer): void {
        this._container = container;
    }

    getContainer(): IContainer | null {
        return this._container;
    }

    registerTransportInterface(type: Transport, iface: InterfaceSymbol<ITransport>): void {
        this._transportInterfaces.set(type, iface);
    }

    getCsrfConfig(): CsrfConfig {
        return this._config.csrf ?? { enabled: true, metaName: 'csrf', headerName: 'X-CSRF-Token' };
    }

    getConfig(): ConfigInterface {
        return this._config;
    }

    get<T = unknown>(key: string): T {
        return this._config[key] as T;
    }

    set<T>(key: string, val: T): T {
        this._config[key] = val;
        return val;
    }

    getTransports(): Transport[] {
        const cfg = this._config.transports ?? Transport.Http;
        const entries: TransportEntry[] = Array.isArray(cfg) ? cfg : [cfg];
        return entries.map(entry => this.isTransportWithConfig(entry) ? entry.type : entry);
    }

    getBaseUrl(transport: Transport): string {
        const cfg = this._config.transports ?? Transport.Http;
        const entries: TransportEntry[] = Array.isArray(cfg) ? cfg : [cfg];
        const entry = entries.find(e =>
            this.isTransportWithConfig(e) ? e.type === transport : e === transport
        );
        if (entry && this.isTransportWithConfig(entry) && entry.baseUrl !== undefined) {
            return entry.baseUrl;
        }
        return this._config.baseUrl ?? '';
    }

    private isTransportWithConfig(entry: TransportEntry): entry is TransportWithConfig {
        return typeof entry === 'object' && 'type' in entry;
    }

    getTransport(type: Transport): ITransport | null {
        if (!this._container) {
            return null;
        }

        // HTTP : déjà enregistré
        const iface = this._transportInterfaces.get(type);
        if (iface) {
            return this._container.get(iface);
        }

        // Socketio : require() synchrone à la demande
        if (type === Transport.Socketio) {
            try {
                const { ISocketioService } = require('./services/socketio-service');
                this._transportInterfaces.set(type, ISocketioService);
                return this._container.get(ISocketioService);
            } catch (e) {
                throw new Error(
                    'Transport Socketio configuré mais socket.io-client non installé. ' +
                    'Installez-le avec : npm install socket.io-client'
                );
            }
        }

        return null;
    }

    getAvailableTransports(): ITransport[] {
        return this.getTransports()
            .map(t => this.getTransport(t))
            .filter((t): t is ITransport => t !== null);
    }
}