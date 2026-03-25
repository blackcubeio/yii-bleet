'use strict';

var aurelia = require('aurelia');
var fetchClient = require('@aurelia/fetch-client');
var Resumable = require('resumablejs');
var Quill = require('quill');

exports.Transport = void 0;
(function (Transport) {
    Transport["Socketio"] = "socketio";
    Transport["Http"] = "http";
})(exports.Transport || (exports.Transport = {}));

const IBleetConfiguration = aurelia.DI.createInterface('IBleetConfiguration', x => x.singleton(Configure));
class Configure {
    _config = {
        transports: exports.Transport.Http,
        csrf: {
            enabled: true,
            metaName: 'csrf',
            headerName: 'X-CSRF-Token',
        }
    };
    _container = null;
    _transportInterfaces = new Map();
    setContainer(container) {
        this._container = container;
    }
    getContainer() {
        return this._container;
    }
    registerTransportInterface(type, iface) {
        this._transportInterfaces.set(type, iface);
    }
    getCsrfConfig() {
        return this._config.csrf ?? { enabled: true, metaName: 'csrf', headerName: 'X-CSRF-Token' };
    }
    getConfig() {
        return this._config;
    }
    get(key) {
        return this._config[key];
    }
    set(key, val) {
        this._config[key] = val;
        return val;
    }
    getTransports() {
        const cfg = this._config.transports ?? exports.Transport.Http;
        const entries = Array.isArray(cfg) ? cfg : [cfg];
        return entries.map(entry => this.isTransportWithConfig(entry) ? entry.type : entry);
    }
    getBaseUrl(transport) {
        const cfg = this._config.transports ?? exports.Transport.Http;
        const entries = Array.isArray(cfg) ? cfg : [cfg];
        const entry = entries.find(e => this.isTransportWithConfig(e) ? e.type === transport : e === transport);
        if (entry && this.isTransportWithConfig(entry) && entry.baseUrl !== undefined) {
            return entry.baseUrl;
        }
        return this._config.baseUrl ?? '';
    }
    isTransportWithConfig(entry) {
        return typeof entry === 'object' && 'type' in entry;
    }
    getTransport(type) {
        if (!this._container) {
            return null;
        }
        // HTTP : déjà enregistré
        const iface = this._transportInterfaces.get(type);
        if (iface) {
            return this._container.get(iface);
        }
        // Socketio : require() synchrone à la demande
        if (type === exports.Transport.Socketio) {
            try {
                const { ISocketioService } = require('./services/socketio-service');
                this._transportInterfaces.set(type, ISocketioService);
                return this._container.get(ISocketioService);
            }
            catch (e) {
                throw new Error('Transport Socketio configuré mais socket.io-client non installé. ' +
                    'Installez-le avec : npm install socket.io-client');
            }
        }
        return null;
    }
    getAvailableTransports() {
        return this.getTransports()
            .map(t => this.getTransport(t))
            .filter((t) => t !== null);
    }
}

const IHttpService = aurelia.DI.createInterface('IHttpService', (x) => x.singleton(HttpService));
class HttpService {
    logger;
    httpClient;
    config;
    type = exports.Transport.Http;
    constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('HttpService'), httpClient = aurelia.resolve(fetchClient.IHttpClient), config = aurelia.resolve(IBleetConfiguration)) {
        this.logger = logger;
        this.httpClient = httpClient;
        this.config = config;
        this.logger.trace('constructor');
        this.httpClient.configure((config) => {
            config.withDefaults({
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include'
            });
            return config;
        });
    }
    isAvailable() {
        return true;
    }
    prepareRequest(ctx) {
        const baseUrl = this.config.getBaseUrl(exports.Transport.Http);
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
        const remainingData = { ...ctx.data };
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
    execute(ctx, responseType = 'json') {
        this.logger.trace('execute', ctx.method, ctx.url);
        const hasBody = ['POST', 'PATCH', 'PUT', 'DELETE'].includes(ctx.method.toUpperCase());
        const headers = { ...ctx.headers };
        const init = {
            method: ctx.method.toUpperCase(),
        };
        if (hasBody && ctx.data) {
            if (ctx.data instanceof FormData) {
                // FormData: don't set Content-Type, browser will set it with boundary
                delete headers['Content-Type'];
                init.body = ctx.data;
            }
            else if (Object.keys(ctx.data).length > 0) {
                init.body = JSON.stringify(ctx.data);
            }
        }
        init.headers = headers;
        return this.httpClient.fetch(ctx.url, init)
            .then((response) => this.parseResponse(response, responseType));
    }
    parseResponse(response, responseType) {
        const headers = {};
        response.headers.forEach((value, key) => {
            headers[key] = value;
        });
        const effectiveType = responseType === 'auto'
            ? this.detectResponseType(response.headers.get('Content-Type'))
            : responseType;
        return this.parseBody(response, effectiveType)
            .then((body) => ({
            statusCode: response.status,
            headers,
            body
        }));
    }
    detectResponseType(contentType) {
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
    parseBody(response, responseType) {
        switch (responseType) {
            case 'json':
                return response.json();
            case 'text':
                return response.text();
            case 'blob':
                return response.blob();
            case 'arraybuffer':
                return response.arrayBuffer();
            default:
                return response.json();
        }
    }
}

const ISvgService = /*@__PURE__*/ aurelia.DI.createInterface('ISvgService', (x) => x.singleton(SvgService));
class SvgService {
    logger;
    static ICONS = {
        'information-circle': `<svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/></svg>`,
        'check-circle': `<svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/></svg>`,
        'exclamation-triangle': `<svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/></svg>`,
        'x-circle': `<svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd"/></svg>`,
        'x-mark': `<svg class="size-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>`,
    };
    constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('SvgService')) {
        this.logger = logger;
        this.logger.trace('constructor');
    }
    /**
     * Retourne le SVG pour une icône
     * @param icon Clé heroicon (ex: 'check-circle') ou SVG inline custom
     * @returns Le SVG du map si clé connue, sinon retourne icon tel quel (SVG custom)
     */
    get(icon) {
        this.logger.trace('get', icon);
        if (!icon)
            return null;
        return SvgService.ICONS[icon] ?? icon;
    }
    has(key) {
        return key in SvgService.ICONS;
    }
}

class CsrfCodec {
    static codec = {
        encode: (ctx) => {
            const meta = document.querySelector('meta[name="csrf"]');
            const token = meta?.getAttribute('content');
            if (!token) {
                return Promise.resolve(ctx);
            }
            return Promise.resolve({
                ...ctx,
                headers: {
                    ...ctx.headers,
                    'X-CSRF-Token': token,
                }
            });
        }
    };
    static fromConfig(config) {
        return {
            encode: (ctx) => {
                const meta = document.querySelector(`meta[name="${config.metaName}"]`);
                const token = meta?.getAttribute('content');
                if (!token) {
                    return Promise.resolve(ctx);
                }
                return Promise.resolve({
                    ...ctx,
                    headers: {
                        ...ctx.headers,
                        [config.headerName]: token,
                    }
                });
            }
        };
    }
}

