import {customElement, IDisposable, IEventAggregator, ILogger, INode, resolve} from "aurelia";
import {Channels, ToasterAction, UiColor, UiToastIcon} from '../enums/event-aggregator';
import {IToast, IToaster} from '../interfaces/event-aggregator';
import template from './bleet-toaster.html';

@customElement({
    name: 'bleet-toaster',
    template
})
export class BleetToaster {

    private disposable?: IDisposable;
    private toasts: Map<string, IToast> = new Map<string, IToast>();
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<bleet-toaster>'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
    ) {
        this.logger.trace('constructor')
    }

    public attaching()
    {
        this.logger.trace('attaching');
        this.disposable = this.ea.subscribe(Channels.Toaster, this.onToasterEvent);
    }

    public attached()
    {
        this.logger.trace('attached');

    }

    public detached()
    {
        this.logger.trace('detached');
        this.disposable?.dispose();
    }

    public dispose()
    {
        this.logger.trace('dispose');
        this.disposable?.dispose();
    }

    private onToasterEvent = (data: IToaster) => {
        this.logger.trace('onToasterEvent', data);
        if (data.action === ToasterAction.Add && data.toast) {
            const toast :IToast = {
                id: data.toast.id || crypto.randomUUID(),
                color: data.toast.color || UiColor.Info,
                icon: data.toast.icon || UiToastIcon.Info,
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
        } else if (data.action === ToasterAction.Remove && data.toast?.id) {
            if (this.toasts.has(data.toast.id)) {
                this.toasts.delete(data.toast.id);
                this.logger.debug(`Toast removed with ID: ${data.toast.id}`);
            }
        }
        // Handle toaster events here
    }

}