import {bindable, queueTask, customElement, IEventAggregator, ILogger, INode, IPlatform, resolve} from "aurelia";
import {Channels, ToasterAction, UiColor, UiToastIcon} from '../enums/event-aggregator';
import {IToaster} from '../interfaces/event-aggregator';


@customElement({
    name: 'bleet-toaster-trigger',
    template: null
})
export class BleetToasterTrigger {

    @bindable() public id: string = '';
    @bindable() public color: UiColor = UiColor.Info;
    @bindable() public icon: UiToastIcon = UiToastIcon.Info;
    @bindable() public title: string = '';
    @bindable() public content: string = '';
    @bindable() public duration: number = 0; // Duration in milliseconds
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<bleet-toaster-trigger>'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly p: IPlatform = resolve(IPlatform),
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
        this.logger.debug(`Triggering toast with`, this.p.document.readyState);
        if (this.p.document.readyState === 'loading') {
            this.p.document.addEventListener('DOMContentLoaded', () => {
                this.onAttach();
            }, { once: true });
        } else {
            this.onAttach();
        }
    }

    public detached()
    {
        this.logger.trace('detached');
    }

    private onAttach = () => {
        this.logger.trace('onAttach');
        queueTask(() => {
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
        });
    }
}