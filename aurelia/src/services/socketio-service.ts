import {DI, ILogger, resolve} from 'aurelia';
import type {Socket} from 'socket.io-client';
import {IHttpRequest, IHttpResponse, ITransport} from '../interfaces/api';
import {Transport} from '../enums/api';
import {IBleetConfiguration} from '../configure';

// io sera chargé à la demande via require()
let io: typeof import('socket.io-client').io | null = null;

function getSocketIo(): typeof import('socket.io-client').io {
    if (!io) {
        try {
            io = require('socket.io-client').io;
        } catch {
            throw new Error(
                'socket.io-client non installé. ' +
                'Installez-le avec : npm install socket.io-client'
            );
        }
    }
    return io;
}

export interface ISocketioService extends SocketioService {}
export const ISocketioService = DI.createInterface<ISocketioService>(
    'ISocketioService',
    (x) => x.singleton(SocketioService)
);

export class SocketioService implements ITransport {
    public readonly type = Transport.Socketio;
    private readonly timeout = 5000;
    private socket: Socket | null = null;
    private connected: boolean = false;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('SocketioService'),
        private readonly config: IBleetConfiguration = resolve(IBleetConfiguration),
    ) {
        this.logger.trace('constructor');
    }

    public isConnected(): boolean {
        return this.connected && this.socket !== null;
    }

    public isAvailable(): boolean {
        return this.isConnected();
    }

    public prepareRequest(ctx: IHttpRequest): IHttpRequest {
        return {
            ...ctx,
            data: {...ctx.data, ...ctx.pathParams}
        };
    }

    public connect(namespace: string = '/', options?: Record<string, any>): Promise<void> {
        const baseUrl = this.config.getBaseUrl(Transport.Socketio);
        const url = baseUrl + namespace;
        this.logger.trace('connect', url);

        if (this.socket !== null) {
            this.logger.warn('connect:already-connected');
            return Promise.resolve();
        }

        return new Promise((resolve, reject) => {
            const socketIo = getSocketIo();
            this.socket = socketIo(url, {
                transports: ['websocket'],
                ...options
            });

            this.socket.on('connect', () => {
                this.logger.trace('connect:success');
                this.connected = true;
                resolve();
            });

            this.socket.on('connect_error', (error: Error) => {
                this.logger.error('connect:error', error);
                this.connected = false;
                reject(error);
            });

            this.socket.on('disconnect', (reason: string) => {
                this.logger.trace('disconnect', reason);
                this.connected = false;
            });
        });
    }

    public disconnect(): void {
        this.logger.trace('disconnect');

        if (this.socket !== null) {
            this.socket.disconnect();
            this.socket = null;
            this.connected = false;
        }
    }

    public execute<T>(ctx: IHttpRequest, responseType?: string): Promise<IHttpResponse<T>> {
        this.logger.trace('execute', ctx.method, ctx.url);

        if (!this.isConnected() || this.socket === null) {
            return Promise.reject(new Error('Socket not connected'));
        }

        const channel = `${ctx.method.toLowerCase()}:${ctx.url}`;
        let data = ctx.data ?? {};

        // Convert FormData to plain object (Socket.io can't send FormData)
        if (data instanceof FormData) {
            const obj: Record<string, any> = {};
            data.forEach((value, key) => {
                // Skip File objects - can't be serialized for Socket.io
                if (!(value instanceof File)) {
                    obj[key] = value;
                }
            });
            data = obj;
        }

        return new Promise((resolve, reject) => {
            const timeoutId = setTimeout(() => {
                reject(new Error('Socket timeout'));
            }, this.timeout);

            this.socket!.emit(channel, data, (response: IHttpResponse<T>) => {
                clearTimeout(timeoutId);
                this.logger.trace('execute:response', channel, response);
                resolve(response);
            });
        });
    }
}
