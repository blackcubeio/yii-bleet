import {customAttribute, ILogger, INode, IPlatform, resolve} from "aurelia";


@customAttribute('bleet-tabs')
export class BleetTabsCustomAttribute {

    private activeClasses: string = '';
    private inactiveClasses: string = '';
    private select?: HTMLSelectElement;
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-tabs'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly p: IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor')
    }

    public attaching()
    {
        this.logger.trace('attaching');
        const activeButton = this.element.querySelector('[data-tabs^="tab-"][aria-selected="true"]');
        const inactiveButton = this.element.querySelector('[data-tabs^="tab-"][aria-selected="false"]');

        this.activeClasses = activeButton?.className || '';
        this.inactiveClasses = inactiveButton?.className || '';

        this.select = this.element.querySelector('select') as HTMLSelectElement;
    }

    public attached()
    {
        this.logger.trace('attached');
        this.element.querySelectorAll('[data-tabs^="tab-"]').forEach((button: Element) => {
            button.addEventListener('click', this.onClickTab);
        });

        // Écouter le select mobile
        if (this.select) {
            this.select.addEventListener('change', this.onChangeSelect);
        }
    }

    public detached()
    {
        this.logger.trace('detached');
        this.element.querySelectorAll('[data-tabs^="tab-"]').forEach((button: Element) => {
            button.removeEventListener('click', this.onClickTab);
        });

        if (this.select) {
            this.select.removeEventListener('change', this.onChangeSelect);
        }
    }

    private onClickTab = (event: Event) => {
        this.logger.trace('onClickTab', event);
        event.preventDefault();
        const tabIndex = (event.currentTarget as HTMLElement).getAttribute('data-tabs')?.replace('tab-', '') || '0';

        // Mettre à jour les tabs
        this.element.querySelectorAll('[data-tabs^="tab-"]').forEach((button: Element) => {
            const buttonTabIndex = button.getAttribute('data-tabs')?.replace('tab-', '') || '0';
            if (buttonTabIndex === tabIndex) {
                button.setAttribute('aria-selected', 'true');
                button.className = this.activeClasses;
            } else {
                button.setAttribute('aria-selected', 'false');
                button.className = this.inactiveClasses;
            }
        });

        // Mettre à jour les panels
        this.element.querySelectorAll('[data-tabs^="panel-"]').forEach((panel: Element) => {
            const panelIndex = panel.getAttribute('data-tabs')?.replace('panel-', '') || '0';
            if (panelIndex === tabIndex) {
                panel.classList.remove('hidden');
                panel.setAttribute('aria-hidden', 'false');
            } else {
                panel.classList.add('hidden');
                panel.setAttribute('aria-hidden', 'true');
            }
        });

        // Synchroniser le select
        if (this.select) {
            this.select.value = tabIndex;
        }
    }

    private onChangeSelect = (event: Event) => {
        this.logger.trace('onChangeSelect', event);
        const tabIndex = this.select?.value;
        const button = this.element.querySelector(`[data-tabs="tab-${tabIndex}"]`) as HTMLElement;

        if (button) {
            button.click();
        }
    }
}