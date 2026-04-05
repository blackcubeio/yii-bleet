import {customAttribute, ILogger, INode, resolve} from "aurelia";
import {ITrapFocusService} from '../services/trap-focus-service';

// @ts-ignore
@customAttribute('bleet-select')
export class BleetSelectCustomAttribute {

    private select?: HTMLSelectElement;
    private button?: HTMLButtonElement;
    private buttonText?: HTMLElement;
    private optionTemplate?: HTMLTemplateElement;
    private groupTemplate?: HTMLTemplateElement;
    private itemsPlace?: HTMLElement;
    private selectObserver?: MutationObserver;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-select'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly trapFocusService: ITrapFocusService = resolve(ITrapFocusService),
    ) {
        this.logger.trace('constructor')
    }

    public attaching()
    {
        this.logger.trace('attaching');
        this.select = this.element.querySelector('select') as HTMLSelectElement;
        this.button = this.element.querySelector('button') as HTMLButtonElement;
        this.buttonText = this.button.querySelector('[data-select=value]') as HTMLElement;
        this.optionTemplate = this.element.querySelector('[data-select=item-template]') as HTMLTemplateElement;
        this.groupTemplate = this.element.querySelector('[data-select=group-template]') as HTMLTemplateElement;
        this.itemsPlace = this.element.querySelector('[data-select=items]') as HTMLElement;
    }

    public attached()
    {
        this.logger.trace('attached');
        if (!this.itemsPlace) {
            throw new Error('Items place element not found');
        }
        if (!this.itemsPlace.id) {
            this.itemsPlace.id = `data-select-items-${Math.random().toString(36).substring(2, 15)}`;
        }
        if (!this.select?.options) {
            throw new Error('Select options not found');
        }
        if (!this.optionTemplate) {
            throw new Error('Option template not found');
        }
        if (!this.button) {
            throw new Error('Button element not found');
        }
        if (!this.buttonText) {
            throw new Error('Button text element not found');
        }
        if (this.select.options.length > 0) {
            this.preparePanel();
        }
        this.selectObserver = new MutationObserver(() => {
            if (this.select && this.select.options.length > 0) {
                this.preparePanel();
            }
        });
        this.selectObserver.observe(this.select, { childList: true });

        // ARIA setup
        this.button.ariaHasPopup = 'listbox';
        this.button.setAttribute('aria-controls', this.itemsPlace.id);
        this.itemsPlace.role = 'listbox';

        // Event listeners
        this.button?.addEventListener('click', this.onClickToggleMenu);
        this.itemsPlace?.addEventListener('click', this.onClickToggleItem);
    }

    public detached()
    {
        this.logger.trace('detached');
        this.selectObserver?.disconnect();
        this.selectObserver = undefined;
        this.button?.removeEventListener('click', this.onClickToggleMenu);
        this.itemsPlace?.removeEventListener('click', this.onClickToggleItem);
    }

    private onClickToggleMenu = (event: MouseEvent) => {
        this.logger.trace('onClick', event);
        event.preventDefault();
        if (this.select?.disabled) {
            return;
        }
        return this.toggleMenu();
    }

    private onStopTrapFocus = () => {
        this.logger.trace('onStopTrapFocus');
        this.itemsPlace?.classList.add('hidden');
        return Promise.resolve();
    }

    private toggleMenu = () => {
        const isClosed = this.itemsPlace?.classList.contains('hidden');
        return new Promise((resolve) => {
            if (isClosed) {
                // Opening menu
                (this.button as HTMLButtonElement).ariaExpanded = 'true';

                // Find selected option to focus initially
                const selectedOption = this.itemsPlace?.querySelector('[aria-selected="true"]') as HTMLElement;

                return this.trapFocusService.start(
                    this.button as HTMLButtonElement,
                    this.itemsPlace as HTMLElement,
                    this.element,
                    undefined,
                    this.onStopTrapFocus,
                    selectedOption // Pass selected option as initial focus
                )
                    .then(() => {
                        this.logger.trace('toggleMenu opened');
                        this.itemsPlace?.classList.remove('hidden');
                        resolve(void 0);
                    });
            } else {
                // Closing menu
                (this.button as HTMLButtonElement).ariaExpanded = 'false';
                return this.trapFocusService.stop()
                    .then(() => {
                        this.logger.trace('toggleMenu closed');
                        this.itemsPlace?.classList.add('hidden');
                        resolve(void 0);
                    });
            }
        });
    }

    private onClickToggleItem = (event: MouseEvent) => {
        this.logger.trace('onClickItem', event);
        event.preventDefault();
        const element = (event.target as HTMLElement).closest('[data-value]') as HTMLElement;

        // Update select options
        Array.from((this.select as HTMLSelectElement).options).forEach((option) => {
            option.selected = option.value == element.dataset.value;
        });

        // Dispatch change event sur le select natif pour active-form
        this.select?.dispatchEvent(new Event('change', { bubbles: true }));

        this.synchSelect();
    }

    private synchSelect() {
        this.swapItemClasses();

        // Close menu
        return this.toggleMenu();
    }

    private swapItemClasses() {
        Array.from((this.select as HTMLSelectElement).options).forEach((option) => {
            const item = this.itemsPlace?.querySelector(`[data-value="${option.value}"]`) as HTMLElement;
            if (!item) return;

            const checkmark = item.querySelector('[data-select=item-check]') as HTMLElement;

            // Récupérer les classes depuis les data-attributes de l'élément
            const itemInactiveClasses = item.dataset.classInactive?.split(' ') || [];
            const itemActiveClasses = item.dataset.classActive?.split(' ') || [];
            const checkInactiveClasses = checkmark?.dataset.classInactive?.split(' ') || [];
            const checkActiveClasses = checkmark?.dataset.classActive?.split(' ') || [];

            if (option.selected) {
                // Swap vers active
                item.classList.remove(...itemInactiveClasses);
                item.classList.add(...itemActiveClasses);
                checkmark?.classList.remove(...checkInactiveClasses);
                checkmark?.classList.add(...checkActiveClasses);

                // Update ARIA
                item.setAttribute('aria-selected', 'true');
                this.button?.setAttribute('aria-activedescendant', item.id);

                // Update button text
                (this.buttonText as HTMLElement).innerHTML = option.innerHTML;
            } else {
                // Swap vers inactive
                item.classList.remove(...itemActiveClasses);
                item.classList.add(...itemInactiveClasses);
                checkmark?.classList.remove(...checkActiveClasses);
                checkmark?.classList.add(...checkInactiveClasses);

                // Update ARIA
                item.setAttribute('aria-selected', 'false');
            }
        });
    }
    private preparePanel() {
        this.logger.trace('preparePanel');
        if (!this.select) {
            throw new Error('Select element not found');
        }
        // Vider le panel (sauf les templates)
        this.itemsPlace?.querySelectorAll('button, [data-select=group]').forEach((child) => child.remove());

        Array.from(this.select.children).forEach((child) => {
            if (child.tagName === 'OPTGROUP') {
                const groupedOptions = Array.from(child.children).filter((c) => c.tagName === 'OPTION');
                if (groupedOptions.length > 0) {
                    this.appendGroupHeader((child as HTMLOptGroupElement).label);
                    groupedOptions.forEach((o) => this.appendOption(o as HTMLOptionElement));
                }
            } else if (child.tagName === 'OPTION') {
                this.appendOption(child as HTMLOptionElement);
            }
        });

        // Appliquer les classes active/inactive
        this.swapItemClasses();
    }

    private appendOption(option: HTMLOptionElement) {
        // @ts-ignore
        const item = this.optionTemplate.content.cloneNode(true) as DocumentFragment;
        const button = item.querySelector('button') as HTMLButtonElement;

        // ARIA attributes
        button.role = 'option';
        button.id = `bleet-option-${option.value}-${Math.random().toString(36).substring(2, 9)}`;

        // Content
        const itemText = item.querySelector('[data-select=item-text]') as HTMLElement;
        const itemValue = item.querySelector('[data-value]') as HTMLElement;
        itemValue.dataset.value = option.value;
        itemText.innerHTML = option.innerHTML;

        this.itemsPlace?.append(item);
    }

    private appendGroupHeader(label: string) {
        if (!this.groupTemplate) return;
        const fragment = this.groupTemplate.content.cloneNode(true) as DocumentFragment;
        const groupLabel = fragment.querySelector('[data-select=group-label]') as HTMLElement | null;
        if (groupLabel) {
            groupLabel.textContent = label;
        }
        this.itemsPlace?.append(fragment);
    }
}