import { IEventAggregator, ILogger, IPlatform } from "aurelia";
import { UiColor, UiToastIcon } from '../enums/event-aggregator';
export declare class BleetToasterTrigger {
    private readonly logger;
    private readonly element;
    private readonly ea;
    private readonly p;
    id: string;
    color: UiColor;
    icon: UiToastIcon;
    title: string;
    content: string;
    duration: number;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator, p?: IPlatform);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onAttach;
}
//# sourceMappingURL=bleet-toaster-trigger.d.ts.map