import {bindable, customAttribute, IEventAggregator, ILogger, INode, resolve} from 'aurelia';
import {Channels, PopoverAction} from '../enums/event-aggregator';
import {IPopover} from '../interfaces/event-aggregator';

@customAttribute({name: 'bleet-popover-trigger', defaultProperty: 'id'})
export class BleetPopoverTriggerCustomAttribute {
    @bindable id: string = '';
    @bindable() absolute: boolean = true;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-popover-trigger'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
    ) {
        this.logger.trace('constructor');
    }

    public attached() {
        this.logger.trace('attached');
        this.element.addEventListener('mouseenter', this.onMouseEnter);
        this.element.addEventListener('mouseleave', this.onMouseLeave);
    }

    public detaching() {
        this.logger.trace('detaching');
        this.element.removeEventListener('mouseenter', this.onMouseEnter);
        this.element.removeEventListener('mouseleave', this.onMouseLeave);
    }

    public dispose() {
        this.logger.trace('dispose');
    }

    private onMouseEnter = () => {
        this.ea.publish(Channels.Popover, <IPopover>{
            id: this.id,
            action: PopoverAction.Open,
            rect: this.absolute ? this.element.getBoundingClientRect() : undefined,
        });
    };

    private onMouseLeave = () => {
        this.ea.publish(Channels.Popover, <IPopover>{
            id: this.id,
            action: PopoverAction.Close,
        });
    };
}
