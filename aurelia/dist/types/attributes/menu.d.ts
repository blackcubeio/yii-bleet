import { IEventAggregator, ILogger, IPlatform } from "aurelia";
import { ITransitionService } from '../services/transition-service';
import { IStorageService } from '../services/storage-service';
export declare class BleetMenuCustomAttribute {
    private readonly logger;
    private readonly ea;
    private readonly element;
    private readonly platform;
    private readonly transitionService;
    private readonly storageService;
    private disposable?;
    private disposableOverlay?;
    private closeButton?;
    private toggleButtons?;
    private sublists;
    private isOpen;
    constructor(logger?: ILogger, ea?: IEventAggregator, element?: HTMLElement, platform?: IPlatform, transitionService?: ITransitionService, storageService?: IStorageService);
    attaching(): void;
    attached(): void;
    detached(): void;
    dispose(): void;
    private open;
    private close;
    private onClickClose;
    private onClickToggleButtons;
    private initMenuButtons;
    private toggleButton;
    private closeOtherButtons;
    private onMenuEvent;
    private onOverlayStatus;
}
//# sourceMappingURL=menu.d.ts.map