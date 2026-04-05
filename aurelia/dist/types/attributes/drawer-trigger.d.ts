import { IEventAggregator, ILogger } from "aurelia";
export declare class BleetDrawerTriggerCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly ea;
    id: string;
    url: string;
    color: string;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator);
    attaching(): void;
    attached(): void;
    detached(): void;
    dispose(): void;
    private onClick;
}
//# sourceMappingURL=drawer-trigger.d.ts.map