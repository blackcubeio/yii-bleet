import {customElement, bindable, IEventAggregator, resolve, ILogger, IDisposable, INode} from 'aurelia';
import template from './bleet-ajaxify.html';
import {IApiService} from '../services/api-service';
import {IAjaxify} from '../interfaces/event-aggregator';
import {AjaxifyAction, Channels} from '../enums/event-aggregator';
import {AjaxifyCodec} from '../codecs/ajaxify-codec';

@customElement({name: 'bleet-ajaxify', template})
export class BleetAjaxify {
    @bindable() id: string = '';
    @bindable() url?: string;

    private ajaxedView: string | null = null;
    private disposable?: IDisposable;

    public constructor(
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<bleet-ajaxify>'),
        private readonly apiService: IApiService = resolve(IApiService),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
    ) {
        this.logger.trace('constructor');
    }

    public attaching() {
        this.logger.trace('attaching')
        this.disposable = this.ea.subscribe(Channels.Ajaxify, this.onEvent);
    }

    public detaching() {
        this.logger.trace('detaching');
        this.disposable?.dispose();
    }

    public dispose() {
        this.logger.trace('dispose');
        this.disposable?.dispose();
    }

    private findForm(): HTMLFormElement | null {
        return this.element.querySelector('form') ?? this.element.closest('form');
    }

    private onEvent = (data: IAjaxify) => {
        this.logger.trace('onEvent', data);
        if (!(data.id && data.id == this.id)) {
            return;
        }

        if (data.action === AjaxifyAction.Refresh) {
            this.handleRefresh(data);
        } else if (data.action === AjaxifyAction.Run) {
            this.handleRun(data);
        }
    }

    /**
     * Refresh: GET to fetch fresh content (existing behavior).
     */
    private handleRefresh(data: IAjaxify): void {
        const url = data.url ? data.url : this.url;
        if (!url || url.length <= 1) {
            return;
        }

        this.logger.debug(`Refresh id=${this.id} from url=${url}`);

        this.apiService
            .url(url)
            .withInputCodec(AjaxifyCodec.codec)
            .toText()
            .get<string>()
            .then((response) => {
                this.logger.debug(`Received for id=${this.id}`);
                this.ajaxedView = response.body;
            })
            .catch((error) => {
                this.logger.error(`Error for id=${this.id}: `, error);
            });
    }

    /**
     * Run: collect form data (or use provided body) and send with the specified method.
     * URL fallback: event url -> component url -> closest form action.
     */
    private handleRun(data: IAjaxify): void {
        let url = data.url || this.url;
        if (!url || url.length <= 1) {
            const form = this.findForm();
            if (form) {
                url = form.action;
            }
        }
        if (!url || url.length <= 1) {
            this.logger.warn(`No URL found for Run id=${this.id}`);
            return;
        }

        const method = (data.method || 'POST').toUpperCase();
        this.logger.debug(`Run id=${this.id} method=${method} url=${url}`);

        let builder = this.apiService
            .url(url)
            .withInputCodec(AjaxifyCodec.codec)
            .toText();

        if (data.body !== undefined) {
            if (data.body instanceof FormData) {
                builder = builder.fromMultipart(data.body);
            } else {
                builder = builder.fromJson(data.body);
            }
        } else {
            const form = this.findForm();
            if (form) {
                builder = builder.fromMultipart(new FormData(form));
            }
        }

        builder
            .request<string>(method)
            .then((response) => {
                this.logger.debug(`Received for id=${this.id}`);
                this.ajaxedView = response.body;
            })
            .catch((error) => {
                this.logger.error(`Error for id=${this.id}: `, error);
            });
    }
}
