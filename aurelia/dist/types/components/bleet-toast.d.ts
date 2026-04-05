import { IEventAggregator, ILogger, IPlatform } from "aurelia";
import { UiColor, UiToastIcon } from '../enums/event-aggregator';
import { ITransitionService } from '../services/transition-service';
export declare class BleetToast {
    private readonly logger;
    private readonly element;
    private readonly ea;
    private readonly platform;
    private readonly transitionService;
    id: string;
    color: UiColor;
    icon: UiToastIcon;
    title: string;
    content: string;
    duration: number;
    private added;
    private closeTimeout?;
    private readonly colorClasses;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator, platform?: IPlatform, transitionService?: ITransitionService);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onClickRemove;
    private close;
}
//# sourceMappingURL=bleet-toast.d.ts.map