import { ILogger } from "aurelia";
export declare class BleetPasswordCustomAttribute {
    private readonly logger;
    private readonly element;
    private button?;
    private iconHidden?;
    private iconVisible?;
    private input?;
    constructor(logger?: ILogger, element?: HTMLElement);
    attaching(): void;
    attached(): void;
    detaching(): void;
    private onToggle;
}
//# sourceMappingURL=password.d.ts.map