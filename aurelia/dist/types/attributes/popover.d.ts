import { IEventAggregator, ILogger } from 'aurelia';
export declare class BleetPopoverCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly ea;
    id: string;
    private isOpen;
    private subscription;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator);
    attaching(): void;
    attached(): void;
    detaching(): void;
    dispose(): void;
    private onPopover;
    private open;
    private close;
    private positionAt;
}
//# sourceMappingURL=popover.d.ts.map