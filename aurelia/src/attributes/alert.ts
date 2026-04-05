import {customAttribute, ILogger, INode, IPlatform, resolve} from "aurelia";
import {ITransitionService} from '../services/transition-service';


@customAttribute('bleet-alert')
export class BleetAlertCustomAttribute {

    private closeButton?: HTMLButtonElement;
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-alert'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
    ) {
        this.logger.trace('constructor')
    }

    public attaching()
    {
        this.logger.trace('attaching');
        this.closeButton = this.element.querySelector('[data-alert=close]') as HTMLButtonElement;
    }

    public attached()
    {
        this.logger.trace('attached');
        this.closeButton?.addEventListener('click', this.onClose);
    }

    public detached()
    {
        this.logger.trace('detached');
        this.closeButton?.removeEventListener('click', this.onClose);
    }
    private onClose = (event: Event) => {
        this.logger.trace('onClose', event);
        event.preventDefault();
        this.transitionService.run(this.element, (element: HTMLElement) => {
            const currentHeight = element.scrollHeight;
            element.style.height = currentHeight + 'px';
            // Force reflow
            element.offsetHeight;
            element.style.height = '0px';
            element.classList.remove('opacity-100');
            element.classList.add('opacity-0');
        }, (element: HTMLElement) => {

            element.classList.add('hidden');
            this.platform.requestAnimationFrame(() => {
                element.style.height = '';
                element.remove();
            });
        });
    }
}