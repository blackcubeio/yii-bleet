import { IEventAggregator, ILogger } from "aurelia";
import { UiColor, UiToastIcon } from '../enums/event-aggregator';
export declare class BleetToasterTriggerCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly ea;
    id: string;
    color: UiColor;
    icon: UiToastIcon;
    title: string;
    content: string;
    duration: number;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onClick;
}
//# sourceMappingURL=toaster-trigger.d.ts.map