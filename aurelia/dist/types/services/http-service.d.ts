import { ILogger } from 'aurelia';
import { IHttpClient } from '@aurelia/fetch-client';
import { IHttpRequest, IHttpResponse, ITransport } from '../interfaces/api';
import { Transport } from '../enums/api';
import { IBleetConfiguration } from '../configure';
export interface IHttpService extends HttpService {
}
export declare const IHttpService: import("@aurelia/kernel").InterfaceSymbol<IHttpService>;
export declare class HttpService implements ITransport {
    private readonly logger;
    private readonly httpClient;
    private readonly config;
    readonly type = Transport.Http;
    constructor(logger?: ILogger, httpClient?: IHttpClient, config?: IBleetConfiguration);
    isAvailable(): boolean;
    prepareRequest(ctx: IHttpRequest): IHttpRequest;
    execute<T>(ctx: IHttpRequest, responseType?: 'json' | 'text' | 'blob' | 'arraybuffer' | 'auto'): Promise<IHttpResponse<T>>;
    private parseResponse;
    private detectResponseType;
    private parseBody;
}
//# sourceMappingURL=http-service.d.ts.map