import {bindable, customAttribute, IDisposable, IEventAggregator, ILogger, INode, resolve} from 'aurelia';
import {Channels, PopoverAction, PopoverStatus} from '../enums/event-aggregator';
import {IPopover, IPopoverStatus} from '../interfaces/event-aggregator';

@customAttribute({name: 'bleet-popover', defaultProperty: 'id'})
export class BleetPopoverCustomAttribute {
    @bindable id: string = '';

    private isOpen: boolean = false;
    private subscription: IDisposable | null = null;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-popover'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
    ) {
        this.logger.trace('constructor');
    }

    public attaching() {
        this.logger.trace('attaching');
        this.subscription = this.ea.subscribe(Channels.Popover, this.onPopover);
    }

    public attached() {
        this.logger.trace('attached');
    }

    public detaching() {
        this.logger.trace('detaching');
        this.subscription?.dispose();
    }

    public dispose() {
        this.logger.trace('dispose');
        this.subscription?.dispose();
    }

    private onPopover = (payload: IPopover) => {
        if (payload.id !== this.id) return;

        switch (payload.action) {
            case PopoverAction.Open:
                this.open(payload.rect);
                break;
            case PopoverAction.Close:
                this.close();
                break;
            case PopoverAction.Toggle:
                if (this.isOpen) {
                    this.close();
                } else {
                    this.open(payload.rect);
                }
                break;
        }
    };

    private open(rect?: DOMRect) {
        this.isOpen = true;
        this.element.classList.add('is-open');
        if (rect) {
            this.positionAt(rect);
        }
        this.ea.publish(Channels.PopoverStatus, <IPopoverStatus>{
            status: PopoverStatus.Opened,
            id: this.id,
        });
    }

    private close() {
        this.isOpen = false;
        this.element.classList.remove('is-open');
        this.ea.publish(Channels.PopoverStatus, <IPopoverStatus>{
            status: PopoverStatus.Closed,
            id: this.id,
        });
    }

    private positionAt(rect: DOMRect) {
        this.element.style.visibility = 'hidden';
        this.element.style.display = 'block';

        const popoverRect = this.element.getBoundingClientRect();

        // Center above trigger
        let top = rect.top - popoverRect.height - 10;
        let left = rect.left + (rect.width / 2) - (popoverRect.width / 2);

        // Fallback below if not enough space above
        if (top < 4) top = rect.bottom + 10;

        // Clamp to viewport
        if (left < 4) left = 4;
        if (left + popoverRect.width > window.innerWidth - 4) {
            left = window.innerWidth - popoverRect.width - 4;
        }

        this.element.style.top = `${top}px`;
        this.element.style.left = `${left}px`;
        this.element.style.visibility = '';
        this.element.style.display = '';
    }
}
