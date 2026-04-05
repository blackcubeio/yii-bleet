import { IEventAggregator, ILogger, IPlatform } from "aurelia";
import { ITransitionService } from '../services/transition-service';
import { ITrapFocusService } from '../services/trap-focus-service';
export declare class BleetProfileCustomAttribute {
    private readonly logger;
    private readonly ea;
    private readonly element;
    private readonly transitionService;
    private readonly platform;
    private readonly trapFocusService;
    id: string;
    private toggleButton?;
    private panel?;
    private isOpen;
    constructor(logger?: ILogger, ea?: IEventAggregator, element?: HTMLElement, transitionService?: ITransitionService, platform?: IPlatform, trapFocusService?: ITrapFocusService);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onClickToggle;
    private onStopTrapFocus;
    private open;
    private close;
}
//# sourceMappingURL=profile.d.ts.map