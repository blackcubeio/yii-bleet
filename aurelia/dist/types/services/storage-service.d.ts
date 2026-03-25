import { ILogger, IPlatform } from "aurelia";
export interface IStorageService extends StorageService {
}
export declare const IStorageService: import("@aurelia/kernel").InterfaceSymbol<IStorageService>;
export declare class StorageService {
    private readonly logger;
    private readonly platform;
    constructor(logger?: ILogger, platform?: IPlatform);
    load(key: string, def?: any): any;
    save(key: string, value: any): void;
    remove(key: string): void;
}
//# sourceMappingURL=storage-service.d.ts.map