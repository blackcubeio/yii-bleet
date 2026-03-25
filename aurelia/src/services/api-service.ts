import {DI, ILogger, resolve} from 'aurelia';
import {
    IBuiltRequest,
    ICacheConfig,
    ICacheEntry,
    ICodec,
    IHttpRequest,
    IHttpResponse,
    IPaginationConfig,
    ITransport
} from '../interfaces/api';
import {IBleetConfiguration} from '../configure';
import {CsrfCodec} from '../codecs/csrf-codec';

export interface IApiService extends ApiService {}
export const IApiService = /*@__PURE__*/DI.createInterface<IApiService>(
    'IApiService',
    (x) => x.singleton(ApiService)
);

export class ApiService {
    private static readonly PARAM_PATTERN = /:[a-zA-Z_][a-zA-Z0-9_-]*/g;
    private memoryCache = new Map<string, ICacheEntry>();

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('ApiService'),
        private readonly config: IBleetConfiguration = resolve(IBleetConfiguration),
    ) {
        this.logger.trace('constructor');
    }

    public url(path: string, params?: Record<string, any>): ApiRequestBuilder {
        return new ApiRequestBuilder(this, path, params);
    }

    /**
     * Simple HTML fetch for AJAX dialogs (modal/drawer)
     * Returns full response for status code checking
     */
    public async fetchHtml(url: string): Promise<IHttpResponse<string>> {
        this.logger.trace('fetchHtml', url);
        return this.url(url).toText().get<string>();
    }

    public execute<T>(request: IBuiltRequest): Promise<IHttpResponse<T>> {
        this.logger.trace('execute', request.method, request.url);

        // 1. Validate path params are present in pathParams or data
        this.validateParams(request.url, request.pathParams, request.data);

        // 2. Build request context
        const initialCtx: IHttpRequest = {
            url: request.url,
            method: request.method,
            headers: request.headers,
            data: request.data,
            pathParams: request.pathParams
        };

        // 3. Build codec chain : CSRF auto + user codecs
        const csrfConfig = this.config.getCsrfConfig();
        const allInputCodecs: ICodec[] = [];
        if (csrfConfig.enabled) {
            allInputCodecs.push(CsrfCodec.fromConfig(csrfConfig));
        }
        allInputCodecs.push(...request.inputCodecs);

        // 4. Apply input codecs (encode) — chaîne de promesses
        const ctxPromise = allInputCodecs.reduce(
            (promise, codec) => codec.encode ? promise.then((ctx) => codec.encode!(ctx)) : promise,
            Promise.resolve(initialCtx)
        );

        return ctxPromise.then((ctx) => {
            // 4. Check cache
            if (request.cache) {
                const cached = this.getFromCache<T>(request, ctx);
                if (cached) {
                    this.logger.trace('execute:cache-hit');
                    return Promise.resolve(cached);
                }
            }

            // 5. Execute via transport avec fallback
            return this.executeWithFallback<T>(ctx, request.responseType)
                .then((response) => {
                    // 6. Apply output codecs (decode)
                    return request.outputCodecs.reduce(
                        (promise, codec) => codec.decode ? promise.then((r) => codec.decode!(r)) : promise,
                        Promise.resolve(response)
                    );
                })
                .then((response) => {
                    // 7. Store in cache
                    if (request.cache) {
                        this.storeInCache(request, ctx, response);
                    }
                    return response;
                });
        });
    }

    private executeWithFallback<T>(
        ctx: IHttpRequest,
        responseType: 'json' | 'text' | 'blob' | 'arraybuffer' | 'auto'
    ): Promise<IHttpResponse<T>> {
        const transports = this.config.getAvailableTransports();

        if (transports.length === 0) {
            return Promise.reject(new Error('No transport available'));
        }

        const tryTransport = (index: number, lastError: Error | null): Promise<IHttpResponse<T>> => {
            if (index >= transports.length) {
                return Promise.reject(lastError ?? new Error('All transports failed'));
            }

            const transport: ITransport = transports[index];

            if (!transport.isAvailable()) {
                this.logger.trace('execute:transport-unavailable', transport.type);
                return tryTransport(index + 1, lastError);
            }

            this.logger.trace('execute:trying', transport.type);

            const preparedCtx = transport.prepareRequest(ctx);

            return transport.execute<T>(preparedCtx, responseType)
                .catch((error: Error) => {
                    this.logger.warn('execute:transport-failed', transport.type, error);
                    return tryTransport(index + 1, error);
                });
        };

        return tryTransport(0, null);
    }

    private validateParams(url: string, pathParams?: Record<string, any>, data?: Record<string, any>): void {
        const matches = url.match(ApiService.PARAM_PATTERN) ?? [];
        for (const match of matches) {
            const paramName = match.slice(1);
            const inPathParams = pathParams !== undefined && paramName in pathParams;
            const inData = data !== undefined && paramName in data;
            if (!inPathParams && !inData) {
                throw new Error(`Missing path param '${match}'`);
            }
        }
    }

    private genCacheKey(request: IBuiltRequest, ctx: IHttpRequest): string {
        const parts = [
            request.method,
            ctx.url,
            JSON.stringify(ctx.data ?? {}),
            request.pagination?.page ?? 0
        ];
        return 'api-cache:' + btoa(parts.join('|')).slice(0, 24);
    }

    private getFromCache<T>(request: IBuiltRequest, ctx: IHttpRequest): IHttpResponse<T> | null {
        const key = this.genCacheKey(request, ctx);
        const storage = request.cache?.storage ?? 'session';

        let entry: ICacheEntry | null = null;

        if (storage === 'session') {
            const raw = sessionStorage.getItem(key);
            entry = raw ? JSON.parse(raw) : null;
        } else {
            entry = this.memoryCache.get(key) ?? null;
        }

        if (!entry) {
            return null;
        }

        // Check TTL
        if (entry.expires && Date.now() > entry.expires) {
            if (storage === 'session') {
                sessionStorage.removeItem(key);
            } else {
                this.memoryCache.delete(key);
            }
            return null;
        }

        return entry.data;
    }

    private storeInCache(request: IBuiltRequest, ctx: IHttpRequest, response: IHttpResponse): void {
        const key = this.genCacheKey(request, ctx);
        const storage = request.cache?.storage ?? 'session';
        const ttl = request.cache?.ttl;

        const entry: ICacheEntry = {
            data: response,
            expires: ttl ? Date.now() + (ttl * 1000) : null,
            created: Date.now()
        };

        if (storage === 'session') {
            sessionStorage.setItem(key, JSON.stringify(entry));
        } else {
            this.memoryCache.set(key, entry);
        }
    }
}

