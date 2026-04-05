import {bindable, customElement, IEventAggregator, ILogger, INode, IPlatform, resolve} from "aurelia";
import {Channels, ToasterAction, UiColor, UiToastIcon} from '../enums/event-aggregator';
import {IToaster} from '../interfaces/event-aggregator';
import {ITransitionService} from '../services/transition-service';
import template from './bleet-toast.html';

@customElement({
    name: 'bleet-toast',
    template
})
export class BleetToast {

    @bindable() public id: string = '';
    @bindable() public color: UiColor = UiColor.Info;
    @bindable() public icon: UiToastIcon = UiToastIcon.Info;
    @bindable() public title: string = '';
    @bindable() public content: string = '';
    @bindable() public duration: number = 0; // Duration in milliseconds
    private added = false;
    private closeTimeout?: number;

    // Classes Tailwind complètes pour éviter le purge
    private readonly colorClasses: Record<string, { container: string; icon: string; title: string; content: string; button: string }> = {
        [UiColor.Primary]: {
            container: 'border-primary-300 bg-primary-50',
            icon: 'text-primary-700',
            title: 'text-primary-700',
            content: 'text-primary-700',
            button: 'bg-primary-50 text-primary-500 hover:bg-primary-100'
        },
        [UiColor.Secondary]: {
            container: 'border-secondary-300 bg-secondary-50',
            icon: 'text-secondary-700',
            title: 'text-secondary-700',
            content: 'text-secondary-700',
            button: 'bg-secondary-50 text-secondary-500 hover:bg-secondary-100'
        },
        [UiColor.Success]: {
            container: 'border-success-300 bg-success-50',
            icon: 'text-success-700',
            title: 'text-success-700',
            content: 'text-success-700',
            button: 'bg-success-50 text-success-500 hover:bg-success-100'
        },
        [UiColor.Danger]: {
            container: 'border-danger-300 bg-danger-50',
            icon: 'text-danger-700',
            title: 'text-danger-700',
            content: 'text-danger-700',
            button: 'bg-danger-50 text-danger-500 hover:bg-danger-100'
        },
        [UiColor.Warning]: {
            container: 'border-warning-300 bg-warning-50',
            icon: 'text-warning-700',
            title: 'text-warning-700',
            content: 'text-warning-700',
            button: 'bg-warning-50 text-warning-500 hover:bg-warning-100'
        },
        [UiColor.Info]: {
            container: 'border-info-300 bg-info-50',
            icon: 'text-info-700',
            title: 'text-info-700',
            content: 'text-info-700',
            button: 'bg-info-50 text-info-500 hover:bg-info-100'
        },
        [UiColor.Accent]: {
            container: 'border-accent-300 bg-accent-50',
            icon: 'text-accent-700',
            title: 'text-accent-700',
            content: 'text-accent-700',
            button: 'bg-accent-50 text-accent-500 hover:bg-accent-100'
        },
    };

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<bleet-toast>'),
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
        if (!this.added) {
            this.added = true;
            this.logger.debug(`Toast added with ID: ${this.id}`);
            this.transitionService.run(this.element, (element: HTMLElement) => {
                if (this.duration > 0) {
                    this.closeTimeout = this.platform.setTimeout(() => {
                        this.close();
                    }, this.duration);
                }
                element.classList.add('opacity-100', 'translate-x-0');
                element.classList.remove('opacity-0', 'translate-x-full');
            });
        }
    }

    public detached()
    {
        this.logger.trace('detached');
    }

    private onClickRemove(evt: Event) {
        evt.preventDefault();
        this.logger.trace('onClickRemove');
        this.close();
    }

    private close() {
        if (this.closeTimeout) {
            this.platform.clearTimeout(this.closeTimeout);
        }
        this.transitionService.run(this.element, (element: HTMLElement) => {
            element.classList.add('opacity-0', 'translate-x-full');
            element.classList.remove('opacity-100', 'translate-x-0');
        }, (element: HTMLElement) => {
            this.ea.publish(Channels.Toaster, <IToaster>{
                action: ToasterAction.Remove,
                toast: { id: this.id }
            });
        });
    }
}