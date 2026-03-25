import { IEventAggregator, ILogger } from "aurelia";
export declare class BleetBadgeCustomAttribute {
    private readonly logger;
    private readonly ea;
    private readonly element;
    private closeButton?;
    id: string;
    constructor(logger?: ILogger, ea?: IEventAggregator, element?: HTMLButtonElement);
    attached(): void;
    detached(): void;
    private onClickRemove;
}
//# sourceMappingURL=badge.d.ts.map