import { ILogger, IPlatform } from "aurelia";
export interface ITransitionService extends TransitionService {
}
export declare const ITransitionService: import("@aurelia/kernel").InterfaceSymbol<ITransitionService>;
export type TransitionCallback = (e: HTMLElement | HTMLDialogElement) => void;
export declare class TransitionService {
    private readonly logger;
    private readonly platform;
    securityTimeout: number;
    constructor(logger?: ILogger, platform?: IPlatform);
    run(element: HTMLElement, before: TransitionCallback, after?: TransitionCallback): void;
}
//# sourceMappingURL=transition-service.d.ts.map