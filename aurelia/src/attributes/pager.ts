import {customAttribute, ILogger, INode, IPlatform, resolve} from "aurelia";


@customAttribute('bleet-pager')
export class BleetPagerCustomAttribute {

    private select?: HTMLSelectElement;
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-pager'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly p: IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor')
    }

    public attaching()
    {
        this.logger.trace('attaching');
        this.select = this.element.querySelector('[data-pager="select"]') as HTMLSelectElement;
    }

    public attached()
    {
        this.logger.trace('attached');
        this.select?.addEventListener('change', this.onChangeSelect);
    }

    public detached()
    {
        this.logger.trace('detached');
        this.select?.removeEventListener('change', this.onChangeSelect);
    }

    private onChangeSelect = (event: Event) => {
        this.logger.trace('onChangeSelect', event);
        const pageNumber = this.select?.value;
        const link = this.element.querySelector(`[data-pager="page-${pageNumber}"]`) as HTMLElement;

        if (link) {
            link.click();
        }
    }
}