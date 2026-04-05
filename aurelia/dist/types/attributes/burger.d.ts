import { IEventAggregator, ILogger } from "aurelia";
export declare class BleetBurgerCustomAttribute {
    private readonly logger;
    private readonly ea;
    private readonly element;
    constructor(logger?: ILogger, ea?: IEventAggregator, element?: HTMLButtonElement);
    attached(): void;
    detached(): void;
    private onClickButton;
}
//# sourceMappingURL=burger.d.ts.map