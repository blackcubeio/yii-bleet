import { ILogger, IPlatform } from "aurelia";
export declare class BleetPagerCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly p;
    private select?;
    constructor(logger?: ILogger, element?: HTMLElement, p?: IPlatform);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onChangeSelect;
}
//# sourceMappingURL=pager.d.ts.map