import { ILogger, IPlatform } from "aurelia";
import { ITransitionService } from '../services/transition-service';
export declare class BleetAlertCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly platform;
    private readonly transitionService;
    private closeButton?;
    constructor(logger?: ILogger, element?: HTMLElement, platform?: IPlatform, transitionService?: ITransitionService);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onClose;
}
//# sourceMappingURL=alert.d.ts.map