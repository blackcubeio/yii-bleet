import {bindable, customAttribute, IEventAggregator, ILogger, INode, IPlatform, resolve} from "aurelia";
import {Channels, ProfileAction, ProfileStatus} from '../enums/event-aggregator';
import {IProfile, IProfileStatus} from '../interfaces/event-aggregator';
import {ITransitionService} from '../services/transition-service';
import {ITrapFocusService} from '../services/trap-focus-service';

@customAttribute({ name: 'bleet-profile', defaultProperty: 'id' })
export class BleetProfileCustomAttribute
{

    @bindable id: string = '';
    private toggleButton?: HTMLButtonElement;
    private panel?: HTMLDivElement;
    private isOpen: boolean = false;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-profile'),
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly trapFocusService: ITrapFocusService = resolve(ITrapFocusService),
    ) {
        this.logger.trace('constructor')
    }

    public attaching()
    {
        this.logger.trace('attaching');
        this.toggleButton = this.element.querySelector('[data-profile=toggle]') as HTMLButtonElement;
        this.panel = this.element.querySelector('[data-profile=panel]') as HTMLDivElement;
    }

    public attached()
    {
        this.logger.trace('attached');
        this.toggleButton?.addEventListener('click', this.onClickToggle);
    }

    public detached()
    {
        this.logger.trace('detached');
        this.toggleButton?.removeEventListener('click', this.onClickToggle);
        if (this.isOpen) {
            this.trapFocusService.stop();
        }
    }

    private onClickToggle = (event: MouseEvent) => {
        this.logger.trace('onClickToggle', event);
        event.preventDefault();
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    private onStopTrapFocus = () => {
        this.logger.trace('onStopTrapFocus');
        this.isOpen = false;
        this.toggleButton?.setAttribute('aria-expanded', 'false');
        this.transitionService.run(this.panel!, (element) => {
            this.ea.publish(Channels.Profile, <IProfile>{action: ProfileAction.Close, id: this.id});
            this.ea.publish(Channels.ProfileStatus, <IProfileStatus>{status: ProfileStatus.Closing, id: this.id});
            this.platform.requestAnimationFrame(() => {
                element.classList.remove('opacity-100', 'scale-100');
                element.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
            });
        }, (element) => {
            element.classList.add('hidden');
            this.ea.publish(Channels.ProfileStatus, <IProfileStatus>{status: ProfileStatus.Closed, id: this.id});
        });
        return Promise.resolve();
    }

    private open() {
        this.logger.trace('open');
        this.isOpen = true;
        this.toggleButton?.setAttribute('aria-expanded', 'true');

        // Find first focusable item in panel
        const firstItem = this.panel?.querySelector('a, button') as HTMLElement;

        this.trapFocusService.start(
            this.toggleButton as HTMLButtonElement,
            this.panel as HTMLElement,
            this.element,
            undefined,
            this.onStopTrapFocus,
            firstItem
        ).then(() => {
            this.transitionService.run(this.panel!, (element) => {
                this.ea.publish(Channels.Profile, <IProfile>{action: ProfileAction.Open, id: this.id});
                this.ea.publish(Channels.ProfileStatus, <IProfileStatus>{status: ProfileStatus.Opening, id: this.id});
                element.classList.remove('hidden');
                this.platform.requestAnimationFrame(() => {
                    element.classList.add('opacity-100', 'scale-100');
                    element.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                });
            }, () => {
                this.ea.publish(Channels.ProfileStatus, <IProfileStatus>{status: ProfileStatus.Opened, id: this.id});
            });
        });
    }

    private close() {
        this.logger.trace('close');
        this.trapFocusService.stop();
    }

}