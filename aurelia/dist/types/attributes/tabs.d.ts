import { ILogger, IPlatform } from "aurelia";
export declare class BleetTabsCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly p;
    private activeClasses;
    private inactiveClasses;
    private select?;
    constructor(logger?: ILogger, element?: HTMLElement, p?: IPlatform);
    attaching(): void;
    attached(): void;
    detached(): void;
    private onClickTab;
    private onChangeSelect;
}
//# sourceMappingURL=tabs.d.ts.map