const IApiService = /*@__PURE__*/ aurelia.DI.createInterface('IApiService', (x) => x.singleton(ApiService));
class ApiService {
    logger;
    config;
    static PARAM_PATTERN = /:[a-zA-Z_][a-zA-Z0-9_-]*/g;
    memoryCache = new Map();
    constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('ApiService'), config = aurelia.resolve(IBleetConfiguration)) {
        this.logger = logger;
        this.config = config;
        this.logger.trace('constructor');
    }
    url(path, params) {
        return new ApiRequestBuilder(this, path, params);
    }
    /**
     * Simple HTML fetch for AJAX dialogs (modal/drawer)
     * Returns full response for status code checking
     */
    async fetchHtml(url) {
        this.logger.trace('fetchHtml', url);
        return this.url(url).toText().get();
    }
    execute(request) {
        this.logger.trace('execute', request.method, request.url);
        // 1. Validate path params are present in pathParams or data
        this.validateParams(request.url, request.pathParams, request.data);
        // 2. Build request context
        const initialCtx = {
            url: request.url,
            method: request.method,
            headers: request.headers,
            data: request.data,
            pathParams: request.pathParams
        };
        // 3. Build codec chain : CSRF auto + user codecs
        const csrfConfig = this.config.getCsrfConfig();
        const allInputCodecs = [];
        if (csrfConfig.enabled) {
            allInputCodecs.push(CsrfCodec.fromConfig(csrfConfig));
        }
        allInputCodecs.push(...request.inputCodecs);
        // 4. Apply input codecs (encode) — chaîne de promesses
        const ctxPromise = allInputCodecs.reduce((promise, codec) => codec.encode ? promise.then((ctx) => codec.encode(ctx)) : promise, Promise.resolve(initialCtx));
        return ctxPromise.then((ctx) => {
            // 4. Check cache
            if (request.cache) {
                const cached = this.getFromCache(request, ctx);
                if (cached) {
                    this.logger.trace('execute:cache-hit');
                    return Promise.resolve(cached);
                }
            }
            // 5. Execute via transport avec fallback
            return this.executeWithFallback(ctx, request.responseType)
                .then((response) => {
                // 6. Apply output codecs (decode)
                return request.outputCodecs.reduce((promise, codec) => codec.decode ? promise.then((r) => codec.decode(r)) : promise, Promise.resolve(response));
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
    executeWithFallback(ctx, responseType) {
        const transports = this.config.getAvailableTransports();
        if (transports.length === 0) {
            return Promise.reject(new Error('No transport available'));
        }
        const tryTransport = (index, lastError) => {
            if (index >= transports.length) {
                return Promise.reject(lastError ?? new Error('All transports failed'));
            }
            const transport = transports[index];
            if (!transport.isAvailable()) {
                this.logger.trace('execute:transport-unavailable', transport.type);
                return tryTransport(index + 1, lastError);
            }
            this.logger.trace('execute:trying', transport.type);
            const preparedCtx = transport.prepareRequest(ctx);
            return transport.execute(preparedCtx, responseType)
                .catch((error) => {
                this.logger.warn('execute:transport-failed', transport.type, error);
                return tryTransport(index + 1, error);
            });
        };
        return tryTransport(0, null);
    }
    validateParams(url, pathParams, data) {
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
    genCacheKey(request, ctx) {
        const parts = [
            request.method,
            ctx.url,
            JSON.stringify(ctx.data ?? {}),
            request.pagination?.page ?? 0
        ];
        return 'api-cache:' + btoa(parts.join('|')).slice(0, 24);
    }
    getFromCache(request, ctx) {
        const key = this.genCacheKey(request, ctx);
        const storage = request.cache?.storage ?? 'session';
        let entry = null;
        if (storage === 'session') {
            const raw = sessionStorage.getItem(key);
            entry = raw ? JSON.parse(raw) : null;
        }
        else {
            entry = this.memoryCache.get(key) ?? null;
        }
        if (!entry) {
            return null;
        }
        // Check TTL
        if (entry.expires && Date.now() > entry.expires) {
            if (storage === 'session') {
                sessionStorage.removeItem(key);
            }
            else {
                this.memoryCache.delete(key);
            }
            return null;
        }
        return entry.data;
    }
    storeInCache(request, ctx, response) {
        const key = this.genCacheKey(request, ctx);
        const storage = request.cache?.storage ?? 'session';
        const ttl = request.cache?.ttl;
        const entry = {
            data: response,
            expires: ttl ? Date.now() + (ttl * 1000) : null,
            created: Date.now()
        };
        if (storage === 'session') {
            sessionStorage.setItem(key, JSON.stringify(entry));
        }
        else {
            this.memoryCache.set(key, entry);
        }
    }
}
class ApiRequestBuilder {
    api;
    _url;
    _method = 'GET';
    _data;
    _pathParams;
    _queryString = {};
    _headers = {};
    _inputCodecs = [];
    _outputCodecs = [];
    _pagination = null;
    _cache = null;
    _contentType = 'application/json';
    _accept = 'application/json';
    _responseType = 'auto';
    constructor(api, path, pathParams) {
        this.api = api;
        this._url = path;
        this._pathParams = pathParams;
    }
    // Query string
    queryString(params) {
        this._queryString = { ...this._queryString, ...params };
        return this;
    }
    // Format entrée
    fromJson(data) {
        this._contentType = 'application/json';
        if (data !== undefined) {
            this._data = data;
        }
        return this;
    }
    fromForm(data) {
        this._contentType = 'application/x-www-form-urlencoded';
        if (data !== undefined) {
            this._data = data;
        }
        return this;
    }
    fromMultipart(data) {
        this._contentType = 'multipart/form-data';
        if (data !== undefined) {
            this._data = data;
        }
        return this;
    }
    // Format sortie
    toJson() {
        this._accept = 'application/json';
        this._responseType = 'json';
        return this;
    }
    toXls() {
        this._accept = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        this._responseType = 'blob';
        return this;
    }
    toBlob() {
        this._accept = 'application/octet-stream';
        this._responseType = 'blob';
        return this;
    }
    toText() {
        this._accept = 'text/plain';
        this._responseType = 'text';
        return this;
    }
    toArrayBuffer() {
        this._accept = 'application/octet-stream';
        this._responseType = 'arraybuffer';
        return this;
    }
    // Codecs
    withInputCodec(codec) {
        this._inputCodecs.push(codec);
        return this;
    }
    withOutputCodec(codec) {
        this._outputCodecs.push(codec);
        return this;
    }
    // Pagination & Cache
    withPagination(config) {
        this._pagination = { pageSize: 20, ...config };
        return this;
    }
    withCache(config) {
        this._cache = { storage: 'session', ...config };
        return this;
    }
    // Execution
    get() {
        this._method = 'GET';
        return this.api.execute(this.build());
    }
    post() {
        this._method = 'POST';
        return this.api.execute(this.build());
    }
    put() {
        this._method = 'PUT';
        return this.api.execute(this.build());
    }
    patch() {
        this._method = 'PATCH';
        return this.api.execute(this.build());
    }
    delete() {
        this._method = 'DELETE';
        return this.api.execute(this.build());
    }
    request(verb) {
        this._method = verb.toUpperCase();
        return this.api.execute(this.build());
    }
    build() {
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
    appendQueryString(url) {
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

// io sera chargé à la demande via require()
let io = null;
function getSocketIo() {
    if (!io) {
        try {
            io = require('socket.io-client').io;
        }
        catch {
            throw new Error('socket.io-client non installé. ' +
                'Installez-le avec : npm install socket.io-client');
        }
    }
    return io;
}
const ISocketioService = aurelia.DI.createInterface('ISocketioService', (x) => x.singleton(SocketioService));
class SocketioService {
    logger;
    config;
    type = exports.Transport.Socketio;
    timeout = 5000;
    socket = null;
    connected = false;
    constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('SocketioService'), config = aurelia.resolve(IBleetConfiguration)) {
        this.logger = logger;
        this.config = config;
        this.logger.trace('constructor');
    }
    isConnected() {
        return this.connected && this.socket !== null;
    }
    isAvailable() {
        return this.isConnected();
    }
    prepareRequest(ctx) {
        return {
            ...ctx,
            data: { ...ctx.data, ...ctx.pathParams }
        };
    }
    connect(namespace = '/', options) {
        const baseUrl = this.config.getBaseUrl(exports.Transport.Socketio);
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
            this.socket.on('connect_error', (error) => {
                this.logger.error('connect:error', error);
                this.connected = false;
                reject(error);
            });
            this.socket.on('disconnect', (reason) => {
                this.logger.trace('disconnect', reason);
                this.connected = false;
            });
        });
    }
    disconnect() {
        this.logger.trace('disconnect');
        if (this.socket !== null) {
            this.socket.disconnect();
            this.socket = null;
            this.connected = false;
        }
    }
    execute(ctx, responseType) {
        this.logger.trace('execute', ctx.method, ctx.url);
        if (!this.isConnected() || this.socket === null) {
            return Promise.reject(new Error('Socket not connected'));
        }
        const channel = `${ctx.method.toLowerCase()}:${ctx.url}`;
        let data = ctx.data ?? {};
        // Convert FormData to plain object (Socket.io can't send FormData)
        if (data instanceof FormData) {
            const obj = {};
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
            this.socket.emit(channel, data, (response) => {
                clearTimeout(timeoutId);
                this.logger.trace('execute:response', channel, response);
                resolve(response);
            });
        });
    }
}

const IStorageService = /*@__PURE__*/ aurelia.DI.createInterface('IStorageService', (x) => x.singleton(StorageService));
class StorageService {
    logger;
    platform;
    constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('StorageService'), platform = aurelia.resolve(aurelia.IPlatform)) {
        this.logger = logger;
        this.platform = platform;
        this.logger.trace('constructor');
    }
    load(key, def = null) {
        this.logger.trace('load', key);
        const value = localStorage.getItem(key);
        if (value === null) {
            return def;
        }
        return JSON.parse(value);
    }
    save(key, value) {
        this.logger.trace('save', key, value);
        localStorage.setItem(key, JSON.stringify(value));
    }
    remove(key) {
        this.logger.trace('remove', key);
        localStorage.removeItem(key);
    }
}

const ITransitionService = /*@__PURE__*/ aurelia.DI.createInterface('ITransitionService', (x) => x.singleton(TransitionService));
class TransitionService {
    logger;
    platform;
    securityTimeout = 2000;
    constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('TransitionService'), platform = aurelia.resolve(aurelia.IPlatform)) {
        this.logger = logger;
        this.platform = platform;
        this.logger.trace('constructor');
    }
    run(element, before, after) {
        let securityTimeout = undefined;
        const endTransition = (evt) => {
            if (securityTimeout !== undefined) {
                this.platform.clearTimeout(securityTimeout);
                securityTimeout = undefined;
            }
            element.removeEventListener('transitionend', endTransition);
            if (after) {
                this.logger.trace('after()');
                after(element);
            }
        };
        if (before) {
            securityTimeout = this.platform.setTimeout(endTransition, this.securityTimeout);
            element.addEventListener('transitionend', endTransition);
            this.platform.requestAnimationFrame(() => {
                this.logger.trace('before()');
                before(element);
            });
        }
    }
}

const ITrapFocusService = /*@__PURE__*/ aurelia.DI.createInterface('ITrapFocusService', (x) => x.transient(TrapFocusService));
class TrapFocusService {
    logger;
    platform;
    focusableElementsQuerySelector = '[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"]), [accesskey], summary, canvas, audio, video, details, iframe, [contenteditable]';
    opener = null;
    target = null;
    globalElement = null;
    startCallback = null;
    stopCallback = null;
    focusableElements = [];
    lastFocusedElement = null;
    started = false;
    constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('TrapFocusService'), platform = aurelia.resolve(aurelia.IPlatform)) {
        this.logger = logger;
        this.platform = platform;
        this.logger.trace('constructor');
    }
    buildFocusableElements() {
        this.focusableElements = [];
        const focusableElements = this.target?.querySelectorAll(this.focusableElementsQuerySelector);
        focusableElements?.forEach((element) => {
            const isDisabled = element.hasAttribute('disabled');
            const isAriaHidden = (element.getAttribute('aria-hidden') === 'true');
            const isNotTabbable = (element.getAttribute('tabindex') === '-1');
            if (isDisabled === false && isAriaHidden === false && isNotTabbable === false) {
                this.focusableElements.push(element);
            }
        });
    }
    start(opener, target, globalElement, startCallback, stopCallback, initialFocusElement) {
        this.logger.trace('start', opener, target);
        this.startCallback = startCallback ?? null;
        this.stopCallback = stopCallback ?? null;
        this.opener = opener;
        this.target = target;
        this.globalElement = globalElement;
        this.buildFocusableElements();
        // Use provided initialFocusElement if valid, otherwise default to first focusable
        if (initialFocusElement && this.focusableElements.includes(initialFocusElement)) {
            this.lastFocusedElement = initialFocusElement;
        }
        else {
            this.lastFocusedElement = this.focusableElements[0] || undefined;
        }
        this.logger.trace('start: add keydown listener');
        this.platform.requestAnimationFrame(() => {
            this.logger.trace('start: focus initial element', this.lastFocusedElement);
            this.lastFocusedElement?.focus();
        });
        this.target.addEventListener('keydown', this.onKeyDown);
        this.platform.document.addEventListener('click', this.onClickOutside);
        this.started = true;
        if (this.startCallback) {
            const promise = this.startCallback();
            if (promise && promise instanceof Promise) {
                return promise;
            }
        }
        return Promise.resolve();
    }
    stop() {
        this.logger.trace('stop');
        return new Promise((resolve, reject) => {
            if (this.started) {
                this.logger.trace('stop: remove keydown listener');
                this.target?.removeEventListener('keydown', this.onKeyDown);
                this.platform.document.removeEventListener('click', this.onClickOutside);
                if (this.stopCallback) {
                    const promise = this.stopCallback();
                    if (promise && promise instanceof Promise) {
                        this.platform.requestAnimationFrame(() => {
                            this.opener?.focus();
                            this.cleanup();
                            promise.then((res) => {
                                return resolve(res);
                            });
                        });
                        return;
                    }
                    return resolve(void 0);
                }
                this.platform.requestAnimationFrame(() => {
                    this.opener?.focus();
                    this.cleanup();
                    return resolve(void 0);
                });
                return;
            }
            return reject('TrapFocusService: not started');
        });
    }
    cleanup() {
        this.logger.trace('cleanup');
        if (this.started) {
            this.opener = null;
            this.startCallback = null;
            this.stopCallback = null;
            this.target = null;
            this.lastFocusedElement = null;
            this.focusableElements = [];
            this.started = false;
        }
    }
    focusedElementIndex() {
        let index = -1;
        if (this.lastFocusedElement) {
            index = this.focusableElements.indexOf(this.lastFocusedElement);
        }
        if (index === -1 && this.lastFocusedElement !== undefined) {
            this.lastFocusedElement = null;
        }
        return index;
    }
    focusPreviousElement(loop = true) {
        const currentIndex = this.focusedElementIndex();
        if (currentIndex === -1) {
            return null;
        }
        let changed = false;
        if (currentIndex === 0 && loop === true) {
            this.lastFocusedElement = this.focusableElements[this.focusableElements.length - 1];
            changed = true;
        }
        else if (currentIndex > 0) {
            this.lastFocusedElement = this.focusableElements[currentIndex - 1];
            changed = true;
        }
        if (changed === true) {
            this.platform.requestAnimationFrame(() => {
                this.logger.trace('focusPreviousElement: focusing', this.lastFocusedElement);
                this.lastFocusedElement?.focus();
            });
        }
        return this.lastFocusedElement;
    }
    focusNextElement(loop = true) {
        const currentIndex = this.focusedElementIndex();
        if (currentIndex === -1) {
            return null;
        }
        let changed = false;
        if (currentIndex === this.focusableElements.length - 1 && loop === true) {
            this.lastFocusedElement = this.focusableElements[0];
            changed = true;
        }
        else if (currentIndex < this.focusableElements.length - 1) {
            this.lastFocusedElement = this.focusableElements[currentIndex + 1];
            changed = true;
        }
        if (changed === true) {
            this.platform.requestAnimationFrame(() => {
                this.logger.trace('focusNextElement: focusing', this.lastFocusedElement);
                this.lastFocusedElement?.focus();
            });
        }
        return this.lastFocusedElement;
    }
    onKeyDown = (event) => {
        if (event.key === 'Tab') {
            if (event.shiftKey) {
                // shift + tab loop backwards
                event.preventDefault();
                this.lastFocusedElement = this.focusPreviousElement(true);
            }
            else {
                // tab loop forwards
                event.preventDefault();
                this.lastFocusedElement = this.focusNextElement(true);
            }
        }
        else if (event.key === 'ArrowUp') {
            // up arrow, no loop
            event.preventDefault();
            this.lastFocusedElement = this.focusPreviousElement(false);
        }
        else if (event.key === 'ArrowDown') {
            // down arrow, no loop
            event.preventDefault();
            this.lastFocusedElement = this.focusNextElement(false);
        }
        else if (event.key === 'Escape') {
            // stop trap focus
            event.preventDefault();
            this.stop();
        }
    };
    onClickOutside = (event) => {
        this.logger.trace('onClickOutside', event);
        if (this.started && this.globalElement && event.target) {
            this.logger.trace('onClickOutside: checking if click is outside globalElement', event.target, this.globalElement);
            if (!this.globalElement.contains(event.target)) {
                this.stop();
            }
        }
    };
}

/******************************************************************************
Copyright (c) Microsoft Corporation.

Permission to use, copy, modify, and/or distribute this software for any
purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
PERFORMANCE OF THIS SOFTWARE.
***************************************************************************** */
/* global Reflect, Promise, SuppressedError, Symbol, Iterator */


function __esDecorate(ctor, descriptorIn, decorators, contextIn, initializers, extraInitializers) {
    function accept(f) { if (f !== void 0 && typeof f !== "function") throw new TypeError("Function expected"); return f; }
    var kind = contextIn.kind, key = kind === "getter" ? "get" : kind === "setter" ? "set" : "value";
    var target = !descriptorIn && ctor ? contextIn["static"] ? ctor : ctor.prototype : null;
    var descriptor = descriptorIn || (target ? Object.getOwnPropertyDescriptor(target, contextIn.name) : {});
    var _, done = false;
    for (var i = decorators.length - 1; i >= 0; i--) {
        var context = {};
        for (var p in contextIn) context[p] = p === "access" ? {} : contextIn[p];
        for (var p in contextIn.access) context.access[p] = contextIn.access[p];
        context.addInitializer = function (f) { if (done) throw new TypeError("Cannot add initializers after decoration has completed"); extraInitializers.push(accept(f || null)); };
        var result = (0, decorators[i])(kind === "accessor" ? { get: descriptor.get, set: descriptor.set } : descriptor[key], context);
        if (kind === "accessor") {
            if (result === void 0) continue;
            if (result === null || typeof result !== "object") throw new TypeError("Object expected");
            if (_ = accept(result.get)) descriptor.get = _;
            if (_ = accept(result.set)) descriptor.set = _;
            if (_ = accept(result.init)) initializers.unshift(_);
        }
        else if (_ = accept(result)) {
            if (kind === "field") initializers.unshift(_);
            else descriptor[key] = _;
        }
    }
    if (target) Object.defineProperty(target, contextIn.name, descriptor);
    done = true;
}
function __runInitializers(thisArg, initializers, value) {
    var useValue = arguments.length > 2;
    for (var i = 0; i < initializers.length; i++) {
        value = useValue ? initializers[i].call(thisArg, value) : initializers[i].call(thisArg);
    }
    return useValue ? value : void 0;
}
typeof SuppressedError === "function" ? SuppressedError : function (error, suppressed, message) {
    var e = new Error(message);
    return e.name = "SuppressedError", e.error = error, e.suppressed = suppressed, e;
};

exports.Channels = void 0;
(function (Channels) {
    Channels["Overlay"] = "bleet:overlay";
    Channels["OverlayStatus"] = "bleet:overlay:status";
    Channels["Modal"] = "bleet:modal";
    Channels["ModalStatus"] = "bleet:modal:status";
    Channels["Drawer"] = "bleet:drawer";
    Channels["DrawerStatus"] = "bleet:drawer:status";
    Channels["Toaster"] = "bleet:toaster";
    Channels["ToasterStatus"] = "bleet:toaster:status";
    Channels["Menu"] = "bleet:menu";
    Channels["MenuStatus"] = "bleet:menu:status";
    Channels["Badge"] = "bleet:badge";
    Channels["Profile"] = "bleet:profile";
    Channels["ProfileStatus"] = "bleet:profile:status";
    Channels["Ajaxify"] = "bleet:ajaxify";
    Channels["Popover"] = "bleet:popover";
    Channels["PopoverStatus"] = "bleet:popover:status";
})(exports.Channels || (exports.Channels = {}));
exports.OverlayAction = void 0;
(function (OverlayAction) {
    OverlayAction["Open"] = "open";
    OverlayAction["Close"] = "close";
    OverlayAction["Toggle"] = "toggle";
})(exports.OverlayAction || (exports.OverlayAction = {}));
exports.OverlayStatus = void 0;
(function (OverlayStatus) {
    OverlayStatus["Opening"] = "opening";
    OverlayStatus["Closing"] = "closing";
    OverlayStatus["Opened"] = "opened";
    OverlayStatus["Closed"] = "closed";
})(exports.OverlayStatus || (exports.OverlayStatus = {}));
exports.ProfileAction = void 0;
(function (ProfileAction) {
    ProfileAction["Open"] = "open";
    ProfileAction["Close"] = "close";
    ProfileAction["Toggle"] = "toggle";
})(exports.ProfileAction || (exports.ProfileAction = {}));
exports.ProfileStatus = void 0;
(function (ProfileStatus) {
    ProfileStatus["Opening"] = "opening";
    ProfileStatus["Closing"] = "closing";
    ProfileStatus["Opened"] = "opened";
    ProfileStatus["Closed"] = "closed";
})(exports.ProfileStatus || (exports.ProfileStatus = {}));
exports.ModalAction = void 0;
(function (ModalAction) {
    ModalAction["Open"] = "open";
    ModalAction["Close"] = "close";
    ModalAction["Toggle"] = "toggle";
})(exports.ModalAction || (exports.ModalAction = {}));
exports.ModalStatus = void 0;
(function (ModalStatus) {
    ModalStatus["Opening"] = "opening";
    ModalStatus["Closing"] = "closing";
    ModalStatus["Opened"] = "opened";
    ModalStatus["Closed"] = "closed";
})(exports.ModalStatus || (exports.ModalStatus = {}));
exports.DrawerAction = void 0;
(function (DrawerAction) {
    DrawerAction["Open"] = "open";
    DrawerAction["Close"] = "close";
    DrawerAction["Toggle"] = "toggle";
})(exports.DrawerAction || (exports.DrawerAction = {}));
exports.DrawerStatus = void 0;
(function (DrawerStatus) {
    DrawerStatus["Opening"] = "opening";
    DrawerStatus["Closing"] = "closing";
    DrawerStatus["Opened"] = "opened";
    DrawerStatus["Closed"] = "closed";
})(exports.DrawerStatus || (exports.DrawerStatus = {}));
exports.AjaxifyAction = void 0;
(function (AjaxifyAction) {
    AjaxifyAction["Refresh"] = "refresh";
})(exports.AjaxifyAction || (exports.AjaxifyAction = {}));
exports.ToasterAction = void 0;
(function (ToasterAction) {
    ToasterAction["Add"] = "add";
    ToasterAction["Remove"] = "remove";
})(exports.ToasterAction || (exports.ToasterAction = {}));
exports.ToasterStatus = void 0;
(function (ToasterStatus) {
    ToasterStatus["Added"] = "added";
    ToasterStatus["Removed"] = "removed";
})(exports.ToasterStatus || (exports.ToasterStatus = {}));
exports.MenuAction = void 0;
(function (MenuAction) {
    MenuAction["Open"] = "open";
    MenuAction["Close"] = "close";
    MenuAction["Toggle"] = "toggle";
})(exports.MenuAction || (exports.MenuAction = {}));
exports.BadgeAction = void 0;
(function (BadgeAction) {
    BadgeAction["Remove"] = "remove";
})(exports.BadgeAction || (exports.BadgeAction = {}));
exports.MenuStatus = void 0;
(function (MenuStatus) {
    MenuStatus["Opening"] = "opening";
    MenuStatus["Closing"] = "closing";
    MenuStatus["Opened"] = "opened";
    MenuStatus["Closed"] = "closed";
})(exports.MenuStatus || (exports.MenuStatus = {}));
exports.PopoverAction = void 0;
(function (PopoverAction) {
    PopoverAction["Open"] = "open";
    PopoverAction["Close"] = "close";
    PopoverAction["Toggle"] = "toggle";
})(exports.PopoverAction || (exports.PopoverAction = {}));
exports.PopoverStatus = void 0;
(function (PopoverStatus) {
    PopoverStatus["Opening"] = "opening";
    PopoverStatus["Closing"] = "closing";
    PopoverStatus["Opened"] = "opened";
    PopoverStatus["Closed"] = "closed";
})(exports.PopoverStatus || (exports.PopoverStatus = {}));
exports.UiColor = void 0;
(function (UiColor) {
    UiColor["Primary"] = "primary";
    UiColor["Secondary"] = "secondary";
    UiColor["Success"] = "success";
    UiColor["Danger"] = "danger";
    UiColor["Warning"] = "warning";
    UiColor["Info"] = "info";
    UiColor["Accent"] = "accent";
})(exports.UiColor || (exports.UiColor = {}));
exports.UiToastIcon = void 0;
(function (UiToastIcon) {
    UiToastIcon["Info"] = "information-circle";
    UiToastIcon["Success"] = "check-circle";
    UiToastIcon["Warning"] = "exclamation-triangle";
    UiToastIcon["Danger"] = "x-circle";
})(exports.UiToastIcon || (exports.UiToastIcon = {}));
/**
 * Icônes pour toasts et dialogs
 * Double alias : court (Info) et long (InformationCircle) → même valeur
 */
exports.UiIcon = void 0;
(function (UiIcon) {
    // Alias courts (usage dev)
    UiIcon["Info"] = "information-circle";
    UiIcon["Success"] = "check-circle";
    UiIcon["Warning"] = "exclamation-triangle";
    UiIcon["Danger"] = "x-circle";
    // Alias longs (match heroicon)
    UiIcon["InformationCircle"] = "information-circle";
    UiIcon["CheckCircle"] = "check-circle";
    UiIcon["ExclamationTriangle"] = "exclamation-triangle";
    UiIcon["XCircle"] = "x-circle";
})(exports.UiIcon || (exports.UiIcon = {}));
/**
 * Actions primaires dialog (mutuellement exclusives)
 */
exports.DialogAction = void 0;
(function (DialogAction) {
    DialogAction["Keep"] = "keep";
    DialogAction["Close"] = "close";
    DialogAction["RefreshAndClose"] = "refreshAndClose";
})(exports.DialogAction || (exports.DialogAction = {}));

let BleetBurgerCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute('bleet-burger')];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        ea;
        element;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-burger'), ea = aurelia.resolve(aurelia.IEventAggregator), element = aurelia.resolve(aurelia.INode)) {
            this.logger = logger;
            this.ea = ea;
            this.element = element;
            this.logger.trace('constructor');
        }
        attached() {
            this.logger.trace('attached');
            this.element.addEventListener('click', this.onClickButton);
        }
        detached() {
            this.logger.trace('detached');
            this.element.removeEventListener('click', this.onClickButton);
        }
        onClickButton = (event) => {
            this.logger.trace('onClickButton', event);
            event.preventDefault();
            this.ea.publish(exports.Channels.Menu, { action: exports.MenuAction.Open });
        };
    });
    return _classThis;
})();

let BleetMenuCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute('bleet-menu')];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        ea;
        element;
        platform;
        transitionService;
        storageService;
        disposable;
        disposableOverlay;
        closeButton;
        toggleButtons;
        sublists = new Map();
        isOpen = false;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-menu'), ea = aurelia.resolve(aurelia.IEventAggregator), element = aurelia.resolve(aurelia.INode), platform = aurelia.resolve(aurelia.IPlatform), transitionService = aurelia.resolve(ITransitionService), storageService = aurelia.resolve(IStorageService)) {
            this.logger = logger;
            this.ea = ea;
            this.element = element;
            this.platform = platform;
            this.transitionService = transitionService;
            this.storageService = storageService;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.closeButton = this.element.querySelector('[data-menu=close]');
            this.toggleButtons = this.element.querySelectorAll('[data-menu^="toggle-button"]');
            this.initMenuButtons();
        }
        attached() {
            this.logger.trace('attached');
            this.disposable = this.ea.subscribe(exports.Channels.Menu, this.onMenuEvent);
            this.disposableOverlay = this.ea.subscribe(exports.Channels.OverlayStatus, this.onOverlayStatus);
            this.closeButton?.addEventListener('click', this.onClickClose);
            this.element.addEventListener('click', this.onClickToggleButtons);
        }
        detached() {
            this.logger.trace('detached');
            this.closeButton?.removeEventListener('click', this.onClickClose);
            this.element.removeEventListener('click', this.onClickToggleButtons);
            this.disposableOverlay?.dispose();
            this.disposable?.dispose();
        }
        dispose() {
            this.logger.trace('dispose');
            this.disposableOverlay?.dispose();
            this.disposable?.dispose();
        }
        open(fromOverlay = false) {
            if (!this.isOpen) {
                this.logger.trace('open');
                this.isOpen = true;
                this.transitionService.run(this.element, (element) => {
                    if (!fromOverlay) {
                        this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Open });
                    }
                    this.ea.publish(exports.Channels.MenuStatus, { status: exports.MenuStatus.Opening });
                    element.classList.add('translate-x-0');
                    element.classList.remove('-translate-x-full');
                    element.ariaHidden = 'false';
                }, (element) => {
                    this.ea.publish(exports.Channels.MenuStatus, { status: exports.MenuStatus.Opened });
                });
            }
        }
        close(fromOverlay = false) {
            if (this.isOpen) {
                this.logger.trace('close');
                this.isOpen = false;
                this.transitionService.run(this.element, (element) => {
                    this.ea.publish(exports.Channels.MenuStatus, { status: exports.MenuStatus.Closing });
                    if (!fromOverlay) {
                        this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Close });
                    }
                    element.classList.add('-translate-x-full');
                    element.classList.remove('translate-x-0');
                    element.ariaHidden = 'true';
                }, (element) => {
                    this.ea.publish(exports.Channels.MenuStatus, { status: exports.MenuStatus.Closed });
                });
            }
        }
        onClickClose = (event) => {
            this.logger.trace('onClickClose', event);
            event.preventDefault();
            this.close();
        };
        onClickToggleButtons = (event) => {
            const target = event.target;
            const btn = target.closest('[data-menu^="toggle-button"]');
            if (btn && btn.matches('[data-menu^="toggle-button"]')) {
                event.preventDefault();
                this.toggleButton(btn);
            }
        };
        initMenuButtons() {
            this.logger.trace('initMenu');
            this.toggleButtons?.forEach((btn) => {
                if (!this.sublists.has(btn)) {
                    const id = btn.dataset.menu?.replace('toggle-button-', '');
                    const isOpen = this.storageService.load(`toggle-list-${id}`, false);
                    const list = this.element.querySelector(`[data-menu="toggle-list-${id}"]`);
                    const svg = btn.querySelector('svg[data-menu=icon]');
                    this.sublists.set(btn, { list, svg });
                    if (!isOpen) {
                        svg.classList.remove('rotate-180');
                        list.classList.add('hidden');
                        btn.ariaExpanded = 'false';
                    }
                    else {
                        svg.classList.add('rotate-180');
                        list.classList.remove('hidden');
                        btn.ariaExpanded = 'true';
                    }
                }
            });
        }
        toggleButton(btn) {
            if (this.sublists.has(btn)) {
                const sublist = this.sublists.get(btn);
                const id = btn.dataset.menu?.replace('toggle-button-', '');
                if (sublist?.list.classList.contains('hidden')) {
                    sublist?.list.classList.remove('hidden');
                    sublist?.svg.classList.add('rotate-180');
                    btn.ariaExpanded = 'true';
                    this.storageService.save(`toggle-list-${id}`, true);
                }
                else {
                    sublist?.list.classList.add('hidden');
                    sublist?.svg.classList.remove('rotate-180');
                    btn.ariaExpanded = 'false';
                    this.storageService.save(`toggle-list-${id}`, false);
                }
            }
        }
        closeOtherButtons(except) {
            this.sublists.forEach((value, key) => {
                if (key !== except) {
                    const id = key.dataset.menu?.replace('toggle-button-', '');
                    this.storageService.save(`toggle-list-${id}`, false);
                    value.list.classList.add('hidden');
                    value.svg.classList.remove('rotate-180');
                }
            });
        }
        onMenuEvent = (data) => {
            this.logger.trace('onMenuEvent', data);
            if (data.action === exports.MenuAction.Close) {
                // this.element.classList.add('-translate-x-full');
                this.logger.trace('Menu Close action received');
                this.close();
            }
            else if (data.action === exports.MenuAction.Open) {
                // this.element.classList.remove('-translate-x-full');
                this.logger.trace('Menu Open action received');
                this.open();
            }
            else if (data.action === exports.MenuAction.Toggle) {
                // this.element.classList.toggle('-translate-x-full');
                this.logger.trace('Menu Toggle action received');
            }
        };
        onOverlayStatus = (data) => {
            if (data.status === exports.OverlayStatus.Closing) {
                this.logger.trace('Overlay Close action received');
                this.close(true);
            }
            else {
                this.logger.trace('onOverlayStatus unhandled', data);
            }
        };
    });
    return _classThis;
})();

let BleetBadgeCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-badge', defaultProperty: 'id' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        ea;
        element;
        closeButton;
        id = __runInitializers(this, _id_initializers, '');
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-badge'), ea = aurelia.resolve(aurelia.IEventAggregator), element = aurelia.resolve(aurelia.INode)) {
            this.logger = logger;
            this.ea = ea;
            this.element = element;
            this.logger.trace('constructor');
        }
        attached() {
            this.logger.trace('attached');
            this.closeButton = this.element.querySelector('[data-badge=remove]') ?? undefined;
            this.closeButton?.addEventListener('click', this.onClickRemove);
        }
        detached() {
            this.logger.trace('detached');
            this.closeButton?.removeEventListener('click', this.onClickRemove);
        }
        onClickRemove = (__runInitializers(this, _id_extraInitializers), (event) => {
            this.logger.trace('onClickRemove', event);
            event.preventDefault();
            this.ea.publish(exports.Channels.Badge, { action: exports.BadgeAction.Remove, id: this.id });
            this.element.remove();
        });
    });
    return _classThis;
})();

// @ts-ignore
let BleetSelectCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute('bleet-select')];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        trapFocusService;
        select;
        button;
        buttonText;
        optionTemplate;
        itemsPlace;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-select'), element = aurelia.resolve(aurelia.INode), trapFocusService = aurelia.resolve(ITrapFocusService)) {
            this.logger = logger;
            this.element = element;
            this.trapFocusService = trapFocusService;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.select = this.element.querySelector('select');
            this.button = this.element.querySelector('button');
            this.buttonText = this.button.querySelector('[data-select=value]');
            this.optionTemplate = this.element.querySelector('[data-select=item-template]');
            this.itemsPlace = this.element.querySelector('[data-select=items]');
        }
        attached() {
            this.logger.trace('attached');
            if (!this.itemsPlace) {
                throw new Error('Items place element not found');
            }
            if (!this.itemsPlace.id) {
                this.itemsPlace.id = `data-select-items-${Math.random().toString(36).substring(2, 15)}`;
            }
            if (!this.select?.options) {
                throw new Error('Select options not found');
            }
            if (!this.optionTemplate) {
                throw new Error('Option template not found');
            }
            if (!this.button) {
                throw new Error('Button element not found');
            }
            if (!this.buttonText) {
                throw new Error('Button text element not found');
            }
            this.preparePanel();
            // ARIA setup
            this.button.ariaHasPopup = 'listbox';
            this.button.setAttribute('aria-controls', this.itemsPlace.id);
            this.itemsPlace.role = 'listbox';
            // Event listeners
            this.button?.addEventListener('click', this.onClickToggleMenu);
            this.itemsPlace?.addEventListener('click', this.onClickToggleItem);
        }
        detached() {
            this.logger.trace('detached');
            this.button?.removeEventListener('click', this.onClickToggleMenu);
            this.itemsPlace?.removeEventListener('click', this.onClickToggleItem);
        }
        onClickToggleMenu = (event) => {
            this.logger.trace('onClick', event);
            event.preventDefault();
            if (this.select?.disabled) {
                return;
            }
            return this.toggleMenu();
        };
        onStopTrapFocus = () => {
            this.logger.trace('onStopTrapFocus');
            this.itemsPlace?.classList.add('hidden');
            return Promise.resolve();
        };
        toggleMenu = () => {
            const isClosed = this.itemsPlace?.classList.contains('hidden');
            return new Promise((resolve) => {
                if (isClosed) {
                    // Opening menu
                    this.button.ariaExpanded = 'true';
                    // Find selected option to focus initially
                    const selectedOption = this.itemsPlace?.querySelector('[aria-selected="true"]');
                    return this.trapFocusService.start(this.button, this.itemsPlace, this.element, undefined, this.onStopTrapFocus, selectedOption // Pass selected option as initial focus
                    )
                        .then(() => {
                        this.logger.trace('toggleMenu opened');
                        this.itemsPlace?.classList.remove('hidden');
                        resolve(void 0);
                    });
                }
                else {
                    // Closing menu
                    this.button.ariaExpanded = 'false';
                    return this.trapFocusService.stop()
                        .then(() => {
                        this.logger.trace('toggleMenu closed');
                        this.itemsPlace?.classList.add('hidden');
                        resolve(void 0);
                    });
                }
            });
        };
        onClickToggleItem = (event) => {
            this.logger.trace('onClickItem', event);
            event.preventDefault();
            const element = event.target.closest('[data-value]');
            // Update select options
            Array.from(this.select.options).forEach((option) => {
                option.selected = option.value == element.dataset.value;
            });
            // Dispatch change event sur le select natif pour active-form
            this.select?.dispatchEvent(new Event('change', { bubbles: true }));
            this.synchSelect();
        };
        synchSelect() {
            this.swapItemClasses();
            // Close menu
            return this.toggleMenu();
        }
        swapItemClasses() {
            Array.from(this.select.options).forEach((option) => {
                const item = this.itemsPlace?.querySelector(`[data-value="${option.value}"]`);
                if (!item)
                    return;
                const checkmark = item.querySelector('[data-select=item-check]');
                // Récupérer les classes depuis les data-attributes de l'élément
                const itemInactiveClasses = item.dataset.classInactive?.split(' ') || [];
                const itemActiveClasses = item.dataset.classActive?.split(' ') || [];
                const checkInactiveClasses = checkmark?.dataset.classInactive?.split(' ') || [];
                const checkActiveClasses = checkmark?.dataset.classActive?.split(' ') || [];
                if (option.selected) {
                    // Swap vers active
                    item.classList.remove(...itemInactiveClasses);
                    item.classList.add(...itemActiveClasses);
                    checkmark?.classList.remove(...checkInactiveClasses);
                    checkmark?.classList.add(...checkActiveClasses);
                    // Update ARIA
                    item.setAttribute('aria-selected', 'true');
                    this.button?.setAttribute('aria-activedescendant', item.id);
                    // Update button text
                    this.buttonText.innerHTML = option.innerHTML;
                }
                else {
                    // Swap vers inactive
                    item.classList.remove(...itemActiveClasses);
                    item.classList.add(...itemInactiveClasses);
                    checkmark?.classList.remove(...checkActiveClasses);
                    checkmark?.classList.add(...checkInactiveClasses);
                    // Update ARIA
                    item.setAttribute('aria-selected', 'false');
                }
            });
        }
        preparePanel() {
            this.logger.trace('preparePanel');
            if (!this.select) {
                throw new Error('Select element not found');
            }
            // Vider le panel (sauf les templates)
            this.itemsPlace?.querySelectorAll('button').forEach((child) => child.remove());
            const options = Array.from(this.select.options);
            options.forEach((option) => {
                // @ts-ignore
                const item = this.optionTemplate.content.cloneNode(true);
                const button = item.querySelector('button');
                // ARIA attributes
                button.role = 'option';
                button.id = `bleet-option-${option.value}-${Math.random().toString(36).substring(2, 9)}`;
                // Content
                const itemText = item.querySelector('[data-select=item-text]');
                const itemValue = item.querySelector('[data-value]');
                itemValue.dataset.value = option.value;
                itemText.innerHTML = option.innerHTML;
                this.itemsPlace?.append(item);
            });
            // Appliquer les classes active/inactive
            this.swapItemClasses();
        }
    });
    return _classThis;
})();

// @ts-ignore
let BleetDropdownCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute('bleet-dropdown')];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        trapFocusService;
        select;
        button;
        buttonText;
        optionTemplate;
        tagTemplate;
        tagsContainer;
        placeholder;
        itemsPlace;
        itemsContainer;
        searchInput;
        emptyMessage;
        isMultiple = false;
        withTags = false;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-dropdown'), element = aurelia.resolve(aurelia.INode), trapFocusService = aurelia.resolve(ITrapFocusService)) {
            this.logger = logger;
            this.element = element;
            this.trapFocusService = trapFocusService;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.select = this.element.querySelector('select');
            this.button = this.element.querySelector('button');
            this.buttonText = this.button.querySelector('[data-dropdown=value]');
            this.tagsContainer = this.button.querySelector('[data-dropdown=tags]');
            this.placeholder = this.button.querySelector('[data-dropdown=placeholder]');
            this.optionTemplate = this.element.querySelector('[data-dropdown=item-template]');
            this.tagTemplate = this.element.querySelector('[data-dropdown=tag-template]');
            this.itemsPlace = this.element.querySelector('[data-dropdown=items]');
            this.itemsContainer = this.element.querySelector('[data-dropdown=items-container]');
            this.searchInput = this.element.querySelector('[data-dropdown=search]');
            this.emptyMessage = this.element.querySelector('[data-dropdown=empty]');
            this.isMultiple = this.select?.multiple || false;
            this.withTags = this.tagTemplate !== null;
        }
        attached() {
            this.logger.trace('attached');
            if (!this.itemsPlace) {
                throw new Error('Items place element not found');
            }
            if (!this.itemsPlace.id) {
                this.itemsPlace.id = `data-dropdown-items-${Math.random().toString(36).substring(2, 15)}`;
            }
            if (!this.select?.options) {
                throw new Error('Select options not found');
            }
            if (!this.optionTemplate) {
                throw new Error('Option template not found');
            }
            if (!this.button) {
                throw new Error('Button element not found');
            }
            if (!this.buttonText && !this.tagsContainer) {
                throw new Error('Button text or tags container element not found');
            }
            this.preparePanel();
            // ARIA setup
            this.button.ariaHasPopup = 'listbox';
            this.button.setAttribute('aria-controls', this.itemsPlace.id);
            this.itemsPlace.role = 'listbox';
            // Event listeners
            this.button?.addEventListener('click', this.onClickToggleMenu);
            this.itemsPlace?.addEventListener('click', this.onClickToggleItem);
            this.searchInput?.addEventListener('input', this.onSearchInput);
            // Tag remove event listener (delegation)
            if (this.withTags) {
                this.tagsContainer?.addEventListener('click', this.onClickRemoveTag);
            }
        }
        detached() {
            this.logger.trace('detached');
            this.button?.removeEventListener('click', this.onClickToggleMenu);
            this.itemsPlace?.removeEventListener('click', this.onClickToggleItem);
            this.searchInput?.removeEventListener('input', this.onSearchInput);
            if (this.withTags) {
                this.tagsContainer?.removeEventListener('click', this.onClickRemoveTag);
            }
        }
        onClickToggleMenu = (event) => {
            this.logger.trace('onClick', event);
            // Ne pas ouvrir si on clique sur un bouton de suppression de tag
            if (event.target.closest('[data-dropdown=tag-remove]')) {
                return;
            }
            event.preventDefault();
            return this.toggleMenu();
        };
        onClickRemoveTag = (event) => {
            const removeButton = event.target.closest('[data-dropdown=tag-remove]');
            if (!removeButton)
                return;
            event.preventDefault();
            event.stopPropagation();
            const tagElement = removeButton.closest('[data-tag-value]');
            if (!tagElement)
                return;
            const value = tagElement.dataset.tagValue;
            // Désélectionner l'option
            Array.from(this.select.options).forEach((option) => {
                if (option.value == value) {
                    option.selected = false;
                }
            });
            // Dispatch change event sur le select natif pour active-form
            this.select?.dispatchEvent(new Event('change', { bubbles: true }));
            this.swapItemClasses();
            this.updateDisplay();
        };
        onStopTrapFocus = () => {
            this.logger.trace('onStopTrapFocus');
            this.itemsPlace?.classList.add('hidden');
            // Reset search on close
            if (this.searchInput) {
                this.searchInput.value = '';
                this.filterItems('');
            }
            return Promise.resolve();
        };
        toggleMenu = () => {
            const isClosed = this.itemsPlace?.classList.contains('hidden');
            return new Promise((resolve) => {
                if (isClosed) {
                    // Opening menu
                    this.button.ariaExpanded = 'true';
                    // Find selected option to focus initially, or search input if searchable
                    const initialFocus = this.searchInput || this.itemsPlace?.querySelector('[aria-selected="true"]');
                    return this.trapFocusService.start(this.button, this.itemsPlace, this.element, undefined, this.onStopTrapFocus, initialFocus)
                        .then(() => {
                        this.logger.trace('toggleMenu opened');
                        this.itemsPlace?.classList.remove('hidden');
                        resolve(void 0);
                    });
                }
                else {
                    // Closing menu
                    this.button.ariaExpanded = 'false';
                    return this.trapFocusService.stop()
                        .then(() => {
                        this.logger.trace('toggleMenu closed');
                        this.itemsPlace?.classList.add('hidden');
                        resolve(void 0);
                    });
                }
            });
        };
        onClickToggleItem = (event) => {
            this.logger.trace('onClickItem', event);
            event.preventDefault();
            const element = event.target.closest('[data-value]');
            if (!element)
                return;
            const clickedValue = element.dataset.value;
            if (this.isMultiple) {
                // Toggle la sélection de l'option cliquée
                Array.from(this.select.options).forEach((option) => {
                    if (option.value == clickedValue) {
                        option.selected = !option.selected;
                    }
                });
                // Dispatch change event sur le select natif pour active-form
                this.select?.dispatchEvent(new Event('change', { bubbles: true }));
                // Ne pas fermer le menu en mode multiple
                this.swapItemClasses();
                this.updateDisplay();
            }
            else {
                // Mode simple : une seule sélection
                Array.from(this.select.options).forEach((option) => {
                    option.selected = option.value == clickedValue;
                });
                // Dispatch change event sur le select natif pour active-form
                this.select?.dispatchEvent(new Event('change', { bubbles: true }));
                this.synchSelect();
            }
        };
        onSearchInput = (event) => {
            const query = event.target.value;
            this.filterItems(query);
        };
        filterItems(query) {
            const normalizedQuery = query.toLowerCase().trim();
            let visibleCount = 0;
            this.itemsContainer?.querySelectorAll('[data-value]').forEach((item) => {
                const text = item.querySelector('[data-dropdown=item-text]')?.textContent?.toLowerCase() || '';
                const isVisible = normalizedQuery === '' || text.includes(normalizedQuery);
                if (isVisible) {
                    item.classList.remove('hidden');
                    visibleCount++;
                }
                else {
                    item.classList.add('hidden');
                }
            });
            // Show/hide empty message
            if (this.emptyMessage) {
                if (visibleCount === 0 && normalizedQuery !== '') {
                    this.emptyMessage.classList.remove('hidden');
                }
                else {
                    this.emptyMessage.classList.add('hidden');
                }
            }
        }
        synchSelect() {
            this.swapItemClasses();
            // Close menu
            return this.toggleMenu();
        }
        updateDisplay() {
            if (this.withTags) {
                this.updateTags();
            }
            else {
                this.updateButtonText();
            }
        }
        updateButtonText() {
            const selectedOptions = Array.from(this.select.options).filter(opt => opt.selected);
            if (selectedOptions.length === 0) {
                // Afficher le placeholder s'il existe
                const placeholder = Array.from(this.select.options).find(opt => opt.value === '');
                this.buttonText.innerHTML = placeholder?.innerHTML || '';
            }
            else if (selectedOptions.length === 1) {
                this.buttonText.innerHTML = selectedOptions[0].innerHTML;
            }
            else {
                this.buttonText.textContent = `${selectedOptions.length} sélectionnés`;
            }
        }
        updateTags() {
            if (!this.tagsContainer || !this.tagTemplate)
                return;
            const selectedOptions = Array.from(this.select.options).filter(opt => opt.selected && opt.value !== '');
            // Supprimer les tags existants (sauf le placeholder)
            this.tagsContainer.querySelectorAll('[data-tag-value]').forEach(tag => tag.remove());
            // Afficher/masquer le placeholder
            if (this.placeholder) {
                if (selectedOptions.length === 0) {
                    this.placeholder.classList.remove('hidden');
                }
                else {
                    this.placeholder.classList.add('hidden');
                }
            }
            // Créer les tags
            selectedOptions.forEach((option) => {
                const tagFragment = this.tagTemplate.content.cloneNode(true);
                const tagElement = tagFragment.querySelector('[data-tag-value]');
                const tagText = tagFragment.querySelector('[data-dropdown=tag-text]');
                tagElement.dataset.tagValue = option.value;
                tagText.textContent = option.textContent;
                this.tagsContainer?.appendChild(tagFragment);
            });
        }
        swapItemClasses() {
            Array.from(this.select.options).forEach((option) => {
                const item = this.itemsContainer?.querySelector(`[data-value="${option.value}"]`);
                if (!item)
                    return;
                const checkmark = item.querySelector('[data-dropdown=item-check]');
                // Récupérer les classes depuis les data-attributes de l'élément
                const itemInactiveClasses = item.dataset.classInactive?.split(' ') || [];
                const itemActiveClasses = item.dataset.classActive?.split(' ') || [];
                const checkInactiveClasses = checkmark?.dataset.classInactive?.split(' ') || [];
                const checkActiveClasses = checkmark?.dataset.classActive?.split(' ') || [];
                if (option.selected) {
                    // Swap vers active
                    item.classList.remove(...itemInactiveClasses);
                    item.classList.add(...itemActiveClasses);
                    checkmark?.classList.remove(...checkInactiveClasses);
                    checkmark?.classList.add(...checkActiveClasses);
                    // Update ARIA
                    item.setAttribute('aria-selected', 'true');
                    this.button?.setAttribute('aria-activedescendant', item.id);
                }
                else {
                    // Swap vers inactive
                    item.classList.remove(...itemActiveClasses);
                    item.classList.add(...itemInactiveClasses);
                    checkmark?.classList.remove(...checkActiveClasses);
                    checkmark?.classList.add(...checkInactiveClasses);
                    // Update ARIA
                    item.setAttribute('aria-selected', 'false');
                }
            });
            // Update display (text or tags)
            this.updateDisplay();
        }
        preparePanel() {
            this.logger.trace('preparePanel');
            if (!this.select) {
                throw new Error('Select element not found');
            }
            // Vider le container
            if (this.itemsContainer) {
                this.itemsContainer.innerHTML = '';
            }
            const options = Array.from(this.select.options);
            options.forEach((option) => {
                // @ts-ignore
                const item = this.optionTemplate.content.cloneNode(true);
                const button = item.querySelector('button');
                // ARIA attributes
                button.role = 'option';
                button.id = `bleet-option-${option.value}-${Math.random().toString(36).substring(2, 9)}`;
                // Content
                const itemText = item.querySelector('[data-dropdown=item-text]');
                const itemValue = item.querySelector('[data-value]');
                itemValue.dataset.value = option.value;
                itemText.innerHTML = option.innerHTML;
                this.itemsContainer?.append(item);
            });
            // Appliquer les classes active/inactive
            this.swapItemClasses();
        }
    });
    return _classThis;
})();

let BleetPasswordCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute('bleet-password')];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        button;
        iconHidden;
        iconVisible;
        input;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-password'), element = aurelia.resolve(aurelia.INode)) {
            this.logger = logger;
            this.element = element;
        }
        attaching() {
            this.button = this.element.querySelector('[data-password=toggle]') ?? undefined;
            this.iconHidden = this.button?.querySelector('[data-password=icon-hidden]') ?? undefined;
            this.iconVisible = this.button?.querySelector('[data-password=icon-visible]') ?? undefined;
            this.input = this.element.querySelector('input') ?? undefined;
        }
        attached() {
            this.button?.addEventListener('click', this.onToggle);
        }
        detaching() {
            this.button?.removeEventListener('click', this.onToggle);
        }
        onToggle = (event) => {
            event.preventDefault();
            const isPassword = this.input?.type === 'password';
            if (this.input) {
                this.input.type = isPassword ? 'text' : 'password';
            }
            this.iconHidden?.classList.toggle('hidden', isPassword);
            this.iconVisible?.classList.toggle('hidden', !isPassword);
        };
    });
    return _classThis;
})();

let BleetTabsCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute('bleet-tabs')];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        p;
        activeClasses = '';
        inactiveClasses = '';
        select;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-tabs'), element = aurelia.resolve(aurelia.INode), p = aurelia.resolve(aurelia.IPlatform)) {
            this.logger = logger;
            this.element = element;
            this.p = p;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            const activeButton = this.element.querySelector('[data-tabs^="tab-"][aria-selected="true"]');
            const inactiveButton = this.element.querySelector('[data-tabs^="tab-"][aria-selected="false"]');
            this.activeClasses = activeButton?.className || '';
            this.inactiveClasses = inactiveButton?.className || '';
            this.select = this.element.querySelector('select');
        }
        attached() {
            this.logger.trace('attached');
            this.element.querySelectorAll('[data-tabs^="tab-"]').forEach((button) => {
                button.addEventListener('click', this.onClickTab);
            });
            // Écouter le select mobile
            if (this.select) {
                this.select.addEventListener('change', this.onChangeSelect);
            }
        }
        detached() {
            this.logger.trace('detached');
            this.element.querySelectorAll('[data-tabs^="tab-"]').forEach((button) => {
                button.removeEventListener('click', this.onClickTab);
            });
            if (this.select) {
                this.select.removeEventListener('change', this.onChangeSelect);
            }
        }
        onClickTab = (event) => {
            this.logger.trace('onClickTab', event);
            event.preventDefault();
            const tabIndex = event.currentTarget.getAttribute('data-tabs')?.replace('tab-', '') || '0';
            // Mettre à jour les tabs
            this.element.querySelectorAll('[data-tabs^="tab-"]').forEach((button) => {
                const buttonTabIndex = button.getAttribute('data-tabs')?.replace('tab-', '') || '0';
                if (buttonTabIndex === tabIndex) {
                    button.setAttribute('aria-selected', 'true');
                    button.className = this.activeClasses;
                }
                else {
                    button.setAttribute('aria-selected', 'false');
                    button.className = this.inactiveClasses;
                }
            });
            // Mettre à jour les panels
            this.element.querySelectorAll('[data-tabs^="panel-"]').forEach((panel) => {
                const panelIndex = panel.getAttribute('data-tabs')?.replace('panel-', '') || '0';
                if (panelIndex === tabIndex) {
                    panel.classList.remove('hidden');
                    panel.setAttribute('aria-hidden', 'false');
                }
                else {
                    panel.classList.add('hidden');
                    panel.setAttribute('aria-hidden', 'true');
                }
            });
            // Synchroniser le select
            if (this.select) {
                this.select.value = tabIndex;
            }
        };
        onChangeSelect = (event) => {
            this.logger.trace('onChangeSelect', event);
            const tabIndex = this.select?.value;
            const button = this.element.querySelector(`[data-tabs="tab-${tabIndex}"]`);
            if (button) {
                button.click();
            }
        };
    });
    return _classThis;
})();

let BleetProfileCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-profile', defaultProperty: 'id' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        ea;
        element;
        transitionService;
        platform;
        trapFocusService;
        id = __runInitializers(this, _id_initializers, '');
        toggleButton = __runInitializers(this, _id_extraInitializers);
        panel;
        isOpen = false;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-profile'), ea = aurelia.resolve(aurelia.IEventAggregator), element = aurelia.resolve(aurelia.INode), transitionService = aurelia.resolve(ITransitionService), platform = aurelia.resolve(aurelia.IPlatform), trapFocusService = aurelia.resolve(ITrapFocusService)) {
            this.logger = logger;
            this.ea = ea;
            this.element = element;
            this.transitionService = transitionService;
            this.platform = platform;
            this.trapFocusService = trapFocusService;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.toggleButton = this.element.querySelector('[data-profile=toggle]');
            this.panel = this.element.querySelector('[data-profile=panel]');
        }
        attached() {
            this.logger.trace('attached');
            this.toggleButton?.addEventListener('click', this.onClickToggle);
        }
        detached() {
            this.logger.trace('detached');
            this.toggleButton?.removeEventListener('click', this.onClickToggle);
            if (this.isOpen) {
                this.trapFocusService.stop();
            }
        }
        onClickToggle = (event) => {
            this.logger.trace('onClickToggle', event);
            event.preventDefault();
            if (this.isOpen) {
                this.close();
            }
            else {
                this.open();
            }
        };
        onStopTrapFocus = () => {
            this.logger.trace('onStopTrapFocus');
            this.isOpen = false;
            this.toggleButton?.setAttribute('aria-expanded', 'false');
            this.transitionService.run(this.panel, (element) => {
                this.ea.publish(exports.Channels.Profile, { action: exports.ProfileAction.Close, id: this.id });
                this.ea.publish(exports.Channels.ProfileStatus, { status: exports.ProfileStatus.Closing, id: this.id });
                this.platform.requestAnimationFrame(() => {
                    element.classList.remove('opacity-100', 'scale-100');
                    element.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                });
            }, (element) => {
                element.classList.add('hidden');
                this.ea.publish(exports.Channels.ProfileStatus, { status: exports.ProfileStatus.Closed, id: this.id });
            });
            return Promise.resolve();
        };
        open() {
            this.logger.trace('open');
            this.isOpen = true;
            this.toggleButton?.setAttribute('aria-expanded', 'true');
            // Find first focusable item in panel
            const firstItem = this.panel?.querySelector('a, button');
            this.trapFocusService.start(this.toggleButton, this.panel, this.element, undefined, this.onStopTrapFocus, firstItem).then(() => {
                this.transitionService.run(this.panel, (element) => {
                    this.ea.publish(exports.Channels.Profile, { action: exports.ProfileAction.Open, id: this.id });
                    this.ea.publish(exports.Channels.ProfileStatus, { status: exports.ProfileStatus.Opening, id: this.id });
                    element.classList.remove('hidden');
                    this.platform.requestAnimationFrame(() => {
                        element.classList.add('opacity-100', 'scale-100');
                        element.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                    });
                }, () => {
                    this.ea.publish(exports.Channels.ProfileStatus, { status: exports.ProfileStatus.Opened, id: this.id });
                });
            });
        }
        close() {
            this.logger.trace('close');
            this.trapFocusService.stop();
        }
    });
    return _classThis;
})();

let BleetToasterTriggerCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-toaster-trigger', defaultProperty: 'id' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    let _color_decorators;
    let _color_initializers = [];
    let _color_extraInitializers = [];
    let _icon_decorators;
    let _icon_initializers = [];
    let _icon_extraInitializers = [];
    let _title_decorators;
    let _title_initializers = [];
    let _title_extraInitializers = [];
    let _content_decorators;
    let _content_initializers = [];
    let _content_extraInitializers = [];
    let _duration_decorators;
    let _duration_initializers = [];
    let _duration_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            _color_decorators = [aurelia.bindable()];
            _icon_decorators = [aurelia.bindable()];
            _title_decorators = [aurelia.bindable()];
            _content_decorators = [aurelia.bindable()];
            _duration_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, null, _color_decorators, { kind: "field", name: "color", static: false, private: false, access: { has: obj => "color" in obj, get: obj => obj.color, set: (obj, value) => { obj.color = value; } }, metadata: _metadata }, _color_initializers, _color_extraInitializers);
            __esDecorate(null, null, _icon_decorators, { kind: "field", name: "icon", static: false, private: false, access: { has: obj => "icon" in obj, get: obj => obj.icon, set: (obj, value) => { obj.icon = value; } }, metadata: _metadata }, _icon_initializers, _icon_extraInitializers);
            __esDecorate(null, null, _title_decorators, { kind: "field", name: "title", static: false, private: false, access: { has: obj => "title" in obj, get: obj => obj.title, set: (obj, value) => { obj.title = value; } }, metadata: _metadata }, _title_initializers, _title_extraInitializers);
            __esDecorate(null, null, _content_decorators, { kind: "field", name: "content", static: false, private: false, access: { has: obj => "content" in obj, get: obj => obj.content, set: (obj, value) => { obj.content = value; } }, metadata: _metadata }, _content_initializers, _content_extraInitializers);
            __esDecorate(null, null, _duration_decorators, { kind: "field", name: "duration", static: false, private: false, access: { has: obj => "duration" in obj, get: obj => obj.duration, set: (obj, value) => { obj.duration = value; } }, metadata: _metadata }, _duration_initializers, _duration_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        id = __runInitializers(this, _id_initializers, '');
        color = (__runInitializers(this, _id_extraInitializers), __runInitializers(this, _color_initializers, exports.UiColor.Info));
        icon = (__runInitializers(this, _color_extraInitializers), __runInitializers(this, _icon_initializers, exports.UiToastIcon.Info));
        title = (__runInitializers(this, _icon_extraInitializers), __runInitializers(this, _title_initializers, ''));
        content = (__runInitializers(this, _title_extraInitializers), __runInitializers(this, _content_initializers, ''));
        duration = (__runInitializers(this, _content_extraInitializers), __runInitializers(this, _duration_initializers, 0)); // Duration in milliseconds
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-toaster-trigger'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
        }
        attached() {
            this.logger.trace('attached');
            this.element.addEventListener('click', this.onClick);
        }
        detached() {
            this.logger.trace('detached');
            this.element.removeEventListener('click', this.onClick);
        }
        onClick = (__runInitializers(this, _duration_extraInitializers), (event) => {
            this.logger.trace('onClick', event);
            event.preventDefault();
            this.ea.publish(exports.Channels.Toaster, {
                action: exports.ToasterAction.Add, toast: {
                    id: this.id,
                    duration: this.duration,
                    color: this.color,
                    icon: this.icon,
                    title: this.title,
                    content: this.content
                }
            });
        });
    });
    return _classThis;
})();

let BleetAlertCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute('bleet-alert')];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        platform;
        transitionService;
        closeButton;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-alert'), element = aurelia.resolve(aurelia.INode), platform = aurelia.resolve(aurelia.IPlatform), transitionService = aurelia.resolve(ITransitionService)) {
            this.logger = logger;
            this.element = element;
            this.platform = platform;
            this.transitionService = transitionService;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.closeButton = this.element.querySelector('[data-alert=close]');
        }
        attached() {
            this.logger.trace('attached');
            this.closeButton?.addEventListener('click', this.onClose);
        }
        detached() {
            this.logger.trace('detached');
            this.closeButton?.removeEventListener('click', this.onClose);
        }
        onClose = (event) => {
            this.logger.trace('onClose', event);
            event.preventDefault();
            this.transitionService.run(this.element, (element) => {
                const currentHeight = element.scrollHeight;
                element.style.height = currentHeight + 'px';
                // Force reflow
                element.offsetHeight;
                element.style.height = '0px';
                element.classList.remove('opacity-100');
                element.classList.add('opacity-0');
            }, (element) => {
                element.classList.add('hidden');
                this.platform.requestAnimationFrame(() => {
                    element.style.height = '';
                    element.remove();
                });
            });
        };
    });
    return _classThis;
})();

let BleetDrawerTriggerCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-drawer-trigger', defaultProperty: 'id' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    let _url_decorators;
    let _url_initializers = [];
    let _url_extraInitializers = [];
    let _color_decorators;
    let _color_initializers = [];
    let _color_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            _url_decorators = [aurelia.bindable()];
            _color_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, null, _url_decorators, { kind: "field", name: "url", static: false, private: false, access: { has: obj => "url" in obj, get: obj => obj.url, set: (obj, value) => { obj.url = value; } }, metadata: _metadata }, _url_initializers, _url_extraInitializers);
            __esDecorate(null, null, _color_decorators, { kind: "field", name: "color", static: false, private: false, access: { has: obj => "color" in obj, get: obj => obj.color, set: (obj, value) => { obj.color = value; } }, metadata: _metadata }, _color_initializers, _color_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        id = __runInitializers(this, _id_initializers, '');
        url = (__runInitializers(this, _id_extraInitializers), __runInitializers(this, _url_initializers, ''));
        color = (__runInitializers(this, _url_extraInitializers), __runInitializers(this, _color_initializers, 'primary'));
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-drawer-trigger'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
        }
        attached() {
            this.logger.trace('attached');
            this.element.addEventListener('click', this.onClick);
        }
        detached() {
            this.logger.trace('detached');
            this.element.removeEventListener('click', this.onClick);
        }
        dispose() {
            this.logger.trace('dispose');
        }
        onClick = (__runInitializers(this, _color_extraInitializers), (event) => {
            this.logger.trace('onClick', event);
            event.preventDefault();
            this.ea.publish(exports.Channels.Drawer, {
                action: exports.DrawerAction.Toggle,
                id: this.id,
                url: this.url,
                color: this.color,
            });
        });
    });
    return _classThis;
})();

let BleetModalTriggerCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-modal-trigger', defaultProperty: 'id' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    let _url_decorators;
    let _url_initializers = [];
    let _url_extraInitializers = [];
    let _color_decorators;
    let _color_initializers = [];
    let _color_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            _url_decorators = [aurelia.bindable()];
            _color_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, null, _url_decorators, { kind: "field", name: "url", static: false, private: false, access: { has: obj => "url" in obj, get: obj => obj.url, set: (obj, value) => { obj.url = value; } }, metadata: _metadata }, _url_initializers, _url_extraInitializers);
            __esDecorate(null, null, _color_decorators, { kind: "field", name: "color", static: false, private: false, access: { has: obj => "color" in obj, get: obj => obj.color, set: (obj, value) => { obj.color = value; } }, metadata: _metadata }, _color_initializers, _color_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        id = __runInitializers(this, _id_initializers, '');
        url = (__runInitializers(this, _id_extraInitializers), __runInitializers(this, _url_initializers, ''));
        color = (__runInitializers(this, _url_extraInitializers), __runInitializers(this, _color_initializers, 'primary'));
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-modal-trigger'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
        }
        attached() {
            this.logger.trace('attached');
            this.element.addEventListener('click', this.onClick);
        }
        detached() {
            this.logger.trace('detached');
            this.element.removeEventListener('click', this.onClick);
        }
        dispose() {
            this.logger.trace('dispose');
        }
        onClick = (__runInitializers(this, _color_extraInitializers), (event) => {
            this.logger.trace('onClick', event);
            event.preventDefault();
            this.ea.publish(exports.Channels.Modal, {
                action: exports.ModalAction.Toggle,
                id: this.id,
                url: this.url,
                color: this.color,
            });
        });
    });
    return _classThis;
})();

let BleetPagerCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute('bleet-pager')];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        p;
        select;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-pager'), element = aurelia.resolve(aurelia.INode), p = aurelia.resolve(aurelia.IPlatform)) {
            this.logger = logger;
            this.element = element;
            this.p = p;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.select = this.element.querySelector('[data-pager="select"]');
        }
        attached() {
            this.logger.trace('attached');
            this.select?.addEventListener('change', this.onChangeSelect);
        }
        detached() {
            this.logger.trace('detached');
            this.select?.removeEventListener('change', this.onChangeSelect);
        }
        onChangeSelect = (event) => {
            this.logger.trace('onChangeSelect', event);
            const pageNumber = this.select?.value;
            const link = this.element.querySelector(`[data-pager="page-${pageNumber}"]`);
            if (link) {
                link.click();
            }
        };
    });
    return _classThis;
})();

let BleetUploadCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-upload', defaultProperty: 'endpoint' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _endpoint_decorators;
    let _endpoint_initializers = [];
    let _endpoint_extraInitializers = [];
    let _previewEndpoint_decorators;
    let _previewEndpoint_initializers = [];
    let _previewEndpoint_extraInitializers = [];
    let _deleteEndpoint_decorators;
    let _deleteEndpoint_initializers = [];
    let _deleteEndpoint_extraInitializers = [];
    let _accept_decorators;
    let _accept_initializers = [];
    let _accept_extraInitializers = [];
    let _maxFiles_decorators;
    let _maxFiles_initializers = [];
    let _maxFiles_extraInitializers = [];
    let _multiple_decorators;
    let _multiple_initializers = [];
    let _multiple_extraInitializers = [];
    let _chunkSize_decorators;
    let _chunkSize_initializers = [];
    let _chunkSize_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _endpoint_decorators = [aurelia.bindable];
            _previewEndpoint_decorators = [aurelia.bindable()];
            _deleteEndpoint_decorators = [aurelia.bindable()];
            _accept_decorators = [aurelia.bindable()];
            _maxFiles_decorators = [aurelia.bindable()];
            _multiple_decorators = [aurelia.bindable()];
            _chunkSize_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _endpoint_decorators, { kind: "field", name: "endpoint", static: false, private: false, access: { has: obj => "endpoint" in obj, get: obj => obj.endpoint, set: (obj, value) => { obj.endpoint = value; } }, metadata: _metadata }, _endpoint_initializers, _endpoint_extraInitializers);
            __esDecorate(null, null, _previewEndpoint_decorators, { kind: "field", name: "previewEndpoint", static: false, private: false, access: { has: obj => "previewEndpoint" in obj, get: obj => obj.previewEndpoint, set: (obj, value) => { obj.previewEndpoint = value; } }, metadata: _metadata }, _previewEndpoint_initializers, _previewEndpoint_extraInitializers);
            __esDecorate(null, null, _deleteEndpoint_decorators, { kind: "field", name: "deleteEndpoint", static: false, private: false, access: { has: obj => "deleteEndpoint" in obj, get: obj => obj.deleteEndpoint, set: (obj, value) => { obj.deleteEndpoint = value; } }, metadata: _metadata }, _deleteEndpoint_initializers, _deleteEndpoint_extraInitializers);
            __esDecorate(null, null, _accept_decorators, { kind: "field", name: "accept", static: false, private: false, access: { has: obj => "accept" in obj, get: obj => obj.accept, set: (obj, value) => { obj.accept = value; } }, metadata: _metadata }, _accept_initializers, _accept_extraInitializers);
            __esDecorate(null, null, _maxFiles_decorators, { kind: "field", name: "maxFiles", static: false, private: false, access: { has: obj => "maxFiles" in obj, get: obj => obj.maxFiles, set: (obj, value) => { obj.maxFiles = value; } }, metadata: _metadata }, _maxFiles_initializers, _maxFiles_extraInitializers);
            __esDecorate(null, null, _multiple_decorators, { kind: "field", name: "multiple", static: false, private: false, access: { has: obj => "multiple" in obj, get: obj => obj.multiple, set: (obj, value) => { obj.multiple = value; } }, metadata: _metadata }, _multiple_initializers, _multiple_extraInitializers);
            __esDecorate(null, null, _chunkSize_decorators, { kind: "field", name: "chunkSize", static: false, private: false, access: { has: obj => "chunkSize" in obj, get: obj => obj.chunkSize, set: (obj, value) => { obj.chunkSize = value; } }, metadata: _metadata }, _chunkSize_initializers, _chunkSize_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        endpoint = __runInitializers(this, _endpoint_initializers, '');
        previewEndpoint = (__runInitializers(this, _endpoint_extraInitializers), __runInitializers(this, _previewEndpoint_initializers, ''));
        deleteEndpoint = (__runInitializers(this, _previewEndpoint_extraInitializers), __runInitializers(this, _deleteEndpoint_initializers, ''));
        accept = (__runInitializers(this, _deleteEndpoint_extraInitializers), __runInitializers(this, _accept_initializers, ''));
        maxFiles = (__runInitializers(this, _accept_extraInitializers), __runInitializers(this, _maxFiles_initializers, 1));
        multiple = (__runInitializers(this, _maxFiles_extraInitializers), __runInitializers(this, _multiple_initializers, false));
        chunkSize = (__runInitializers(this, _multiple_extraInitializers), __runInitializers(this, _chunkSize_initializers, 512 * 1024));
        resumable = (__runInitializers(this, _chunkSize_extraInitializers), null);
        dropzone = null;
        browseButton = null;
        fileList = null;
        hiddenInput = null;
        previewTemplate = null;
        handledFiles = [];
        parentForm = null;
        csrfToken = null;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-upload'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.logger.trace('constructor');
        }
        attaching() {
            this.dropzone = this.element.querySelector('[data-upload=dropzone]');
            this.browseButton = this.element.querySelector('[data-upload=browse]');
            this.fileList = this.element.querySelector('[data-upload=list]');
            this.hiddenInput = this.element.querySelector('[data-upload=value]');
            this.previewTemplate = this.element.querySelector('[data-upload=preview-template]');
        }
        attached() {
            if (!this.endpoint || !this.dropzone) {
                this.logger.warn('Missing endpoint or dropzone');
                return;
            }
            if (this.element.hasAttribute('data-disabled')) {
                return;
            }
            this.parentForm = this.element.closest('form');
            this.extractCsrfToken();
            this.initResumable();
            this.setFiles(this.hiddenInput?.value || '');
        }
        detaching() {
            if (this.resumable && this.dropzone) {
                this.dropzone.removeEventListener('dragover', this.onDragEnter);
                this.dropzone.removeEventListener('dragenter', this.onDragEnter);
                this.dropzone.removeEventListener('dragleave', this.onDragLeave);
                this.dropzone.removeEventListener('drop', this.onDragLeave);
            }
        }
        extractCsrfToken() {
            if (!this.parentForm)
                return;
            const csrfInput = this.parentForm.querySelector('input[name=_csrf]');
            if (csrfInput) {
                this.csrfToken = {
                    name: csrfInput.name,
                    value: csrfInput.value
                };
            }
        }
        initResumable() {
            const resumableConfig = {
                target: this.endpoint,
                chunkSize: this.chunkSize,
                simultaneousUploads: 3,
                permanentErrors: [400, 404, 415, 422, 500, 501],
                maxChunkRetries: 0
            };
            if (this.accept) {
                const fileTypes = this.accept.split(/\s*,\s*/).filter(v => v.trim() !== '');
                resumableConfig.fileType = fileTypes;
                resumableConfig.fileTypeErrorCallback = (file) => {
                    this.showErrorToast(`Le fichier "${file.fileName}" n'est pas un type autorise (${fileTypes.map(t => t.toUpperCase()).join(', ')})`);
                };
            }
            if (this.csrfToken) {
                resumableConfig.headers = {
                    'X-CSRF-Token': this.csrfToken.value
                };
            }
            this.resumable = new Resumable(resumableConfig);
            if (!this.resumable.support) {
                this.logger.warn('Resumable.js not supported');
                return;
            }
            if (this.browseButton) {
                this.resumable.assignBrowse(this.browseButton, false);
            }
            if (this.dropzone) {
                this.resumable.assignDrop(this.dropzone);
                this.dropzone.addEventListener('dragover', this.onDragEnter);
                this.dropzone.addEventListener('dragenter', this.onDragEnter);
                this.dropzone.addEventListener('dragleave', this.onDragLeave);
                this.dropzone.addEventListener('drop', this.onDragLeave);
            }
            this.resumable.on('fileAdded', this.onFileAdded);
            this.resumable.on('fileSuccess', this.onFileSuccess);
            this.resumable.on('fileError', this.onFileError);
        }
        /**
         * Charge les fichiers depuis une valeur (initialisation)
         */
        setFiles(value) {
            const files = value.split(/\s*,\s*/).filter(v => v.trim() !== '');
            this.handledFiles = files.map(name => ({
                name,
                shortname: name.split(/.*[\/|\\]/).pop(),
                previewUrl: this.generatePreviewUrl(name),
                deleteUrl: this.generateDeleteUrl(name)
            }));
            this.renderFileList();
            this.updateHiddenInput();
        }
        /**
         * Remplace tous les fichiers par un seul (mode single)
         */
        setFile(name, file = null) {
            // Supprimer les anciens fichiers temporaires
            this.handledFiles.forEach(f => {
                if (f.file && this.resumable) {
                    this.resumable.removeFile(f.file);
                }
                this.deleteFileOnServer(f.name);
            });
            this.handledFiles = [{
                    name,
                    shortname: name.split(/.*[\/|\\]/).pop(),
                    previewUrl: this.generatePreviewUrl(name),
                    deleteUrl: this.generateDeleteUrl(name),
                    file
                }];
            this.renderFileList();
            this.updateHiddenInput();
        }
        /**
         * Ajoute un fichier (mode multiple)
         */
        appendFile(name, file = null) {
            this.handledFiles.push({
                name,
                shortname: name.split(/.*[\/|\\]/).pop(),
                previewUrl: this.generatePreviewUrl(name),
                deleteUrl: this.generateDeleteUrl(name),
                file
            });
            this.renderFileList();
            this.updateHiddenInput();
        }
        /**
         * Supprime un fichier
         */
        onRemove(handledFile, evt) {
            evt.stopPropagation();
            evt.preventDefault();
            const index = this.handledFiles.findIndex(f => f.name === handledFile.name);
            if (index === -1)
                return;
            if (handledFile.file && this.resumable) {
                this.resumable.removeFile(handledFile.file);
            }
            this.deleteFileOnServer(handledFile.name);
            this.handledFiles.splice(index, 1);
            this.renderFileList();
            this.updateHiddenInput();
        }
        deleteFileOnServer(name) {
            // Ne supprimer que les fichiers temporaires
            if (!name || !name.startsWith('@bltmp/'))
                return;
            const deleteUrl = this.generateDeleteUrl(name);
            if (!deleteUrl)
                return;
            fetch(deleteUrl, {
                method: 'DELETE',
                headers: this.csrfToken ? {
                    'X-CSRF-Token': this.csrfToken.value
                } : {}
            }).catch(e => this.logger.error('Delete failed', e));
        }
        generatePreviewUrl(name) {
            if (!this.previewEndpoint)
                return '';
            return this.previewEndpoint.replace('__name__', encodeURIComponent(name));
        }
        generateDeleteUrl(name) {
            if (!this.deleteEndpoint)
                return '';
            return this.deleteEndpoint.replace('__name__', encodeURIComponent(name));
        }
        updateHiddenInput() {
            if (!this.hiddenInput)
                return;
            this.hiddenInput.value = this.handledFiles.map(f => f.name).join(', ');
            this.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
        renderFileList() {
            if (!this.fileList || !this.previewTemplate)
                return;
            this.fileList.innerHTML = '';
            this.handledFiles.forEach(handledFile => {
                const fragment = this.previewTemplate.content.cloneNode(true);
                const item = fragment.firstElementChild;
                // Preview link
                const previewLink = item.querySelector('[data-upload=preview-link]');
                if (previewLink) {
                    previewLink.href = handledFile.previewUrl ? `${handledFile.previewUrl}&original=1` : '#';
                }
                // Preview image et icon
                const previewImage = item.querySelector('[data-upload=preview-image]');
                const previewIcon = item.querySelector('[data-upload=preview-icon]');
                if (handledFile.previewUrl) {
                    this.loadPreview(previewImage, previewIcon, handledFile);
                }
                // Si pas de previewUrl, l'icône reste visible (hidden est sur l'image par défaut)
                // Nom du fichier
                const nameEl = item.querySelector('[data-upload=preview-name]');
                if (nameEl) {
                    nameEl.textContent = handledFile.shortname || '';
                }
                // Bouton supprimer
                const removeBtn = item.querySelector('[data-upload=preview-remove]');
                if (removeBtn) {
                    removeBtn.addEventListener('click', (e) => this.onRemove(handledFile, e));
                }
                this.fileList.appendChild(fragment);
            });
        }
        loadPreview(previewImage, previewIcon, handledFile) {
            const shortname = handledFile.shortname || '';
            if (shortname.toLowerCase().endsWith('.svg')) {
                // SVG : fetch et inline dans le container parent (le lien)
                fetch(handledFile.previewUrl)
                    .then(response => {
                    if (!response.ok)
                        throw new Error('Failed to load SVG');
                    return response.text();
                })
                    .then(svgContent => {
                    // Cacher l'icône par défaut
                    previewIcon.classList.add('hidden');
                    // Insérer le SVG à la place de l'image
                    previewImage.insertAdjacentHTML('afterend', svgContent);
                    const svg = previewImage.parentElement?.querySelector('svg:not([data-upload])');
                    if (svg) {
                        svg.classList.add('size-full');
                        svg.removeAttribute('width');
                        svg.removeAttribute('height');
                    }
                })
                    .catch(() => {
                    // Garder l'icône visible en cas d'erreur
                });
            }
            else {
                // Autres fichiers : utiliser l'image du template
                previewImage.src = handledFile.previewUrl;
                previewImage.alt = shortname;
                previewImage.onload = () => {
                    previewImage.classList.remove('hidden');
                    previewIcon.classList.add('hidden');
                };
                previewImage.onerror = () => {
                    // Garder l'icône visible en cas d'erreur
                };
            }
        }
        showErrorToast(message) {
            this.ea.publish(exports.Channels.Toaster, {
                action: exports.ToasterAction.Add,
                toast: {
                    id: `upload-error-${Date.now()}`,
                    duration: 5000,
                    color: exports.UiColor.Danger,
                    icon: exports.UiToastIcon.Danger,
                    title: 'Erreur',
                    content: message
                }
            });
        }
        // Resumable.js event handlers
        onDragEnter = (evt) => {
            evt.preventDefault();
            const dt = evt.dataTransfer;
            if (dt && dt.types.indexOf('Files') >= 0) {
                evt.stopPropagation();
                dt.dropEffect = 'copy';
                this.dropzone?.classList.add('border-primary-600', 'bg-primary-50');
            }
        };
        onDragLeave = (evt) => {
            this.dropzone?.classList.remove('border-primary-600', 'bg-primary-50');
        };
        onFileAdded = (file, event) => {
            this.logger.debug('onFileAdded', file.fileName);
            this.resumable?.upload();
        };
        onFileSuccess = (file, serverMessage) => {
            this.logger.debug('onFileSuccess', file.fileName, serverMessage);
            try {
                const response = JSON.parse(serverMessage);
                if (!response.finalFilename) {
                    throw new Error('Missing finalFilename in response');
                }
                const finalName = `@bltmp/${response.finalFilename}`;
                if (!this.multiple) {
                    this.setFile(finalName, file);
                }
                else {
                    this.appendFile(finalName, file);
                }
            }
            catch (e) {
                this.logger.error('Failed to parse server response', e);
                this.showErrorToast('Reponse serveur invalide');
            }
        };
        onFileError = (file, message) => {
            this.logger.error('onFileError', file.fileName, message);
            this.showErrorToast(`Echec de l'upload de "${file.fileName}"`);
        };
    });
    return _classThis;
})();

/**
 * Generic AJAX trigger attribute for elements.
 * Placed on the element that triggers the AJAX call.
 * Looks up parent form with closest() to get URL/verb if not specified.
 * Sends only the inputs contained within this.element.
 * Pessimistic UI: state changes only on server response.
 *
 * Bindables:
 *   - url (primary): URL to call. Falls back to closest form.action.
 *   - verb: HTTP method. Falls back to closest form.method or 'POST'.
 *   - event: DOM event to listen for (default: 'click')
 *   - collect: If set, also collects inputs with data-ajaxify="{collect}" from closest form
 */
let BleetAjaxifyTriggerCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-ajaxify-trigger', defaultProperty: 'url' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _url_decorators;
    let _url_initializers = [];
    let _url_extraInitializers = [];
    let _verb_decorators;
    let _verb_initializers = [];
    let _verb_extraInitializers = [];
    let _event_decorators;
    let _event_initializers = [];
    let _event_extraInitializers = [];
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _url_decorators = [aurelia.bindable];
            _verb_decorators = [aurelia.bindable()];
            _event_decorators = [aurelia.bindable()];
            _id_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _url_decorators, { kind: "field", name: "url", static: false, private: false, access: { has: obj => "url" in obj, get: obj => obj.url, set: (obj, value) => { obj.url = value; } }, metadata: _metadata }, _url_initializers, _url_extraInitializers);
            __esDecorate(null, null, _verb_decorators, { kind: "field", name: "verb", static: false, private: false, access: { has: obj => "verb" in obj, get: obj => obj.verb, set: (obj, value) => { obj.verb = value; } }, metadata: _metadata }, _verb_initializers, _verb_extraInitializers);
            __esDecorate(null, null, _event_decorators, { kind: "field", name: "event", static: false, private: false, access: { has: obj => "event" in obj, get: obj => obj.event, set: (obj, value) => { obj.event = value; } }, metadata: _metadata }, _event_initializers, _event_extraInitializers);
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        apiService;
        url = __runInitializers(this, _url_initializers, '');
        verb = (__runInitializers(this, _url_extraInitializers), __runInitializers(this, _verb_initializers, ''));
        event = (__runInitializers(this, _verb_extraInitializers), __runInitializers(this, _event_initializers, 'click'));
        id = (__runInitializers(this, _event_extraInitializers), __runInitializers(this, _id_initializers, ''));
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-ajaxify-trigger'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator), apiService = aurelia.resolve(IApiService)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.apiService = apiService;
            this.logger.trace('constructor');
        }
        attached() {
            this.logger.trace('attached', { url: this.url, verb: this.verb, event: this.event });
            this.element.addEventListener(this.event, this.onTrigger);
        }
        detached() {
            this.logger.trace('detached');
            this.element.removeEventListener(this.event, this.onTrigger);
        }
        onTrigger = (__runInitializers(this, _id_extraInitializers), (event) => {
            event.preventDefault();
            event.stopPropagation();
            this.logger.trace('onTrigger', event);
            const form = this.element.closest('form');
            const url = this.resolveUrl(form);
            const verb = this.resolveVerb(form);
            if (!url) {
                this.logger.warn('No URL found for ajaxify-trigger');
                return;
            }
            this.logger.debug('onTrigger', { url, verb });
            // Build FormData from inputs INSIDE this.element only
            const formData = this.buildFormData(event);
            this.apiService
                .url(url)
                .fromMultipart(formData)
                .request(verb)
                .then((response) => {
                this.logger.debug('response', response.body);
                // Update element from response HTML
                if (response.body.element) {
                    this.updateElement(response.body.element);
                }
                // Show toast if provided
                if (response.body.toast) {
                    this.ea.publish(exports.Channels.Toaster, {
                        action: exports.ToasterAction.Add,
                        toast: response.body.toast
                    });
                }
                // Trigger ajaxify refresh if provided
                if (response.body.ajaxify) {
                    this.ea.publish(exports.Channels.Ajaxify, response.body.ajaxify);
                }
            })
                .catch((error) => {
                this.logger.error('AJAX request failed', error);
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Add,
                    toast: {
                        color: exports.UiColor.Danger,
                        title: 'Erreur',
                        content: 'Une erreur est survenue.',
                        duration: 5000,
                    }
                });
            });
        });
        buildFormData(event) {
            const formData = new FormData();
            // Get all inputs inside this.element
            const inputs = this.element.querySelectorAll('input, select, textarea');
            this.appendInputsToFormData(formData, inputs);
            // Capture submitter button name/value (for submit events)
            if (event instanceof SubmitEvent && event.submitter) {
                const submitter = event.submitter;
                if (submitter.name) {
                    formData.append(submitter.name, submitter.value || '');
                }
            }
            // Also get CSRF token from parent form if exists
            const form = this.element.closest('form');
            if (form) {
                const csrfInput = form.querySelector('input[name="_csrf"]');
                if (csrfInput) {
                    formData.append('_csrf', csrfInput.value);
                }
                // If id is set, also collect inputs with data-ajaxify="{id}" from the form
                if (this.id) {
                    const ajaxifyInputs = form.querySelectorAll(`[data-ajaxify="${this.id}"]`);
                    this.appendInputsToFormData(formData, ajaxifyInputs);
                }
            }
            return formData;
        }
        appendInputsToFormData(formData, inputs) {
            for (const input of Array.from(inputs)) {
                if (input instanceof HTMLInputElement) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        if (input.checked && input.name) {
                            formData.append(input.name, input.value || 'on');
                        }
                    }
                    else if (input.name) {
                        formData.append(input.name, input.value);
                    }
                }
                else if (input instanceof HTMLSelectElement && input.name) {
                    formData.append(input.name, input.value);
                }
                else if (input instanceof HTMLTextAreaElement && input.name) {
                    formData.append(input.name, input.value);
                }
            }
        }
        resolveUrl(form) {
            if (this.url) {
                return this.url;
            }
            if (form) {
                return form.action || '';
            }
            return '';
        }
        resolveVerb(form) {
            if (this.verb) {
                return this.verb;
            }
            if (form) {
                return form.method || 'POST';
            }
            return 'POST';
        }
        updateElement(html) {
            const template = document.createElement('template');
            template.innerHTML = html.trim();
            const newElement = template.content.firstElementChild;
            if (newElement) {
                this.syncElement(this.element, newElement);
            }
        }
        syncElement(current, incoming) {
            // Sync attributes of current element
            for (const attr of Array.from(incoming.attributes)) {
                current.setAttribute(attr.name, attr.value);
            }
            // Remove attributes that don't exist in incoming
            for (const attr of Array.from(current.attributes)) {
                if (!incoming.hasAttribute(attr.name)) {
                    current.removeAttribute(attr.name);
                }
            }
            // Sync input values for form elements
            if (current instanceof HTMLInputElement && incoming instanceof HTMLInputElement) {
                if (current.type === 'checkbox' || current.type === 'radio') {
                    current.checked = incoming.checked;
                }
                else {
                    current.value = incoming.value;
                }
            }
            // Sync children recursively
            const currentChildren = Array.from(current.children);
            const incomingChildren = Array.from(incoming.children);
            for (let i = 0; i < incomingChildren.length; i++) {
                if (i < currentChildren.length) {
                    this.syncElement(currentChildren[i], incomingChildren[i]);
                }
            }
        }
    });
    return _classThis;
})();

let BleetPopoverCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-popover', defaultProperty: 'id' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        id = __runInitializers(this, _id_initializers, '');
        isOpen = (__runInitializers(this, _id_extraInitializers), false);
        subscription = null;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-popover'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.subscription = this.ea.subscribe(exports.Channels.Popover, this.onPopover);
        }
        attached() {
            this.logger.trace('attached');
        }
        detaching() {
            this.logger.trace('detaching');
            this.subscription?.dispose();
        }
        dispose() {
            this.logger.trace('dispose');
            this.subscription?.dispose();
        }
        onPopover = (payload) => {
            if (payload.id !== this.id)
                return;
            switch (payload.action) {
                case exports.PopoverAction.Open:
                    this.open(payload.rect);
                    break;
                case exports.PopoverAction.Close:
                    this.close();
                    break;
                case exports.PopoverAction.Toggle:
                    if (this.isOpen) {
                        this.close();
                    }
                    else {
                        this.open(payload.rect);
                    }
                    break;
            }
        };
        open(rect) {
            this.isOpen = true;
            this.element.classList.add('is-open');
            if (rect) {
                this.positionAt(rect);
            }
            this.ea.publish(exports.Channels.PopoverStatus, {
                status: exports.PopoverStatus.Opened,
                id: this.id,
            });
        }
        close() {
            this.isOpen = false;
            this.element.classList.remove('is-open');
            this.ea.publish(exports.Channels.PopoverStatus, {
                status: exports.PopoverStatus.Closed,
                id: this.id,
            });
        }
        positionAt(rect) {
            this.element.style.visibility = 'hidden';
            this.element.style.display = 'block';
            const popoverRect = this.element.getBoundingClientRect();
            // Center above trigger
            let top = rect.top - popoverRect.height - 10;
            let left = rect.left + (rect.width / 2) - (popoverRect.width / 2);
            // Fallback below if not enough space above
            if (top < 4)
                top = rect.bottom + 10;
            // Clamp to viewport
            if (left < 4)
                left = 4;
            if (left + popoverRect.width > window.innerWidth - 4) {
                left = window.innerWidth - popoverRect.width - 4;
            }
            this.element.style.top = `${top}px`;
            this.element.style.left = `${left}px`;
            this.element.style.visibility = '';
            this.element.style.display = '';
        }
    });
    return _classThis;
})();

let BleetPopoverTriggerCustomAttribute = (() => {
    let _classDecorators = [aurelia.customAttribute({ name: 'bleet-popover-trigger', defaultProperty: 'id' })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    let _absolute_decorators;
    let _absolute_initializers = [];
    let _absolute_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            _absolute_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, null, _absolute_decorators, { kind: "field", name: "absolute", static: false, private: false, access: { has: obj => "absolute" in obj, get: obj => obj.absolute, set: (obj, value) => { obj.absolute = value; } }, metadata: _metadata }, _absolute_initializers, _absolute_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        id = __runInitializers(this, _id_initializers, '');
        absolute = (__runInitializers(this, _id_extraInitializers), __runInitializers(this, _absolute_initializers, true));
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('bleet-popover-trigger'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.logger.trace('constructor');
        }
        attached() {
            this.logger.trace('attached');
            this.element.addEventListener('mouseenter', this.onMouseEnter);
            this.element.addEventListener('mouseleave', this.onMouseLeave);
        }
        detaching() {
            this.logger.trace('detaching');
            this.element.removeEventListener('mouseenter', this.onMouseEnter);
            this.element.removeEventListener('mouseleave', this.onMouseLeave);
        }
        dispose() {
            this.logger.trace('dispose');
        }
        onMouseEnter = (__runInitializers(this, _absolute_extraInitializers), () => {
            this.ea.publish(exports.Channels.Popover, {
                id: this.id,
                action: exports.PopoverAction.Open,
                rect: this.absolute ? this.element.getBoundingClientRect() : undefined,
            });
        });
        onMouseLeave = () => {
            this.ea.publish(exports.Channels.Popover, {
                id: this.id,
                action: exports.PopoverAction.Close,
            });
        };
    });
    return _classThis;
})();

let BleetOverlay = (() => {
    let _classDecorators = [aurelia.customElement({
            name: 'bleet-overlay',
            template: null,
        })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        platform;
        transitionService;
        disposable;
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('<bleet-overlay>'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator), platform = aurelia.resolve(aurelia.IPlatform), transitionService = aurelia.resolve(ITransitionService)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.platform = platform;
            this.transitionService = transitionService;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
        }
        attached() {
            this.logger.trace('attached');
            this.disposable = this.ea.subscribe(exports.Channels.Overlay, this.onOverlayEvent);
            this.element.addEventListener('click', this.onClickOverlay);
        }
        detached() {
            this.logger.trace('detached');
            this.element.removeEventListener('click', this.onClickOverlay);
            this.disposable?.dispose();
        }
        dispose() {
            this.logger.trace('dispose');
            this.disposable?.dispose();
        }
        toggle(fromOverlay = false) {
            if (this.element.classList.contains('hidden')) {
                this.open(fromOverlay);
            }
            else {
                this.close(fromOverlay);
            }
        }
        open(fromOverlay = false) {
            if (this.element.classList.contains('hidden')) {
                this.logger.trace('open');
                this.transitionService.run(this.element, (element) => {
                    if (fromOverlay) {
                        this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Open });
                    }
                    this.ea.publish(exports.Channels.OverlayStatus, { status: exports.OverlayStatus.Opening });
                    element.classList.remove('hidden');
                    this.platform.requestAnimationFrame(() => {
                        element.classList.remove('opacity-0');
                        element.classList.add('opacity-100');
                    });
                    this.logger.trace('open before()');
                }, (element) => {
                    this.ea.publish(exports.Channels.OverlayStatus, { status: exports.OverlayStatus.Opened });
                    this.logger.trace('open after()');
                });
            }
        }
        close(fromOverlay = false) {
            if (!this.element.classList.contains('hidden')) {
                this.transitionService.run(this.element, (element) => {
                    if (fromOverlay) {
                        this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Close });
                    }
                    this.ea.publish(exports.Channels.OverlayStatus, { status: exports.OverlayStatus.Closing });
                    element.classList.remove('opacity-100');
                    element.classList.add('opacity-0');
                    this.logger.trace('close before()');
                }, (element) => {
                    element.classList.add('hidden');
                    this.logger.trace('close after()');
                    this.ea.publish(exports.Channels.OverlayStatus, { status: exports.OverlayStatus.Closed });
                });
            }
        }
        onOverlayEvent = (data) => {
            if (data.action === exports.OverlayAction.Open) {
                this.logger.trace('onOverlayEvent', data);
                this.open();
            }
            else if (data.action === exports.OverlayAction.Close) {
                this.logger.trace('onOverlayEvent', data);
                this.close();
            }
            else if (data.action === exports.OverlayAction.Toggle) {
                this.logger.trace('onOverlayEvent', data);
                this.toggle();
            }
            else {
                this.logger.trace('onOverlayEvent unhandled', data);
            }
        };
        onClickOverlay = (event) => {
            this.logger.trace('onClickOverlay', event);
            event.preventDefault();
            this.close(true);
        };
    });
    return _classThis;
})();

var template$5 = `<template>
        <div class="rounded-md border-l-4 p-4 shadow-lg"
             class.bind="'border-'+color+'-300 bg-'+color+'-50'">
            <div class="flex">
                <div class="shrink-0">
                    <svg class="size-5"
                         class.bind="'text-'+color+'-500'"
                         xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path if.bind="icon == 'information-circle'" fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 0 1 .67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 1 1-.671-1.34l.041-.022ZM12 9a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/>
                        <path if.bind="icon == 'check-circle'" fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/>
                        <path if.bind="icon == 'exclamation-triangle'" fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003ZM12 8.25a.75.75 0 0 1 .75.75v3.75a.75.75 0 0 1-1.5 0V9a.75.75 0 0 1 .75-.75Zm0 8.25a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" clip-rule="evenodd"/>
                        <path if.bind="icon == 'x-circle'" fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-1.72 6.97a.75.75 0 1 0-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 1 0 1.06 1.06L12 13.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L13.06 12l1.72-1.72a.75.75 0 1 0-1.06-1.06L12 10.94l-1.72-1.72Z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium"
                       if.bind="title"
                       class.bind="'text-'+color+'-700'">
                        \${title}
                    </p>
                    <div class.bind="title?'mt-2':''">
                        <p class="text-sm"
                            class.bind="'text-'+color+'-700'"
                        innerHTML.bind="content">
                        </p>
                    </div>
                </div>
                <div class="ml-auto pl-3">
                    <button type="button"
                            type="button"
                            class="inline-flex rounded-md cursor-pointer"
                            class.bind="'bg-'+color+'-50 text-'+color+'-500 hover:bg-'+color+'-100'"
                            click.trigger="onClickRemove($event)"
                            data-toast="close">
                        <span class="sr-only">Fermer</span>
                        <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
                            <path fill-rule="evenodd" d="M5.47 5.47a.75.75 0 0 1 1.06 0L12 10.94l5.47-5.47a.75.75 0 1 1 1.06 1.06L13.06 12l5.47 5.47a.75.75 0 1 1-1.06 1.06L12 13.06l-5.47 5.47a.75.75 0 0 1-1.06-1.06L10.94 12 5.47 6.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
</template>`;

let BleetToast = (() => {
    let _classDecorators = [aurelia.customElement({
            name: 'bleet-toast',
            template: template$5
        })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    let _color_decorators;
    let _color_initializers = [];
    let _color_extraInitializers = [];
    let _icon_decorators;
    let _icon_initializers = [];
    let _icon_extraInitializers = [];
    let _title_decorators;
    let _title_initializers = [];
    let _title_extraInitializers = [];
    let _content_decorators;
    let _content_initializers = [];
    let _content_extraInitializers = [];
    let _duration_decorators;
    let _duration_initializers = [];
    let _duration_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable()];
            _color_decorators = [aurelia.bindable()];
            _icon_decorators = [aurelia.bindable()];
            _title_decorators = [aurelia.bindable()];
            _content_decorators = [aurelia.bindable()];
            _duration_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, null, _color_decorators, { kind: "field", name: "color", static: false, private: false, access: { has: obj => "color" in obj, get: obj => obj.color, set: (obj, value) => { obj.color = value; } }, metadata: _metadata }, _color_initializers, _color_extraInitializers);
            __esDecorate(null, null, _icon_decorators, { kind: "field", name: "icon", static: false, private: false, access: { has: obj => "icon" in obj, get: obj => obj.icon, set: (obj, value) => { obj.icon = value; } }, metadata: _metadata }, _icon_initializers, _icon_extraInitializers);
            __esDecorate(null, null, _title_decorators, { kind: "field", name: "title", static: false, private: false, access: { has: obj => "title" in obj, get: obj => obj.title, set: (obj, value) => { obj.title = value; } }, metadata: _metadata }, _title_initializers, _title_extraInitializers);
            __esDecorate(null, null, _content_decorators, { kind: "field", name: "content", static: false, private: false, access: { has: obj => "content" in obj, get: obj => obj.content, set: (obj, value) => { obj.content = value; } }, metadata: _metadata }, _content_initializers, _content_extraInitializers);
            __esDecorate(null, null, _duration_decorators, { kind: "field", name: "duration", static: false, private: false, access: { has: obj => "duration" in obj, get: obj => obj.duration, set: (obj, value) => { obj.duration = value; } }, metadata: _metadata }, _duration_initializers, _duration_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        platform;
        transitionService;
        id = __runInitializers(this, _id_initializers, '');
        color = (__runInitializers(this, _id_extraInitializers), __runInitializers(this, _color_initializers, exports.UiColor.Info));
        icon = (__runInitializers(this, _color_extraInitializers), __runInitializers(this, _icon_initializers, exports.UiToastIcon.Info));
        title = (__runInitializers(this, _icon_extraInitializers), __runInitializers(this, _title_initializers, ''));
        content = (__runInitializers(this, _title_extraInitializers), __runInitializers(this, _content_initializers, ''));
        duration = (__runInitializers(this, _content_extraInitializers), __runInitializers(this, _duration_initializers, 0)); // Duration in milliseconds
        added = (__runInitializers(this, _duration_extraInitializers), false);
        closeTimeout;
        // Classes Tailwind complètes pour éviter le purge
        colorClasses = {
            [exports.UiColor.Primary]: {
                container: 'border-primary-300 bg-primary-50',
                icon: 'text-primary-700',
                title: 'text-primary-700',
                content: 'text-primary-700',
                button: 'bg-primary-50 text-primary-500 hover:bg-primary-100'
            },
            [exports.UiColor.Secondary]: {
                container: 'border-secondary-300 bg-secondary-50',
                icon: 'text-secondary-700',
                title: 'text-secondary-700',
                content: 'text-secondary-700',
                button: 'bg-secondary-50 text-secondary-500 hover:bg-secondary-100'
            },
            [exports.UiColor.Success]: {
                container: 'border-success-300 bg-success-50',
                icon: 'text-success-700',
                title: 'text-success-700',
                content: 'text-success-700',
                button: 'bg-success-50 text-success-500 hover:bg-success-100'
            },
            [exports.UiColor.Danger]: {
                container: 'border-danger-300 bg-danger-50',
                icon: 'text-danger-700',
                title: 'text-danger-700',
                content: 'text-danger-700',
                button: 'bg-danger-50 text-danger-500 hover:bg-danger-100'
            },
            [exports.UiColor.Warning]: {
                container: 'border-warning-300 bg-warning-50',
                icon: 'text-warning-700',
                title: 'text-warning-700',
                content: 'text-warning-700',
                button: 'bg-warning-50 text-warning-500 hover:bg-warning-100'
            },
            [exports.UiColor.Info]: {
                container: 'border-info-300 bg-info-50',
                icon: 'text-info-700',
                title: 'text-info-700',
                content: 'text-info-700',
                button: 'bg-info-50 text-info-500 hover:bg-info-100'
            },
            [exports.UiColor.Accent]: {
                container: 'border-accent-300 bg-accent-50',
                icon: 'text-accent-700',
                title: 'text-accent-700',
                content: 'text-accent-700',
                button: 'bg-accent-50 text-accent-500 hover:bg-accent-100'
            },
        };
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('<bleet-toast>'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator), platform = aurelia.resolve(aurelia.IPlatform), transitionService = aurelia.resolve(ITransitionService)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.platform = platform;
            this.transitionService = transitionService;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
        }
        attached() {
            this.logger.trace('attached');
            if (!this.added) {
                this.added = true;
                this.logger.debug(`Toast added with ID: ${this.id}`);
                this.transitionService.run(this.element, (element) => {
                    if (this.duration > 0) {
                        this.closeTimeout = this.platform.setTimeout(() => {
                            this.close();
                        }, this.duration);
                    }
                    element.classList.add('opacity-100', 'translate-x-0');
                    element.classList.remove('opacity-0', 'translate-x-full');
                });
            }
        }
        detached() {
            this.logger.trace('detached');
        }
        onClickRemove(evt) {
            evt.preventDefault();
            this.logger.trace('onClickRemove');
            this.close();
        }
        close() {
            if (this.closeTimeout) {
                this.platform.clearTimeout(this.closeTimeout);
            }
            this.transitionService.run(this.element, (element) => {
                element.classList.add('opacity-0', 'translate-x-full');
                element.classList.remove('opacity-100', 'translate-x-0');
            }, (element) => {
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Remove,
                    toast: { id: this.id }
                });
            });
        }
    });
    return _classThis;
})();

