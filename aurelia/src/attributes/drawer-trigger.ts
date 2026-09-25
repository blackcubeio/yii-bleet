import {bindable, customAttribute, IEventAggregator, ILogger, INode, resolve} from "aurelia";
import {Channels, DrawerAction} from '../enums/event-aggregator';
import {IDrawer} from '../interfaces/event-aggregator';

@customAttribute({ name: 'bleet-drawer-trigger', defaultProperty: 'id' })
export class BleetDrawerTriggerCustomAttribute {

    @bindable id: string = '';
    @bindable() url: string = '';
    @bindable() color: string = 'primary';

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-drawer-trigger'),
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
    public dispose()
    {
        this.logger.trace('dispose');
    }

    private onClick = (event: Event): void => {
        this.logger.trace('onClick', event);
        event.preventDefault();

        this.ea.publish(Channels.Drawer, <IDrawer>{
            action: DrawerAction.Toggle,
            id: this.id,
            url: this.url,
            color: this.color,
        });
    };

}
