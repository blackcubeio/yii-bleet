import { IEventAggregator, ILogger } from 'aurelia';
export declare class BleetPopoverTriggerCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly ea;
    id: string;
    absolute: boolean;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator);
    attached(): void;
    detaching(): void;
    dispose(): void;
    private onMouseEnter;
    private onMouseLeave;
}
//# sourceMappingURL=popover-trigger.d.ts.map