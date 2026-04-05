import { ILogger } from 'aurelia';
import { IHttpRequest, IHttpResponse, ITransport } from '../interfaces/api';
import { Transport } from '../enums/api';
import { IBleetConfiguration } from '../configure';
export interface ISocketioService extends SocketioService {
}
export declare const ISocketioService: import("@aurelia/kernel").InterfaceSymbol<ISocketioService>;
export declare class SocketioService implements ITransport {
    private readonly logger;
    private readonly config;
    readonly type = Transport.Socketio;
    private readonly timeout;
    private socket;
    private connected;
    constructor(logger?: ILogger, config?: IBleetConfiguration);
    isConnected(): boolean;
    isAvailable(): boolean;
    prepareRequest(ctx: IHttpRequest): IHttpRequest;
    connect(namespace?: string, options?: Record<string, any>): Promise<void>;
    disconnect(): void;
    execute<T>(ctx: IHttpRequest, responseType?: string): Promise<IHttpResponse<T>>;
}
//# sourceMappingURL=socketio-service.d.ts.map