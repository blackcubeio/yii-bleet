import { IContainer } from "aurelia";
import { InterfaceSymbol } from "@aurelia/kernel";
import { Transport, TransportConfig } from './enums/api';
import { ITransport } from './interfaces/api';
export interface CsrfConfig {
    enabled: boolean;
    metaName: string;
    headerName: string;
}
export interface ConfigInterface {
    transports?: TransportConfig;
    baseUrl?: string;
    csrf?: CsrfConfig;
}
export interface IBleetConfiguration extends Configure {
}
export declare const IBleetConfiguration: InterfaceSymbol<IBleetConfiguration>;
export declare class Configure {
    protected _config: ConfigInterface;
    private _container;
    private _transportInterfaces;
    setContainer(container: IContainer): void;
    getContainer(): IContainer | null;
    registerTransportInterface(type: Transport, iface: InterfaceSymbol<ITransport>): void;
    getCsrfConfig(): CsrfConfig;
    getConfig(): ConfigInterface;
    get<T = unknown>(key: string): T;
    set<T>(key: string, val: T): T;
    getTransports(): Transport[];
    getBaseUrl(transport: Transport): string;
    private isTransportWithConfig;
    getTransport(type: Transport): ITransport | null;
    getAvailableTransports(): ITransport[];
}
//# sourceMappingURL=configure.d.ts.map