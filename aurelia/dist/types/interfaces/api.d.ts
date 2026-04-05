import { Transport } from '../enums/api';
export interface ITransport {
    readonly type: Transport;
    isAvailable(): boolean;
    prepareRequest(ctx: IHttpRequest): IHttpRequest;
    execute<T>(ctx: IHttpRequest, responseType?: string): Promise<IHttpResponse<T>>;
}
export interface IHttpResponse<T = any> {
    statusCode: number;
    headers: Record<string, string>;
    body: T;
    pagination?: IPagination;
}
export interface IPagination {
    page: number;
    pageCount: number;
    pageSize: number;
    totalCount: number;
}
export interface IHttpRequest<T = any> {
    url: string;
    method: string;
    headers: Record<string, string>;
    data?: T;
    pathParams?: Record<string, any>;
}
export interface ICodec<TIn = any, TOut = any> {
    encode?: (ctx: IHttpRequest<TIn>) => Promise<IHttpRequest<TIn>>;
    decode?: (ctx: IHttpResponse<TOut>) => Promise<IHttpResponse<TOut>>;
}
export interface ICacheConfig {
    ttl?: number;
    storage?: 'memory' | 'session';
}
export interface IPaginationConfig {
    pageSize?: number;
    page?: number;
}
export interface IBuiltRequest {
    url: string;
    method: string;
    headers: Record<string, string>;
    data?: any;
    pathParams?: Record<string, any>;
    responseType: 'json' | 'text' | 'blob' | 'arraybuffer' | 'auto';
    inputCodecs: ICodec[];
    outputCodecs: ICodec[];
    pagination: IPaginationConfig | null;
    cache: ICacheConfig | null;
}
export interface ICacheEntry {
    data: any;
    expires: number | null;
    created: number;
}
//# sourceMappingURL=api.d.ts.map