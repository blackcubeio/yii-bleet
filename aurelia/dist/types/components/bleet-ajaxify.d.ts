import { IEventAggregator, ILogger } from 'aurelia';
import { IApiService } from '../services/api-service';
export declare class BleetAjaxify {
    private ea;
    private logger;
    private apiService;
    id: string;
    url?: string;
    private ajaxedView;
    private disposable?;
    constructor(ea?: IEventAggregator, logger?: ILogger, apiService?: IApiService);
    attaching(): void;
    detaching(): void;
    dispose(): void;
    private onEvent;
}
//# sourceMappingURL=bleet-ajaxify.d.ts.map