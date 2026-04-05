import { ILogger, IPlatform } from "aurelia";
export interface ITrapFocusService extends TrapFocusService {
}
export declare const ITrapFocusService: import("@aurelia/kernel").InterfaceSymbol<ITrapFocusService>;
export declare class TrapFocusService {
    private readonly logger;
    private readonly platform;
    focusableElementsQuerySelector: string;
    private opener;
    private target;
    private globalElement;
    private startCallback;
    private stopCallback;
    private focusableElements;
    private lastFocusedElement;
    private started;
    constructor(logger?: ILogger, platform?: IPlatform);
    private buildFocusableElements;
    start(opener: HTMLElement, target: HTMLElement, globalElement: HTMLElement, startCallback?: Function, stopCallback?: Function, initialFocusElement?: HTMLElement): Promise<any>;
    stop(): Promise<unknown>;
    private cleanup;
    private focusedElementIndex;
    private focusPreviousElement;
    private focusNextElement;
    private onKeyDown;
    private onClickOutside;
}
//# sourceMappingURL=trap-focus-service.d.ts.map