var template$4 = `<template class="fixed top-4 right-0 z-70 pr-4 flex flex-col gap-4 pointer-events-none">
    <bleet-toast repeat.for="[id, toast] of toasts"
                class="w-90 max-w-full translate-x-full opacity-0 transition-all duration-500 ease-in-out pointer-events-auto"
        id.bind="id"
                 color.bind="toast.color"
                 icon.bind="toast.icon"
                 title.bind="toast.title"
                 content.bind="toast.content"
                 duration.bind="toast.duration"
    ></bleet-toast>
</template>`;

let BleetToaster = (() => {
    let _classDecorators = [aurelia.customElement({
            name: 'bleet-toaster',
            template: template$4
        })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        disposable;
        toasts = new Map();
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('<bleet-toaster>'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.disposable = this.ea.subscribe(exports.Channels.Toaster, this.onToasterEvent);
        }
        attached() {
            this.logger.trace('attached');
        }
        detached() {
            this.logger.trace('detached');
            this.disposable?.dispose();
        }
        dispose() {
            this.logger.trace('dispose');
            this.disposable?.dispose();
        }
        onToasterEvent = (data) => {
            this.logger.trace('onToasterEvent', data);
            if (data.action === exports.ToasterAction.Add && data.toast) {
                const toast = {
                    id: data.toast.id || crypto.randomUUID(),
                    color: data.toast.color || exports.UiColor.Info,
                    icon: data.toast.icon || exports.UiToastIcon.Info,
                    duration: data.toast.duration || 0,
                    title: data.toast.title,
                    content: data.toast.content,
                };
                // @ts-ignore
                if (!this.toasts.has(toast.id)) {
                    // @ts-ignore
                    this.toasts.set(toast.id, toast);
                    this.logger.debug(`Toast added with ID: ${toast.id}`);
                }
            }
            else if (data.action === exports.ToasterAction.Remove && data.toast?.id) {
                if (this.toasts.has(data.toast.id)) {
                    this.toasts.delete(data.toast.id);
                    this.logger.debug(`Toast removed with ID: ${data.toast.id}`);
                }
            }
            // Handle toaster events here
        };
    });
    return _classThis;
})();

let BleetToasterTrigger = (() => {
    let _classDecorators = [aurelia.customElement({
            name: 'bleet-toaster-trigger',
            template: null
        })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    let _color_decorators;
    let _color_initializers = [];
    let _color_extraInitializers = [];
    let _icon_decorators;
    let _icon_initializers = [];
    let _icon_extraInitializers = [];
    let _title_decorators;
    let _title_initializers = [];
    let _title_extraInitializers = [];
    let _content_decorators;
    let _content_initializers = [];
    let _content_extraInitializers = [];
    let _duration_decorators;
    let _duration_initializers = [];
    let _duration_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable()];
            _color_decorators = [aurelia.bindable()];
            _icon_decorators = [aurelia.bindable()];
            _title_decorators = [aurelia.bindable()];
            _content_decorators = [aurelia.bindable()];
            _duration_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, null, _color_decorators, { kind: "field", name: "color", static: false, private: false, access: { has: obj => "color" in obj, get: obj => obj.color, set: (obj, value) => { obj.color = value; } }, metadata: _metadata }, _color_initializers, _color_extraInitializers);
            __esDecorate(null, null, _icon_decorators, { kind: "field", name: "icon", static: false, private: false, access: { has: obj => "icon" in obj, get: obj => obj.icon, set: (obj, value) => { obj.icon = value; } }, metadata: _metadata }, _icon_initializers, _icon_extraInitializers);
            __esDecorate(null, null, _title_decorators, { kind: "field", name: "title", static: false, private: false, access: { has: obj => "title" in obj, get: obj => obj.title, set: (obj, value) => { obj.title = value; } }, metadata: _metadata }, _title_initializers, _title_extraInitializers);
            __esDecorate(null, null, _content_decorators, { kind: "field", name: "content", static: false, private: false, access: { has: obj => "content" in obj, get: obj => obj.content, set: (obj, value) => { obj.content = value; } }, metadata: _metadata }, _content_initializers, _content_extraInitializers);
            __esDecorate(null, null, _duration_decorators, { kind: "field", name: "duration", static: false, private: false, access: { has: obj => "duration" in obj, get: obj => obj.duration, set: (obj, value) => { obj.duration = value; } }, metadata: _metadata }, _duration_initializers, _duration_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        element;
        ea;
        p;
        id = __runInitializers(this, _id_initializers, '');
        color = (__runInitializers(this, _id_extraInitializers), __runInitializers(this, _color_initializers, exports.UiColor.Info));
        icon = (__runInitializers(this, _color_extraInitializers), __runInitializers(this, _icon_initializers, exports.UiToastIcon.Info));
        title = (__runInitializers(this, _icon_extraInitializers), __runInitializers(this, _title_initializers, ''));
        content = (__runInitializers(this, _title_extraInitializers), __runInitializers(this, _content_initializers, ''));
        duration = (__runInitializers(this, _content_extraInitializers), __runInitializers(this, _duration_initializers, 0)); // Duration in milliseconds
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('<bleet-toaster-trigger>'), element = aurelia.resolve(aurelia.INode), ea = aurelia.resolve(aurelia.IEventAggregator), p = aurelia.resolve(aurelia.IPlatform)) {
            this.logger = logger;
            this.element = element;
            this.ea = ea;
            this.p = p;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
        }
        attached() {
            this.logger.trace('attached');
            this.logger.debug(`Triggering toast with`, this.p.document.readyState);
            if (this.p.document.readyState === 'loading') {
                this.p.document.addEventListener('DOMContentLoaded', () => {
                    this.onAttach();
                }, { once: true });
            }
            else {
                this.onAttach();
            }
        }
        detached() {
            this.logger.trace('detached');
        }
        onAttach = (__runInitializers(this, _duration_extraInitializers), () => {
            this.logger.trace('onAttach');
            aurelia.queueTask(() => {
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Add, toast: {
                        id: this.id,
                        duration: this.duration,
                        color: this.color,
                        icon: this.icon,
                        title: this.title,
                        content: this.content
                    }
                });
            });
        });
    });
    return _classThis;
})();

var template$3 = `<template>
    <dialog ref="dialogElement"
            class="fixed inset-0 z-50 size-auto max-h-none max-w-none overflow-y-auto transition ease-in-out duration-300 bg-transparent backdrop:bg-transparent opacity-0">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl sm:my-8 sm:w-full sm:max-w-lg">

                <!-- Loader -->
                <div if.bind="loading" class="flex items-center justify-center py-12">
                    <svg class="animate-spin size-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Contenu -->
                <template else>
                    <!-- Header -->
                    <au-compose if.bind="headerView" template.bind="headerView"></au-compose>

                    <!-- Content -->
                    <au-compose if.bind="contentView" template.bind="contentView"></au-compose>

                    <!-- Footer -->
                    <au-compose if.bind="footerView" template.bind="footerView"></au-compose>
                </template>

            </div>
        </div>
    </dialog>
</template>`;

let BleetModal = (() => {
    let _classDecorators = [aurelia.customElement({ name: 'bleet-modal', template: template$3 })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    var BleetModal = class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            BleetModal = _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
        }
        logger;
        ea;
        platform;
        transitionService;
        svgService;
        apiService;
        id = __runInitializers(this, _id_initializers, '');
        dialogElement = __runInitializers(this, _id_extraInitializers);
        disposable;
        // State
        loading = false;
        color = exports.UiColor.Primary;
        icon = null;
        headerView = null;
        contentView = null;
        footerView = null;
        // Color classes — no Tailwind interpolation
        static ICON_BG_CLASSES = {
            [exports.UiColor.Primary]: 'bg-primary-100',
            [exports.UiColor.Secondary]: 'bg-secondary-100',
            [exports.UiColor.Success]: 'bg-success-100',
            [exports.UiColor.Danger]: 'bg-danger-100',
            [exports.UiColor.Warning]: 'bg-warning-100',
            [exports.UiColor.Info]: 'bg-info-100',
            [exports.UiColor.Accent]: 'bg-accent-100',
        };
        static ICON_TEXT_CLASSES = {
            [exports.UiColor.Primary]: 'text-primary-600',
            [exports.UiColor.Secondary]: 'text-secondary-600',
            [exports.UiColor.Success]: 'text-success-600',
            [exports.UiColor.Danger]: 'text-danger-600',
            [exports.UiColor.Warning]: 'text-warning-600',
            [exports.UiColor.Info]: 'text-info-600',
            [exports.UiColor.Accent]: 'text-accent-600',
        };
        static HEADER_BG_CLASSES = {
            [exports.UiColor.Primary]: 'bg-primary-600',
            [exports.UiColor.Secondary]: 'bg-secondary-600',
            [exports.UiColor.Success]: 'bg-success-600',
            [exports.UiColor.Danger]: 'bg-danger-600',
            [exports.UiColor.Warning]: 'bg-warning-600',
            [exports.UiColor.Info]: 'bg-info-600',
            [exports.UiColor.Accent]: 'bg-accent-600',
        };
        static CLOSE_BUTTON_TEXT_CLASSES = {
            [exports.UiColor.Primary]: 'text-primary-200',
            [exports.UiColor.Secondary]: 'text-secondary-200',
            [exports.UiColor.Success]: 'text-success-200',
            [exports.UiColor.Danger]: 'text-danger-200',
            [exports.UiColor.Warning]: 'text-warning-200',
            [exports.UiColor.Info]: 'text-info-200',
            [exports.UiColor.Accent]: 'text-accent-200',
        };
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('<bleet-modal>'), ea = aurelia.resolve(aurelia.IEventAggregator), platform = aurelia.resolve(aurelia.IPlatform), transitionService = aurelia.resolve(ITransitionService), svgService = aurelia.resolve(ISvgService), apiService = aurelia.resolve(IApiService)) {
            this.logger = logger;
            this.ea = ea;
            this.platform = platform;
            this.transitionService = transitionService;
            this.svgService = svgService;
            this.apiService = apiService;
        }
        // Getters
        get iconSvg() {
            if (!this.icon)
                return null;
            return this.svgService.get(this.icon);
        }
        get iconBgClass() {
            return BleetModal.ICON_BG_CLASSES[this.color] ?? BleetModal.ICON_BG_CLASSES[exports.UiColor.Primary];
        }
        get iconTextClass() {
            return BleetModal.ICON_TEXT_CLASSES[this.color] ?? BleetModal.ICON_TEXT_CLASSES[exports.UiColor.Primary];
        }
        get headerBgClass() {
            return BleetModal.HEADER_BG_CLASSES[this.color] ?? BleetModal.HEADER_BG_CLASSES[exports.UiColor.Primary];
        }
        get closeButtonTextClass() {
            return BleetModal.CLOSE_BUTTON_TEXT_CLASSES[this.color] ?? BleetModal.CLOSE_BUTTON_TEXT_CLASSES[exports.UiColor.Primary];
        }
        // Lifecycle
        attached() {
            this.disposable = this.ea.subscribe(exports.Channels.Modal, this.onModalEvent);
            this.dialogElement.addEventListener('close', this.onCloseEvent);
            this.dialogElement.addEventListener('cancel', this.onCancelEvent);
        }
        detached() {
            this.dialogElement.removeEventListener('close', this.onCloseEvent);
            this.dialogElement.removeEventListener('cancel', this.onCancelEvent);
            this.disposable?.dispose();
        }
        bindDialogEvents() {
            this.dialogElement.addEventListener('submit', this.onFormSubmit);
            this.dialogElement.addEventListener('click', this.onDialogClick);
        }
        unbindDialogEvents() {
            this.dialogElement.removeEventListener('submit', this.onFormSubmit);
            this.dialogElement.removeEventListener('click', this.onDialogClick);
        }
        // Handlers
        onModalEvent = (data) => {
            if (this.id && this.id !== '' && data.id === this.id) {
                if (data.action === exports.ModalAction.Open && this.dialogElement.open !== true) {
                    if (data.url) {
                        this.loadFromUrl(data.url);
                    }
                }
                else if (data.action === exports.ModalAction.Close && this.dialogElement.open === true) {
                    this.close();
                }
                else if (data.action === exports.ModalAction.Toggle) {
                    if (this.dialogElement.open === true) {
                        this.close();
                    }
                    else if (data.url) {
                        this.loadFromUrl(data.url);
                    }
                }
            }
        };
        onCloseEvent = (event) => {
            this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Close });
        };
        onCancelEvent = (event) => {
            this.close();
        };
        onDialogClick = (event) => {
            const target = event.target;
            if (target.closest('[data-modal="close"]')) {
                event.preventDefault();
                this.close();
            }
        };
        onFormSubmit = (event) => {
            const form = event.target.closest('form');
            if (!form)
                return;
            event.preventDefault();
            const formData = new FormData(form);
            const method = formData.get('_method') || form.getAttribute('method') || 'POST';
            this.apiService
                .url(form.action)
                .fromMultipart(formData)
                .request(method)
                .then((response) => {
                this.applyResponse(response.body);
            })
                .catch((error) => {
                this.logger.error('form submit failed', error);
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Add,
                    toast: { color: exports.UiColor.Danger, content: 'Erreur lors de l\'envoi' }
                });
            });
        };
        // AJAX
        loadFromUrl(url) {
            this.loading = true;
            this.open();
            this.apiService.url(url).get()
                .then((response) => {
                this.applyResponse(response.body);
            })
                .catch((error) => {
                this.logger.error('loadFromUrl failed', error);
                this.close();
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Add,
                    toast: { color: exports.UiColor.Danger, content: 'Erreur de chargement' }
                });
            })
                .finally(() => {
                this.loading = false;
            });
        }
        applyResponse(response) {
            // Style
            this.color = response.color ?? exports.UiColor.Primary;
            this.icon = response.icon ?? null;
            // Content
            if (response.header)
                this.headerView = response.header;
            if (response.content)
                this.contentView = response.content;
            if (response.footer)
                this.footerView = response.footer;
            // Actions
            this.executeActions(response);
        }
        executeActions(response) {
            // 1. Primary action
            if (response.action === exports.DialogAction.Close) {
                this.close();
            }
            // DialogAction.Keep → do nothing, dialog stays open
            // 2. Secondary actions (combinable)
            if (response.toast) {
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Add,
                    toast: response.toast
                });
            }
            if (response.ajaxify) {
                this.ea.publish(exports.Channels.Ajaxify, response.ajaxify);
            }
            else if (response.redirect) {
                this.platform.window.location.href = response.redirect;
            }
            else if (response.refresh) {
                this.platform.window.location.reload();
            }
        }
        // Open / Close
        open() {
            this.bindDialogEvents();
            this.transitionService.run(this.dialogElement, (element) => {
                this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Open });
                this.ea.publish(exports.Channels.ModalStatus, { status: exports.ModalStatus.Opening, id: this.id });
                element.showModal();
                this.platform.requestAnimationFrame(() => {
                    element.classList.add('opacity-100');
                    element.classList.remove('opacity-0');
                });
            }, () => {
                this.ea.publish(exports.Channels.ModalStatus, { status: exports.ModalStatus.Opened, id: this.id });
            });
        }
        close() {
            this.transitionService.run(this.dialogElement, (element) => {
                this.ea.publish(exports.Channels.ModalStatus, { status: exports.ModalStatus.Closing, id: this.id });
                this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Close });
                element.classList.add('opacity-0');
                element.classList.remove('opacity-100');
            }, (element) => {
                element.close();
                this.unbindDialogEvents();
                // Reset
                this.headerView = null;
                this.contentView = null;
                this.footerView = null;
                this.icon = null;
                this.color = exports.UiColor.Primary;
                this.loading = false;
                this.ea.publish(exports.Channels.ModalStatus, { status: exports.ModalStatus.Closed, id: this.id });
            });
        }
        static {
            __runInitializers(_classThis, _classExtraInitializers);
        }
    };
    return BleetModal = _classThis;
})();

var template$2 = `<template>
    <dialog ref="dialogElement"
            class="fixed inset-0 z-50 size-auto max-h-none max-w-none overflow-hidden backdrop:bg-transparent bg-transparent transform translate-x-full transition ease-in-out duration-300">
        <div class="absolute inset-0 pl-10 sm:pl-16 overflow-hidden">
            <div class="ml-auto flex flex-col h-full w-full sm:w-2/3 sm:min-w-md transform bg-white shadow-xl">

                <!-- Loader -->
                <div if.bind="loading" class="flex items-center justify-center h-full">
                    <svg class="animate-spin size-8 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>

                <!-- Contenu -->
                <template else>
                    <!-- Header (fixed) -->
                    <div class="shrink-0">
                        <au-compose if.bind="headerView" template.bind="headerView"></au-compose>
                    </div>

                    <!-- Content (scrollable) -->
                    <div class="flex-1 overflow-y-auto">
                        <au-compose if.bind="contentView" template.bind="contentView"></au-compose>
                    </div>

                    <!-- Footer (fixed) -->
                    <div class="shrink-0">
                        <au-compose if.bind="footerView" template.bind="footerView"></au-compose>
                    </div>
                </template>

            </div>
        </div>
    </dialog>
</template>`;

