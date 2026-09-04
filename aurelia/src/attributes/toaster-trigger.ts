import {bindable, customAttribute, IEventAggregator, ILogger, INode, resolve} from "aurelia";
import {Channels, ToasterAction, UiColor, UiToastIcon} from '../enums/event-aggregator';
import {IToaster} from '../interfaces/event-aggregator';

@customAttribute({ name: 'bleet-toaster-trigger', defaultProperty: 'id' })
export class BleetToasterTriggerCustomAttribute {

    @bindable id: string = '';
    @bindable() public color: UiColor = UiColor.Info;
    @bindable() public icon: UiToastIcon = UiToastIcon.Info;
    @bindable() public title: string = '';
    @bindable() public content: string = '';
    @bindable() public duration: number = 0; // Duration in milliseconds
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-toaster-trigger'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
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
        this.element.addEventListener('click', this.onClick);
    }

    public detached()
    {
        this.logger.trace('detached');
        this.element.removeEventListener('click', this.onClick);
    }

    private onClick = (event: Event) => {
        this.logger.trace('onClick', event);
        event.preventDefault();
        this.ea.publish(Channels.Toaster, <IToaster>{
            action: ToasterAction.Add, toast:
                {
                    id: this.id,
                    duration: this.duration,
                    color: this.color,
                    icon: this.icon,
                    title: this.title,
                    content: this.content
                }
        });
    }
}