import { IEventAggregator, ILogger } from "aurelia";
export declare class BleetToaster {
    private readonly logger;
    private readonly element;
    private readonly ea;
    private disposable?;
    private toasts;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator);
    attaching(): void;
    attached(): void;
    detached(): void;
    dispose(): void;
    private onToasterEvent;
}
//# sourceMappingURL=bleet-toaster.d.ts.map