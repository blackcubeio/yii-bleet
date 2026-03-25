import { ILogger } from 'aurelia';
import { IBuiltRequest, ICacheConfig, ICodec, IHttpResponse, IPaginationConfig } from '../interfaces/api';
import { IBleetConfiguration } from '../configure';
export interface IApiService extends ApiService {
}
export declare const IApiService: import("@aurelia/kernel").InterfaceSymbol<IApiService>;
export declare class ApiService {
    private readonly logger;
    private readonly config;
    private static readonly PARAM_PATTERN;
    private memoryCache;
    constructor(logger?: ILogger, config?: IBleetConfiguration);
    url(path: string, params?: Record<string, any>): ApiRequestBuilder;
    /**
     * Simple HTML fetch for AJAX dialogs (modal/drawer)
     * Returns full response for status code checking
     */
    fetchHtml(url: string): Promise<IHttpResponse<string>>;
    execute<T>(request: IBuiltRequest): Promise<IHttpResponse<T>>;
    private executeWithFallback;
    private validateParams;
    private genCacheKey;
    private getFromCache;
    private storeInCache;
}
export declare class ApiRequestBuilder {
    private api;
    private _url;
    private _method;
    private _data;
    private _pathParams;
    private _queryString;
    private _headers;
    private _inputCodecs;
    private _outputCodecs;
    private _pagination;
    private _cache;
    private _contentType;
    private _accept;
    private _responseType;
    constructor(api: ApiService, path: string, pathParams?: Record<string, any>);
    queryString(params: Record<string, any>): this;
    fromJson<T>(data?: T): this;
    fromForm<T>(data?: T): this;
    fromMultipart(data?: FormData): this;
    toJson(): this;
    toXls(): this;
    toBlob(): this;
    toText(): this;
    toArrayBuffer(): this;
    withInputCodec(codec: ICodec): this;
    withOutputCodec(codec: ICodec): this;
    withPagination(config?: IPaginationConfig): this;
    withCache(config?: ICacheConfig): this;
    get<T>(): Promise<IHttpResponse<T>>;
    post<T>(): Promise<IHttpResponse<T>>;
    put<T>(): Promise<IHttpResponse<T>>;
    patch<T>(): Promise<IHttpResponse<T>>;
    delete<T>(): Promise<IHttpResponse<T>>;
    request<T>(verb: string): Promise<IHttpResponse<T>>;
    private build;
    private appendQueryString;
}
//# sourceMappingURL=api-service.d.ts.map