import {DI, ILogger, IPlatform, resolve} from "aurelia";

export interface IStorageService extends StorageService { }
export const IStorageService = /*@__PURE__*/DI.createInterface<IStorageService>('IStorageService', (x) => x.singleton(StorageService));

export class StorageService
{
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('StorageService'),
        private readonly platform:IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor')
    }

    public load(key: string, def: any = null): any
    {
        this.logger.trace('load', key);
        const value = localStorage.getItem(key);
        if (value === null) {
            return def;
        }
        return JSON.parse(value);
    }

    public save(key: string, value: any): void
    {
        this.logger.trace('save', key, value);
        localStorage.setItem(key, JSON.stringify(value));
    }

    public remove(key: string): void
    {
        this.logger.trace('remove', key);
        localStorage.removeItem(key);
    }
}