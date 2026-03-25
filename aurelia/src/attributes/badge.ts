import {bindable, customAttribute, IEventAggregator, ILogger, INode, resolve} from "aurelia";
import {BadgeAction, Channels} from '../enums/event-aggregator';
import {IBadge} from '../interfaces/event-aggregator';

@customAttribute({ name: 'bleet-badge', defaultProperty: 'id' })
export class BleetBadgeCustomAttribute
{
    private closeButton?: HTMLElement;
    @bindable id: string = '';
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-badge'),
        private readonly ea:IEventAggregator = resolve(IEventAggregator),
        private readonly element: HTMLButtonElement = resolve(INode) as HTMLButtonElement,
    ) {
        this.logger.trace('constructor')
    }

    public attached()
    {
        this.logger.trace('attached');
        this.closeButton = this.element.querySelector('[data-badge=remove]') ?? undefined;
        this.closeButton?.addEventListener('click', this.onClickRemove);

    }

    public detached()
    {
        this.logger.trace('detached');
        this.closeButton?.removeEventListener('click', this.onClickRemove);
    }

    private onClickRemove = (event: MouseEvent) => {
        this.logger.trace('onClickRemove', event);
        event.preventDefault();
        this.ea.publish(Channels.Badge, <IBadge>{action: BadgeAction.Remove, id: this.id});
        this.element.remove();
    }

}