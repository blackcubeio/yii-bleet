import { ILogger } from "aurelia";
import { ITrapFocusService } from '../services/trap-focus-service';
export declare class BleetDropdownCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly trapFocusService;
    private select?;
    private button?;
    private buttonText?;
    private optionTemplate?;
    private tagTemplate?;
    private tagsContainer?;
    private placeholder?;
    private itemsPlace?;
    private itemsContainer?;
    private searchInput?;
    private emptyMessage?;
    private isMultiple;
    private withTags;
    constructor(logger?: ILogger, element?: HTMLElement, trapFocusService?: ITrapFocusService);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onClickToggleMenu;
    private onClickRemoveTag;
    private onStopTrapFocus;
    private toggleMenu;
    private onClickToggleItem;
    private onSearchInput;
    private filterItems;
    private synchSelect;
    private updateDisplay;
    private updateButtonText;
    private updateTags;
    private swapItemClasses;
    private preparePanel;
}
//# sourceMappingURL=dropdown.d.ts.map