export class ApiRequestBuilder {
    private _url: string;
    private _method: string = 'GET';
    private _data: any;
    private _pathParams: Record<string, any> | undefined;
    private _queryString: Record<string, any> = {};
    private _headers: Record<string, string> = {};
    private _inputCodecs: ICodec[] = [];
    private _outputCodecs: ICodec[] = [];
    private _pagination: IPaginationConfig | null = null;
    private _cache: ICacheConfig | null = null;
    private _contentType: string = 'application/json';
    private _accept: string = 'application/json';
    private _responseType: 'json' | 'text' | 'blob' | 'arraybuffer' | 'auto' = 'auto';

    constructor(
        private api: ApiService,
        path: string,
        pathParams?: Record<string, any>
    ) {
        this._url = path;
        this._pathParams = pathParams;
    }

    // Query string
    public queryString(params: Record<string, any>): this {
        this._queryString = {...this._queryString, ...params};
        return this;
    }

    // Format entrée
    public fromJson<T>(data?: T): this {
        this._contentType = 'application/json';
        if (data !== undefined) {
            this._data = data;
        }
        return this;
    }

    public fromForm<T>(data?: T): this {
        this._contentType = 'application/x-www-form-urlencoded';
        if (data !== undefined) {
            this._data = data;
        }
        return this;
    }

    public fromMultipart(data?: FormData): this {
        this._contentType = 'multipart/form-data';
        if (data !== undefined) {
            this._data = data;
        }
        return this;
    }

    // Format sortie
    public toJson(): this {
        this._accept = 'application/json';
        this._responseType = 'json';
        return this;
    }

    public toXls(): this {
        this._accept = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        this._responseType = 'blob';
        return this;
    }

    public toBlob(): this {
        this._accept = 'application/octet-stream';
        this._responseType = 'blob';
        return this;
    }

    public toText(): this {
        this._accept = 'text/plain';
        this._responseType = 'text';
        return this;
    }

    public toArrayBuffer(): this {
        this._accept = 'application/octet-stream';
        this._responseType = 'arraybuffer';
        return this;
    }

    // Codecs
    public withInputCodec(codec: ICodec): this {
        this._inputCodecs.push(codec);
        return this;
    }

    public withOutputCodec(codec: ICodec): this {
        this._outputCodecs.push(codec);
        return this;
    }

    // Pagination & Cache
    public withPagination(config?: IPaginationConfig): this {
        this._pagination = {pageSize: 20, ...config};
        return this;
    }

    public withCache(config?: ICacheConfig): this {
        this._cache = {storage: 'session', ...config};
        return this;
    }

    // Execution
    public get<T>(): Promise<IHttpResponse<T>> {
        this._method = 'GET';
        return this.api.execute<T>(this.build());
    }

    public post<T>(): Promise<IHttpResponse<T>> {
        this._method = 'POST';
        return this.api.execute<T>(this.build());
    }

    public put<T>(): Promise<IHttpResponse<T>> {
        this._method = 'PUT';
        return this.api.execute<T>(this.build());
    }

    public patch<T>(): Promise<IHttpResponse<T>> {
        this._method = 'PATCH';
        return this.api.execute<T>(this.build());
    }

    public delete<T>(): Promise<IHttpResponse<T>> {
        this._method = 'DELETE';
        return this.api.execute<T>(this.build());
    }

    public request<T>(verb: string): Promise<IHttpResponse<T>> {
        this._method = verb.toUpperCase();
        return this.api.execute<T>(this.build());
    }

    private build(): IBuiltRequest {
        return {
            url: this.appendQueryString(this._url),
            method: this._method,
            headers: {
                'Content-Type': this._contentType,
                'Accept': this._accept,
                ...this._headers
            },
            data: this._data,
            pathParams: this._pathParams,
            responseType: this._responseType,
            inputCodecs: this._inputCodecs,
            outputCodecs: this._outputCodecs,
            pagination: this._pagination,
            cache: this._cache
        };
    }

    private appendQueryString(url: string): string {
        if (Object.keys(this._queryString).length === 0) {
            return url;
        }

        const params = new URLSearchParams();
        for (const [key, value] of Object.entries(this._queryString)) {
            if (value !== undefined && value !== null) {
                params.append(key, String(value));
            }
        }

        const qs = params.toString();
        if (!qs) {
            return url;
        }

        return url.includes('?') ? `${url}&${qs}` : `${url}?${qs}`;
    }
}