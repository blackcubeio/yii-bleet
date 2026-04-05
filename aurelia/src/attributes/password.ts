import {customAttribute, ILogger, INode, resolve} from "aurelia";

@customAttribute('bleet-password')
export class BleetPasswordCustomAttribute
{
    private button?: HTMLElement;
    private iconHidden?: HTMLElement;
    private iconVisible?: HTMLElement;
    private input?: HTMLInputElement;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-password'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
    ) {
    }

    public attaching(): void
    {
        this.button = this.element.querySelector('[data-password=toggle]') ?? undefined;
        this.iconHidden = this.button?.querySelector('[data-password=icon-hidden]') ?? undefined;
        this.iconVisible = this.button?.querySelector('[data-password=icon-visible]') ?? undefined;
        this.input = this.element.querySelector('input') ?? undefined;
    }

    public attached(): void
    {
        this.button?.addEventListener('click', this.onToggle);
    }

    public detaching(): void
    {
        this.button?.removeEventListener('click', this.onToggle);
    }

    private onToggle = (event: MouseEvent): void => {
        event.preventDefault();

        const isPassword = this.input?.type === 'password';

        if (this.input) {
            this.input.type = isPassword ? 'text' : 'password';
        }

        this.iconHidden?.classList.toggle('hidden', isPassword);
        this.iconVisible?.classList.toggle('hidden', !isPassword);
    }
}