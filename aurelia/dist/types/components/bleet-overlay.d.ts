import { IEventAggregator, ILogger, IPlatform } from "aurelia";
import { ITransitionService } from '../services/transition-service';
export declare class BleetOverlay {
    private readonly logger;
    private readonly element;
    private readonly ea;
    private readonly platform;
    private readonly transitionService;
    private disposable?;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator, platform?: IPlatform, transitionService?: ITransitionService);
    attaching(): void;
    attached(): void;
    detached(): void;
    dispose(): void;
    private toggle;
    private open;
    private close;
    private onOverlayEvent;
    private onClickOverlay;
}
//# sourceMappingURL=bleet-overlay.d.ts.map