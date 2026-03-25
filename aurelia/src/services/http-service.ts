import {DI, ILogger, resolve} from 'aurelia';
import {HttpClientConfiguration, IHttpClient} from '@aurelia/fetch-client';
import {IHttpRequest, IHttpResponse, ITransport} from '../interfaces/api';
import {Transport} from '../enums/api';
import {IBleetConfiguration} from '../configure';

export interface IHttpService extends HttpService {}
export const IHttpService = DI.createInterface<IHttpService>(
    'IHttpService',
    (x) => x.singleton(HttpService)
);

export class HttpService implements ITransport {
    public readonly type = Transport.Http;
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('HttpService'),
        private readonly httpClient: IHttpClient = resolve(IHttpClient),
        private readonly config: IBleetConfiguration = resolve(IBleetConfiguration),
    ) {
        this.logger.trace('constructor');
        this.httpClient.configure((config: HttpClientConfiguration) => {
            config.withDefaults({
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include'
            });
            return config;
        });
    }

    public isAvailable(): boolean {
        return true;
    }

    public prepareRequest(ctx: IHttpRequest): IHttpRequest {
        const baseUrl = this.config.getBaseUrl(Transport.Http);
        let url = baseUrl + ctx.url;
        const pathParams = ctx.pathParams ?? {};

        // FormData: don't try to extract path params from it, keep as-is
        if (ctx.data instanceof FormData) {
            // Substitute :params in URL from pathParams only
            const paramPattern = /:[a-zA-Z_][a-zA-Z0-9_-]*/g;
            const matches = ctx.url.match(paramPattern) ?? [];

            for (const match of matches) {
                const paramName = match.slice(1);
                if (paramName in pathParams) {
                    url = url.replace(match, encodeURIComponent(String(pathParams[paramName])));
                }
            }

            return {
                url,
                method: ctx.method,
                headers: ctx.headers,
                data: ctx.data
            };
        }

        const remainingData = {...ctx.data};

        // Substitute :params in URL (explicit > implicit) and remove from payload
        const paramPattern = /:[a-zA-Z_][a-zA-Z0-9_-]*/g;
        const matches = ctx.url.match(paramPattern) ?? [];

        for (const match of matches) {
            const paramName = match.slice(1);
            const value = paramName in pathParams ? pathParams[paramName] : remainingData[paramName];
            url = url.replace(match, encodeURIComponent(String(value)));
            delete remainingData[paramName];
        }

        return {
            url,
            method: ctx.method,
            headers: ctx.headers,
            data: remainingData
        };
    }

    public execute<T>(
        ctx: IHttpRequest,
        responseType: 'json' | 'text' | 'blob' | 'arraybuffer' | 'auto' = 'json'
    ): Promise<IHttpResponse<T>> {
        this.logger.trace('execute', ctx.method, ctx.url);

        const hasBody = ['POST', 'PATCH', 'PUT', 'DELETE'].includes(ctx.method.toUpperCase());
        const headers = { ...ctx.headers };

        const init: RequestInit = {
            method: ctx.method.toUpperCase(),
        };

        if (hasBody && ctx.data) {
            if (ctx.data instanceof FormData) {
                // FormData: don't set Content-Type, browser will set it with boundary
                delete headers['Content-Type'];
                init.body = ctx.data;
            } else if (Object.keys(ctx.data).length > 0) {
                init.body = JSON.stringify(ctx.data);
            }
        }

        init.headers = headers;

        return this.httpClient.fetch(ctx.url, init)
            .then((response) => this.parseResponse<T>(response, responseType));
    }

    private parseResponse<T>(response: Response, responseType: string): Promise<IHttpResponse<T>> {
        const headers: Record<string, string> = {};
        response.headers.forEach((value, key) => {
            headers[key] = value;
        });

        const effectiveType = responseType === 'auto'
            ? this.detectResponseType(response.headers.get('Content-Type'))
            : responseType;

        return this.parseBody<T>(response, effectiveType)
            .then((body) => ({
                statusCode: response.status,
                headers,
                body
            }));
    }

    private detectResponseType(contentType: string | null): 'json' | 'text' | 'blob' {
        if (!contentType) {
            return 'text';
        }

        const ct = contentType.toLowerCase();

        if (ct.includes('application/json')) {
            return 'json';
        }

        if (ct.startsWith('text/')) {
            return 'text';
        }

        if (ct.includes('application/')) {
            return 'blob';
        }

        return 'text';
    }

    private parseBody<T>(response: Response, responseType: string): Promise<T> {
        switch (responseType) {
            case 'json':
                return response.json();
            case 'text':
                return response.text() as Promise<T>;
            case 'blob':
                return response.blob() as Promise<T>;
            case 'arraybuffer':
                return response.arrayBuffer() as Promise<T>;
            default:
                return response.json();
        }
    }
}