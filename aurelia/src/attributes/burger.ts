import {customAttribute, IEventAggregator, ILogger, INode, resolve} from "aurelia";
import {Channels, MenuAction} from '../enums/event-aggregator';
import {IMenu} from '../interfaces/event-aggregator';

@customAttribute('bleet-burger')
export class BleetBurgerCustomAttribute
{

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-burger'),
        private readonly ea:IEventAggregator = resolve(IEventAggregator),
        private readonly element: HTMLButtonElement = resolve(INode) as HTMLButtonElement,
    ) {
        this.logger.trace('constructor')
    }

    public attached()
    {
        this.logger.trace('attached');
        this.element.addEventListener('click', this.onClickButton);

    }

    public detached()
    {
        this.logger.trace('detached');
        this.element.removeEventListener('click', this.onClickButton);
    }

    private onClickButton = (event: MouseEvent) => {
        this.logger.trace('onClickButton', event);
        event.preventDefault();
        this.ea.publish(Channels.Menu, <IMenu>{action: MenuAction.Open});
    }

}