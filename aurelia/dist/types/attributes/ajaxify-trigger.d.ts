import { IEventAggregator, ILogger } from "aurelia";
import { IApiService } from '../services/api-service';
/**
 * Generic AJAX trigger attribute for elements.
 * Placed on the element that triggers the AJAX call.
 * Looks up parent form with closest() to get URL/verb if not specified.
 * Sends only the inputs contained within this.element.
 * Pessimistic UI: state changes only on server response.
 *
 * Bindables:
 *   - url (primary): URL to call. Falls back to closest form.action.
 *   - verb: HTTP method. Falls back to closest form.method or 'POST'.
 *   - event: DOM event to listen for (default: 'click')
 *   - collect: If set, also collects inputs with data-ajaxify="{collect}" from closest form
 */
export declare class BleetAjaxifyTriggerCustomAttribute {
    private readonly logger;
    private readonly element;
    private readonly ea;
    private readonly apiService;
    url: string;
    verb: string;
    event: string;
    id: string;
    constructor(logger?: ILogger, element?: HTMLElement, ea?: IEventAggregator, apiService?: IApiService);
    attached(): void;
    detached(): void;
    private onTrigger;
    private buildFormData;
    private appendInputsToFormData;
    private resolveUrl;
    private resolveVerb;
    private updateElement;
    private syncElement;
}
//# sourceMappingURL=ajaxify-trigger.d.ts.map