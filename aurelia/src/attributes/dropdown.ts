import {customAttribute, ILogger, INode, resolve} from "aurelia";
import {ITrapFocusService} from '../services/trap-focus-service';

// @ts-ignore
@customAttribute('bleet-dropdown')
export class BleetDropdownCustomAttribute {

    private select?: HTMLSelectElement;
    private button?: HTMLButtonElement;
    private buttonText?: HTMLElement;
    private optionTemplate?: HTMLTemplateElement;
    private groupTemplate?: HTMLTemplateElement;
    private tagTemplate?: HTMLTemplateElement;
    private tagsContainer?: HTMLElement;
    private placeholder?: HTMLElement;
    private itemsPlace?: HTMLElement;
    private itemsContainer?: HTMLElement;
    private searchInput?: HTMLInputElement;
    private emptyMessage?: HTMLElement;
    private isMultiple: boolean = false;
    private withTags: boolean = false;
    private selectObserver?: MutationObserver;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-dropdown'),
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
        this.buttonText = this.button.querySelector('[data-dropdown=value]') as HTMLElement;
        this.tagsContainer = this.button.querySelector('[data-dropdown=tags]') as HTMLElement;
        this.placeholder = this.button.querySelector('[data-dropdown=placeholder]') as HTMLElement;
        this.optionTemplate = this.element.querySelector('[data-dropdown=item-template]') as HTMLTemplateElement;
        this.groupTemplate = this.element.querySelector('[data-dropdown=group-template]') as HTMLTemplateElement;
        this.tagTemplate = this.element.querySelector('[data-dropdown=tag-template]') as HTMLTemplateElement;
        this.itemsPlace = this.element.querySelector('[data-dropdown=items]') as HTMLElement;
        this.itemsContainer = this.element.querySelector('[data-dropdown=items-container]') as HTMLElement;
        this.searchInput = this.element.querySelector('[data-dropdown=search]') as HTMLInputElement;
        this.emptyMessage = this.element.querySelector('[data-dropdown=empty]') as HTMLElement;
        this.isMultiple = this.select?.multiple || false;
        this.withTags = this.tagTemplate !== null;
    }

    public attached()
    {
        this.logger.trace('attached');
        if (!this.itemsPlace) {
            throw new Error('Items place element not found');
        }
        if (!this.itemsPlace.id) {
            this.itemsPlace.id = `data-dropdown-items-${Math.random().toString(36).substring(2, 15)}`;
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
        if (!this.buttonText && !this.tagsContainer) {
            throw new Error('Button text or tags container element not found');
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
        this.searchInput?.addEventListener('input', this.onSearchInput);

        // Tag remove event listener (delegation)
        if (this.withTags) {
            this.tagsContainer?.addEventListener('click', this.onClickRemoveTag);
        }
    }

    public detached()
    {
        this.logger.trace('detached');
        this.selectObserver?.disconnect();
        this.selectObserver = undefined;
        this.button?.removeEventListener('click', this.onClickToggleMenu);
        this.itemsPlace?.removeEventListener('click', this.onClickToggleItem);
        this.searchInput?.removeEventListener('input', this.onSearchInput);
        if (this.withTags) {
            this.tagsContainer?.removeEventListener('click', this.onClickRemoveTag);
        }
    }

    private onClickToggleMenu = (event: MouseEvent) => {
        this.logger.trace('onClick', event);
        // Ne pas ouvrir si on clique sur un bouton de suppression de tag
        if ((event.target as HTMLElement).closest('[data-dropdown=tag-remove]')) {
            return;
        }
        event.preventDefault();
        return this.toggleMenu();
    }

    private onClickRemoveTag = (event: MouseEvent) => {
        const removeButton = (event.target as HTMLElement).closest('[data-dropdown=tag-remove]');
        if (!removeButton) return;

        event.preventDefault();
        event.stopPropagation();

        const tagElement = removeButton.closest('[data-tag-value]') as HTMLElement;
        if (!tagElement) return;

        const value = tagElement.dataset.tagValue;

        // Désélectionner l'option
        Array.from((this.select as HTMLSelectElement).options).forEach((option) => {
            if (option.value == value) {
                option.selected = false;
            }
        });

        // Dispatch change event sur le select natif pour active-form
        this.select?.dispatchEvent(new Event('change', { bubbles: true }));

        this.swapItemClasses();
        this.updateDisplay();
    }

    private onStopTrapFocus = () => {
        this.logger.trace('onStopTrapFocus');
        this.itemsPlace?.classList.add('hidden');
        // Reset search on close
        if (this.searchInput) {
            this.searchInput.value = '';
            this.filterItems('');
        }
        return Promise.resolve();
    }

    private toggleMenu = () => {
        const isClosed = this.itemsPlace?.classList.contains('hidden');
        return new Promise((resolve) => {
            if (isClosed) {
                // Opening menu
                (this.button as HTMLButtonElement).ariaExpanded = 'true';

                // Find selected option to focus initially, or search input if searchable
                const initialFocus = this.searchInput || this.itemsPlace?.querySelector('[aria-selected="true"]') as HTMLElement;

                return this.trapFocusService.start(
                    this.button as HTMLButtonElement,
                    this.itemsPlace as HTMLElement,
                    this.element,
                    undefined,
                    this.onStopTrapFocus,
                    initialFocus
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
        if (!element) return;

        const clickedValue = element.dataset.value;

        if (this.isMultiple) {
            // Toggle la sélection de l'option cliquée
            Array.from((this.select as HTMLSelectElement).options).forEach((option) => {
                if (option.value == clickedValue) {
                    option.selected = !option.selected;
                }
            });

            // Dispatch change event sur le select natif pour active-form
            this.select?.dispatchEvent(new Event('change', { bubbles: true }));

            // Ne pas fermer le menu en mode multiple
            this.swapItemClasses();
            this.updateDisplay();
        } else {
            // Mode simple : une seule sélection
            Array.from((this.select as HTMLSelectElement).options).forEach((option) => {
                option.selected = option.value == clickedValue;
            });

            // Dispatch change event sur le select natif pour active-form
            this.select?.dispatchEvent(new Event('change', { bubbles: true }));

            this.synchSelect();
        }
    }

    private onSearchInput = (event: Event) => {
        const query = (event.target as HTMLInputElement).value;
        this.filterItems(query);
    }

    private filterItems(query: string) {
        const normalizedQuery = query.toLowerCase().trim();
        let visibleCount = 0;

        this.itemsContainer?.querySelectorAll('[data-value]').forEach((item) => {
            const text = item.querySelector('[data-dropdown=item-text]')?.textContent?.toLowerCase() || '';
            const isVisible = normalizedQuery === '' || text.includes(normalizedQuery);

            if (isVisible) {
                (item as HTMLElement).classList.remove('hidden');
                visibleCount++;
            } else {
                (item as HTMLElement).classList.add('hidden');
            }
        });

        // Show/hide empty message
        if (this.emptyMessage) {
            if (visibleCount === 0 && normalizedQuery !== '') {
                this.emptyMessage.classList.remove('hidden');
            } else {
                this.emptyMessage.classList.add('hidden');
            }
        }

        this.updateGroupVisibility();
    }

    private updateGroupVisibility() {
        if (!this.itemsContainer) return;
        const groups = this.itemsContainer.querySelectorAll('[data-dropdown=group]');
        groups.forEach((group) => {
            let hasVisible = false;
            let sibling = group.nextElementSibling;
            while (sibling && !sibling.matches('[data-dropdown=group]')) {
                if (!sibling.classList.contains('hidden')) {
                    hasVisible = true;
                    break;
                }
                sibling = sibling.nextElementSibling;
            }
            if (hasVisible) {
                (group as HTMLElement).classList.remove('hidden');
            } else {
                (group as HTMLElement).classList.add('hidden');
            }
        });
    }

    private synchSelect() {
        this.swapItemClasses();

        // Close menu
        return this.toggleMenu();
    }

    private updateDisplay() {
        if (this.withTags) {
            this.updateTags();
        } else {
            this.updateButtonText();
        }
    }

    private updateButtonText() {
        const selectedOptions = Array.from((this.select as HTMLSelectElement).options).filter(opt => opt.selected);

        if (selectedOptions.length === 0) {
            // Afficher le placeholder s'il existe
            const placeholder = Array.from((this.select as HTMLSelectElement).options).find(opt => opt.value === '');
            (this.buttonText as HTMLElement).innerHTML = placeholder?.innerHTML || '';
        } else if (selectedOptions.length === 1) {
            (this.buttonText as HTMLElement).innerHTML = selectedOptions[0].innerHTML;
        } else {
            (this.buttonText as HTMLElement).textContent = `${selectedOptions.length} sélectionnés`;
        }
    }

    private updateTags() {
        if (!this.tagsContainer || !this.tagTemplate) return;

        const selectedOptions = Array.from((this.select as HTMLSelectElement).options).filter(opt => opt.selected && opt.value !== '');

        // Supprimer les tags existants (sauf le placeholder)
        this.tagsContainer.querySelectorAll('[data-tag-value]').forEach(tag => tag.remove());

        // Afficher/masquer le placeholder
        if (this.placeholder) {
            if (selectedOptions.length === 0) {
                this.placeholder.classList.remove('hidden');
            } else {
                this.placeholder.classList.add('hidden');
            }
        }

        // Créer les tags
        selectedOptions.forEach((option) => {
            const tagFragment = this.tagTemplate!.content.cloneNode(true) as DocumentFragment;
            const tagElement = tagFragment.querySelector('[data-tag-value]') as HTMLElement;
            const tagText = tagFragment.querySelector('[data-dropdown=tag-text]') as HTMLElement;

            tagElement.dataset.tagValue = option.value;
            tagText.textContent = option.textContent;

            this.tagsContainer?.appendChild(tagFragment);
        });
    }

    private swapItemClasses() {
        Array.from((this.select as HTMLSelectElement).options).forEach((option) => {
            const item = this.itemsContainer?.querySelector(`[data-value="${option.value}"]`) as HTMLElement;
            if (!item) return;

            const checkmark = item.querySelector('[data-dropdown=item-check]') as HTMLElement;

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

        // Update display (text or tags)
        this.updateDisplay();
    }

    private preparePanel() {
        this.logger.trace('preparePanel');
        if (!this.select) {
            throw new Error('Select element not found');
        }
        // Vider le container
        if (this.itemsContainer) {
            this.itemsContainer.innerHTML = '';
        }

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
        const itemText = item.querySelector('[data-dropdown=item-text]') as HTMLElement;
        const itemValue = item.querySelector('[data-value]') as HTMLElement;
        itemValue.dataset.value = option.value;
        itemText.innerHTML = option.innerHTML;

        this.itemsContainer?.append(item);
    }

    private appendGroupHeader(label: string) {
        if (!this.groupTemplate) return;
        const fragment = this.groupTemplate.content.cloneNode(true) as DocumentFragment;
        const groupLabel = fragment.querySelector('[data-dropdown=group-label]') as HTMLElement | null;
        if (groupLabel) {
            groupLabel.textContent = label;
        }
        this.itemsContainer?.append(fragment);
    }
}
