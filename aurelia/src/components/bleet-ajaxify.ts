import {customElement, bindable, IEventAggregator, resolve, ILogger, IDisposable} from 'aurelia';
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
        private ea: IEventAggregator = resolve(IEventAggregator),
        private logger: ILogger = resolve(ILogger).scopeTo('<bleet-ajaxify>'),
        private apiService: IApiService = resolve(IApiService),
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

    private onEvent = (data: IAjaxify) => {
        this.logger.trace('onEvent', data);
        if (data.action === AjaxifyAction.Refresh) {
            if (data.id && data.id == this.id) {
                this.logger.debug(`Refreshing ajaxify id=${this.id} from url=${this.url}`);
                const url = data.url ? data.url : this.url;
                if (url.length >1) {
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
            }
        }
    }
}