let BleetDrawer = (() => {
    let _classDecorators = [aurelia.customElement({ name: 'bleet-drawer', template: template$2 })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    var BleetDrawer = class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            BleetDrawer = _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
        }
        logger;
        ea;
        platform;
        transitionService;
        svgService;
        apiService;
        id = __runInitializers(this, _id_initializers, '');
        dialogElement = __runInitializers(this, _id_extraInitializers);
        disposable;
        // State
        loading = false;
        color = exports.UiColor.Primary;
        headerView = null;
        contentView = null;
        footerView = null;
        // Color classes for header
        static HEADER_BG_CLASSES = {
            [exports.UiColor.Primary]: 'bg-primary-700',
            [exports.UiColor.Secondary]: 'bg-secondary-700',
            [exports.UiColor.Success]: 'bg-success-700',
            [exports.UiColor.Danger]: 'bg-danger-700',
            [exports.UiColor.Warning]: 'bg-warning-700',
            [exports.UiColor.Info]: 'bg-info-700',
            [exports.UiColor.Accent]: 'bg-accent-700',
        };
        static CLOSE_BUTTON_TEXT_CLASSES = {
            [exports.UiColor.Primary]: 'text-primary-200',
            [exports.UiColor.Secondary]: 'text-secondary-200',
            [exports.UiColor.Success]: 'text-success-200',
            [exports.UiColor.Danger]: 'text-danger-200',
            [exports.UiColor.Warning]: 'text-warning-200',
            [exports.UiColor.Info]: 'text-info-200',
            [exports.UiColor.Accent]: 'text-accent-200',
        };
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('<bleet-drawer>'), ea = aurelia.resolve(aurelia.IEventAggregator), platform = aurelia.resolve(aurelia.IPlatform), transitionService = aurelia.resolve(ITransitionService), svgService = aurelia.resolve(ISvgService), apiService = aurelia.resolve(IApiService)) {
            this.logger = logger;
            this.ea = ea;
            this.platform = platform;
            this.transitionService = transitionService;
            this.svgService = svgService;
            this.apiService = apiService;
        }
        // Getters
        get headerBgClass() {
            return BleetDrawer.HEADER_BG_CLASSES[this.color] ?? BleetDrawer.HEADER_BG_CLASSES[exports.UiColor.Primary];
        }
        get closeButtonTextClass() {
            return BleetDrawer.CLOSE_BUTTON_TEXT_CLASSES[this.color] ?? BleetDrawer.CLOSE_BUTTON_TEXT_CLASSES[exports.UiColor.Primary];
        }
        // Lifecycle
        attached() {
            this.disposable = this.ea.subscribe(exports.Channels.Drawer, this.onDrawerEvent);
            this.dialogElement.addEventListener('close', this.onCloseEvent);
            this.dialogElement.addEventListener('cancel', this.onCancelEvent);
        }
        detached() {
            this.dialogElement.removeEventListener('close', this.onCloseEvent);
            this.dialogElement.removeEventListener('cancel', this.onCancelEvent);
            this.disposable?.dispose();
        }
        bindDialogEvents() {
            this.dialogElement.addEventListener('submit', this.onFormSubmit);
            this.dialogElement.addEventListener('click', this.onDialogClick);
        }
        unbindDialogEvents() {
            this.dialogElement.removeEventListener('submit', this.onFormSubmit);
            this.dialogElement.removeEventListener('click', this.onDialogClick);
        }
        // Handlers
        onDrawerEvent = (data) => {
            if (this.id && this.id !== '' && data.id === this.id) {
                if (data.action === exports.DrawerAction.Open && this.dialogElement.open !== true) {
                    if (data.url) {
                        this.loadFromUrl(data.url);
                    }
                }
                else if (data.action === exports.DrawerAction.Close && this.dialogElement.open === true) {
                    this.close();
                }
                else if (data.action === exports.DrawerAction.Toggle) {
                    if (this.dialogElement.open === true) {
                        this.close();
                    }
                    else if (data.url) {
                        this.loadFromUrl(data.url);
                    }
                }
            }
        };
        onCloseEvent = (event) => {
            this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Close });
        };
        onCancelEvent = (event) => {
            this.close();
        };
        onDialogClick = (event) => {
            const target = event.target;
            if (target.closest('[data-drawer="close"]')) {
                event.preventDefault();
                this.close();
            }
        };
        onFormSubmit = (event) => {
            const form = event.target.closest('form');
            if (!form)
                return;
            event.preventDefault();
            const formData = new FormData(form);
            // Capture submitter button name/value (not included by default in FormData)
            if (event.submitter instanceof HTMLButtonElement && event.submitter.name) {
                formData.append(event.submitter.name, event.submitter.value || '');
            }
            const method = formData.get('_method') || form.getAttribute('method') || 'POST';
            this.apiService
                .url(form.action)
                .fromMultipart(formData)
                .request(method)
                .then((response) => {
                this.applyResponse(response.body);
            })
                .catch((error) => {
                this.logger.error('form submit failed', error);
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Add,
                    toast: { color: exports.UiColor.Danger, content: 'Erreur lors de l\'envoi' }
                });
            });
        };
        loadFromUrl(url) {
            this.loading = true;
            this.open();
            this.apiService.url(url).get()
                .then((response) => {
                this.applyResponse(response.body);
            })
                .catch((error) => {
                this.logger.error('loadFromUrl failed', error);
                this.close();
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Add,
                    toast: { color: exports.UiColor.Danger, content: 'Erreur de chargement' }
                });
            })
                .finally(() => {
                this.loading = false;
            });
        }
        applyResponse(response) {
            this.color = response.color ?? exports.UiColor.Primary;
            if (response.header)
                this.headerView = response.header;
            if (response.content)
                this.contentView = response.content;
            if (response.footer)
                this.footerView = response.footer;
            this.executeActions(response);
        }
        executeActions(response) {
            // 1. Primary action
            if (response.action === exports.DialogAction.Close) {
                this.close();
            }
            // 2. Secondary actions (combinable)
            if (response.toast) {
                this.ea.publish(exports.Channels.Toaster, {
                    action: exports.ToasterAction.Add,
                    toast: response.toast
                });
            }
            if (response.ajaxify) {
                this.ea.publish(exports.Channels.Ajaxify, response.ajaxify);
            }
            else if (response.redirect) {
                this.platform.window.location.href = response.redirect;
            }
            else if (response.refresh) {
                this.platform.window.location.reload();
            }
        }
        // Open / Close — translate-x
        open() {
            this.bindDialogEvents();
            this.transitionService.run(this.dialogElement, (element) => {
                this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Open });
                this.ea.publish(exports.Channels.DrawerStatus, { status: exports.DrawerStatus.Opening, id: this.id });
                element.showModal();
                this.platform.requestAnimationFrame(() => {
                    element.classList.add('translate-x-0');
                    element.classList.remove('translate-x-full');
                });
            }, () => {
                this.ea.publish(exports.Channels.DrawerStatus, { status: exports.DrawerStatus.Opened, id: this.id });
            });
        }
        close() {
            this.transitionService.run(this.dialogElement, (element) => {
                this.ea.publish(exports.Channels.DrawerStatus, { status: exports.DrawerStatus.Closing, id: this.id });
                this.ea.publish(exports.Channels.Overlay, { action: exports.OverlayAction.Close });
                element.classList.add('translate-x-full');
                element.classList.remove('translate-x-0');
            }, (element) => {
                element.close();
                this.unbindDialogEvents();
                this.headerView = null;
                this.contentView = null;
                this.footerView = null;
                this.color = exports.UiColor.Primary;
                this.loading = false;
                this.ea.publish(exports.Channels.DrawerStatus, { status: exports.DrawerStatus.Closed, id: this.id });
            });
        }
        static {
            __runInitializers(_classThis, _classExtraInitializers);
        }
    };
    return BleetDrawer = _classThis;
})();

var template$1 = `<template>
    <au-slot if.bind="!ajaxedView"></au-slot>
    <au-compose if.bind="ajaxedView" template.bind="ajaxedView"></au-compose>
</template>`;

class AjaxifyCodec {
    static codec = {
        encode: (ctx) => {
            return Promise.resolve({
                ...ctx,
                headers: {
                    ...ctx.headers,
                    'X-Requested-For': 'Ajaxify',
                }
            });
        }
    };
}

let BleetAjaxify = (() => {
    let _classDecorators = [aurelia.customElement({ name: 'bleet-ajaxify', template: template$1 })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _id_decorators;
    let _id_initializers = [];
    let _id_extraInitializers = [];
    let _url_decorators;
    let _url_initializers = [];
    let _url_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _id_decorators = [aurelia.bindable()];
            _url_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _id_decorators, { kind: "field", name: "id", static: false, private: false, access: { has: obj => "id" in obj, get: obj => obj.id, set: (obj, value) => { obj.id = value; } }, metadata: _metadata }, _id_initializers, _id_extraInitializers);
            __esDecorate(null, null, _url_decorators, { kind: "field", name: "url", static: false, private: false, access: { has: obj => "url" in obj, get: obj => obj.url, set: (obj, value) => { obj.url = value; } }, metadata: _metadata }, _url_initializers, _url_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        ea;
        logger;
        apiService;
        id = __runInitializers(this, _id_initializers, '');
        url = (__runInitializers(this, _id_extraInitializers), __runInitializers(this, _url_initializers, void 0));
        ajaxedView = (__runInitializers(this, _url_extraInitializers), null);
        disposable;
        constructor(ea = aurelia.resolve(aurelia.IEventAggregator), logger = aurelia.resolve(aurelia.ILogger).scopeTo('<bleet-ajaxify>'), apiService = aurelia.resolve(IApiService)) {
            this.ea = ea;
            this.logger = logger;
            this.apiService = apiService;
            this.logger.trace('constructor');
        }
        attaching() {
            this.logger.trace('attaching');
            this.disposable = this.ea.subscribe(exports.Channels.Ajaxify, this.onEvent);
        }
        detaching() {
            this.logger.trace('detaching');
            this.disposable?.dispose();
        }
        dispose() {
            this.logger.trace('dispose');
            this.disposable?.dispose();
        }
        onEvent = (data) => {
            this.logger.trace('onEvent', data);
            if (data.action === exports.AjaxifyAction.Refresh) {
                if (data.id && data.id == this.id) {
                    this.logger.debug(`Refreshing ajaxify id=${this.id} from url=${this.url}`);
                    const url = data.url ? data.url : this.url;
                    if (url.length > 1) {
                        this.apiService
                            .url(url)
                            .withInputCodec(AjaxifyCodec.codec)
                            .toText()
                            .get()
                            .then((response) => {
                            this.logger.debug(`Received for id=${this.id}`);
                            this.ajaxedView = response.body;
                        })
                            .catch((error) => {
                            this.logger.error(`Error for id=${this.id}: `, error);
                        });
                    }
                }
            }
        };
    });
    return _classThis;
})();

var template = `<template>
    <input type="hidden" ref="hiddenField">
    <div ref="editorElement"></div>
</template>`;

const Link = Quill.import('formats/link');
class BlackcubeLink extends Link {
    static create(value) {
        let node = super.create(value);
        value = this.sanitize(value);
        node.setAttribute('href', value);
        if (value.startsWith("https://") || value.startsWith("http://") || value.startsWith("://")) ;
        else {
            node.removeAttribute('target');
        }
        return node;
    }
}
Quill.register(BlackcubeLink, true);
let Quilljs = (() => {
    let _classDecorators = [aurelia.customElement({ name: 'bleet-quilljs', template })];
    let _classDescriptor;
    let _classExtraInitializers = [];
    let _classThis;
    let _fieldId_decorators;
    let _fieldId_initializers = [];
    let _fieldId_extraInitializers = [];
    let _fieldName_decorators;
    let _fieldName_initializers = [];
    let _fieldName_extraInitializers = [];
    let _content_decorators;
    let _content_initializers = [];
    let _content_extraInitializers = [];
    let _options_decorators;
    let _options_initializers = [];
    let _options_extraInitializers = [];
    (class {
        static { _classThis = this; }
        static {
            const _metadata = typeof Symbol === "function" && Symbol.metadata ? Object.create(null) : void 0;
            _fieldId_decorators = [aurelia.bindable()];
            _fieldName_decorators = [aurelia.bindable()];
            _content_decorators = [aurelia.bindable()];
            _options_decorators = [aurelia.bindable()];
            __esDecorate(null, null, _fieldId_decorators, { kind: "field", name: "fieldId", static: false, private: false, access: { has: obj => "fieldId" in obj, get: obj => obj.fieldId, set: (obj, value) => { obj.fieldId = value; } }, metadata: _metadata }, _fieldId_initializers, _fieldId_extraInitializers);
            __esDecorate(null, null, _fieldName_decorators, { kind: "field", name: "fieldName", static: false, private: false, access: { has: obj => "fieldName" in obj, get: obj => obj.fieldName, set: (obj, value) => { obj.fieldName = value; } }, metadata: _metadata }, _fieldName_initializers, _fieldName_extraInitializers);
            __esDecorate(null, null, _content_decorators, { kind: "field", name: "content", static: false, private: false, access: { has: obj => "content" in obj, get: obj => obj.content, set: (obj, value) => { obj.content = value; } }, metadata: _metadata }, _content_initializers, _content_extraInitializers);
            __esDecorate(null, null, _options_decorators, { kind: "field", name: "options", static: false, private: false, access: { has: obj => "options" in obj, get: obj => obj.options, set: (obj, value) => { obj.options = value; } }, metadata: _metadata }, _options_initializers, _options_extraInitializers);
            __esDecorate(null, _classDescriptor = { value: _classThis }, _classDecorators, { kind: "class", name: _classThis.name, metadata: _metadata }, null, _classExtraInitializers);
            _classThis = _classDescriptor.value;
            if (_metadata) Object.defineProperty(_classThis, Symbol.metadata, { enumerable: true, configurable: true, writable: true, value: _metadata });
            __runInitializers(_classThis, _classExtraInitializers);
        }
        logger;
        platform;
        element;
        hiddenField;
        editorElement;
        quill;
        fieldId = __runInitializers(this, _fieldId_initializers, '');
        fieldName = (__runInitializers(this, _fieldId_extraInitializers), __runInitializers(this, _fieldName_initializers, ''));
        content = (__runInitializers(this, _fieldName_extraInitializers), __runInitializers(this, _content_initializers, ''));
        options = (__runInitializers(this, _content_extraInitializers), __runInitializers(this, _options_initializers, {
            theme: 'snow',
            modules: {
                toolbar: [
                    ['bold', 'italic', 'underline'],
                    [{ 'list': 'bullet' }],
                    ['link']
                ]
            },
            formats: ['bold', 'italic', 'link', 'underline', 'list']
        }));
        constructor(logger = aurelia.resolve(aurelia.ILogger).scopeTo('<bleet-quilljs>'), platform = aurelia.resolve(aurelia.IPlatform), element = aurelia.resolve(aurelia.INode)) {
            this.logger = logger;
            this.platform = platform;
            this.element = element;
        }
        attached() {
            this.logger.debug('Attached');
            if (this.fieldId) {
                this.hiddenField.id = this.fieldId;
            }
            if (this.fieldName) {
                this.hiddenField.name = this.fieldName;
            }
            this.hiddenField.value = this.content;
            this.editorElement.innerHTML = this.content;
            this.options.theme = 'snow';
            this.quill = new Quill(this.editorElement, this.options);
            this.quill.on('text-change', this.onTextChange);
        }
        onTextChange = (__runInitializers(this, _options_extraInitializers), () => {
            this.hiddenField.value = this.editorElement.querySelector('.ql-editor')?.innerHTML ?? '';
            this.logger.trace(this.hiddenField.value);
        });
        detaching() {
            this.quill.off('text-change', this.onTextChange);
            this.logger.debug('Detaching');
        }
    });
    return _classThis;
})();

class RequestCodec {
    static codec = {
        encode: (ctx) => {
            return Promise.resolve({
                ...ctx,
                headers: {
                    ...ctx.headers,
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
        }
    };
}

const DefaultComponents = [
    // attributes
    BleetAlertCustomAttribute,
    BleetBurgerCustomAttribute,
    BleetDrawerTriggerCustomAttribute,
    BleetDropdownCustomAttribute,
    BleetMenuCustomAttribute,
    BleetModalTriggerCustomAttribute,
    BleetPagerCustomAttribute,
    BleetProfileCustomAttribute,
    BleetPasswordCustomAttribute,
    BleetSelectCustomAttribute,
    BleetBadgeCustomAttribute,
    BleetTabsCustomAttribute,
    BleetToasterTriggerCustomAttribute,
    BleetUploadCustomAttribute,
    BleetAjaxifyTriggerCustomAttribute,
    BleetPopoverCustomAttribute,
    BleetPopoverTriggerCustomAttribute,
    // components
    BleetOverlay,
    BleetToast,
    BleetToaster,
    BleetToasterTrigger,
    BleetModal,
    BleetDrawer,
    BleetAjaxify,
    Quilljs
];
function createBleetConfiguration(optionsCallback) {
    return {
        register(container) {
            const configClass = container.get(IBleetConfiguration);
            configClass.setContainer(container);
            if (optionsCallback) {
                const options = configClass.getConfig();
                optionsCallback(options);
            }
            // Enregistrer l'interface HTTP (toujours disponible)
            configClass.registerTransportInterface(exports.Transport.Http, IHttpService);
            // Socketio sera résolu à la demande dans getTransport()
            return container.register(...DefaultComponents);
        },
        customize(callback) {
            return createBleetConfiguration(callback);
        }
    };
}
const BleetConfiguration = createBleetConfiguration();

exports.AjaxifyCodec = AjaxifyCodec;
exports.BleetAjaxify = BleetAjaxify;
exports.BleetAjaxifyTriggerCustomAttribute = BleetAjaxifyTriggerCustomAttribute;
exports.BleetAlertCustomAttribute = BleetAlertCustomAttribute;
exports.BleetBadgeCustomAttribute = BleetBadgeCustomAttribute;
exports.BleetBurgerCustomAttribute = BleetBurgerCustomAttribute;
exports.BleetConfiguration = BleetConfiguration;
exports.BleetDrawer = BleetDrawer;
exports.BleetDrawerTriggerCustomAttribute = BleetDrawerTriggerCustomAttribute;
exports.BleetDropdownCustomAttribute = BleetDropdownCustomAttribute;
exports.BleetMenuCustomAttribute = BleetMenuCustomAttribute;
exports.BleetModal = BleetModal;
exports.BleetModalTriggerCustomAttribute = BleetModalTriggerCustomAttribute;
exports.BleetOverlay = BleetOverlay;
exports.BleetPagerCustomAttribute = BleetPagerCustomAttribute;
exports.BleetPasswordCustomAttribute = BleetPasswordCustomAttribute;
exports.BleetPopoverCustomAttribute = BleetPopoverCustomAttribute;
exports.BleetPopoverTriggerCustomAttribute = BleetPopoverTriggerCustomAttribute;
exports.BleetProfileCustomAttribute = BleetProfileCustomAttribute;
exports.BleetQuilljs = Quilljs;
exports.BleetSelectCustomAttribute = BleetSelectCustomAttribute;
exports.BleetTabsCustomAttribute = BleetTabsCustomAttribute;
exports.BleetToast = BleetToast;
exports.BleetToaster = BleetToaster;
exports.BleetToasterTrigger = BleetToasterTrigger;
exports.BleetToasterTriggerCustomAttribute = BleetToasterTriggerCustomAttribute;
exports.BleetUploadCustomAttribute = BleetUploadCustomAttribute;
exports.CsrfCodec = CsrfCodec;
exports.IApiService = IApiService;
exports.IHttpService = IHttpService;
exports.ISocketioService = ISocketioService;
exports.IStorageService = IStorageService;
exports.ISvgService = ISvgService;
exports.ITransitionService = ITransitionService;
exports.ITrapFocusService = ITrapFocusService;
exports.RequestCodec = RequestCodec;
//# sourceMappingURL=index.js.map
