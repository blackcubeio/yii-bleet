import { ILogger } from "aurelia";
import { ITrapFocusService } from '../services/trap-focus-service';
export declare class BleetSelectCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly trapFocusService;
    private select?;
    private button?;
    private buttonText?;
    private optionTemplate?;
    private groupTemplate?;
    private itemsPlace?;
    private selectObserver?;
    constructor(logger?: ILogger, element?: HTMLElement, trapFocusService?: ITrapFocusService);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onClickToggleMenu;
    private onStopTrapFocus;
    private toggleMenu;
    private onClickToggleItem;
    private synchSelect;
    private swapItemClasses;
    private preparePanel;
    private appendOption;
    private appendGroupHeader;
}
//# sourceMappingURL=select.d.ts.map