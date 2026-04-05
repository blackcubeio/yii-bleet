import { ILogger } from "aurelia";
export interface ISvgService extends SvgService {
}
export declare const ISvgService: import("@aurelia/kernel").InterfaceSymbol<ISvgService>;
export declare class SvgService {
    private readonly logger;
    private static readonly ICONS;
    constructor(logger?: ILogger);
    /**
     * Retourne le SVG pour une icône
     * @param icon Clé heroicon (ex: 'check-circle') ou SVG inline custom
     * @returns Le SVG du map si clé connue, sinon retourne icon tel quel (SVG custom)
     */
    get(icon: string): string | null;
    has(key: string): boolean;
}
//# sourceMappingURL=svg-service.d.ts.map