import {customElement, IDisposable, IEventAggregator, ILogger, INode, IPlatform, resolve} from "aurelia";
import {Channels, OverlayAction, OverlayStatus} from '../enums/event-aggregator';
import {IOverlay, IOverlayStatus} from '../interfaces/event-aggregator';
import {ITransitionService} from '../services/transition-service';

@customElement({
    name: 'bleet-overlay',
    template: null,
})
export class BleetOverlay {

    private disposable?: IDisposable;
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<bleet-overlay>'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
    ) {
        this.logger.trace('constructor')
    }

    public attaching()
    {
        this.logger.trace('attaching');
    }

    public attached()
    {
        this.logger.trace('attached');
        this.disposable = this.ea.subscribe(Channels.Overlay, this.onOverlayEvent);
        this.element.addEventListener('click', this.onClickOverlay);
    }

    public detached()
    {
        this.logger.trace('detached');
        this.element.removeEventListener('click', this.onClickOverlay);
        this.disposable?.dispose();
    }
    public dispose()
    {
        this.logger.trace('dispose');
        this.disposable?.dispose();
    }

    private toggle(fromOverlay: boolean = false) {
        if (this.element.classList.contains('hidden')) {
            this.open(fromOverlay);
        } else {
            this.close(fromOverlay);
        }
    }
    private open(fromOverlay: boolean = false) {
        if (this.element.classList.contains('hidden')) {
            this.logger.trace('open');
            this.transitionService.run(this.element, (element: HTMLElement) => {
                if (fromOverlay) {
                    this.ea.publish(Channels.Overlay, <IOverlay>{action: OverlayAction.Open});
                }
                this.ea.publish(Channels.OverlayStatus, <IOverlayStatus>{status: OverlayStatus.Opening});
                element.classList.remove('hidden');
                this.platform.requestAnimationFrame(() => {
                    element.classList.remove('opacity-0');
                    element.classList.add('opacity-100');
                });
                this.logger.trace('open before()');
            }, (element: HTMLElement) => {
                this.ea.publish(Channels.OverlayStatus, <IOverlayStatus>{status: OverlayStatus.Opened});
                this.logger.trace('open after()');
            });
        }
    }
    private close(fromOverlay: boolean = false) {
        if (!this.element.classList.contains('hidden')) {
            this.transitionService.run(this.element, (element: HTMLElement) => {
                if (fromOverlay) {
                    this.ea.publish(Channels.Overlay, <IOverlay>{action: OverlayAction.Close});
                }
                this.ea.publish(Channels.OverlayStatus, <IOverlayStatus>{status: OverlayStatus.Closing});
                element.classList.remove('opacity-100');
                element.classList.add('opacity-0');
                this.logger.trace('close before()');
            }, (element: HTMLElement) => {
                element.classList.add('hidden');
                this.logger.trace('close after()');
                this.ea.publish(Channels.OverlayStatus, <IOverlayStatus>{status: OverlayStatus.Closed});
            });
        }
    }
    private onOverlayEvent = (data: IOverlay) => {
        if (data.action === OverlayAction.Open) {
            this.logger.trace('onOverlayEvent', data);
            this.open();
        } else if (data.action === OverlayAction.Close) {
            this.logger.trace('onOverlayEvent', data);
            this.close();
        } else if (data.action === OverlayAction.Toggle) {
            this.logger.trace('onOverlayEvent', data);
            this.toggle();
        } else {
            this.logger.trace('onOverlayEvent unhandled', data);
        }
    }
    private onClickOverlay = (event: MouseEvent) => {
        this.logger.trace('onClickOverlay', event);
        event.preventDefault();
        this.close(true);
    }

}