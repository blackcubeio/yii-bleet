import {bindable, customAttribute, IEventAggregator, ILogger, INode, IPlatform, resolve} from "aurelia";
import {AjaxifyAction, AjaxifyTriggerMode, Channels, ToasterAction, UiColor} from '../enums/event-aggregator';
import {IAjaxify, IAjaxifyResponse, IToast, IToaster} from '../interfaces/event-aggregator';
import {IApiService} from '../services/api-service';

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
 *   - id: If set, also collects inputs with data-ajaxify="{id}" from closest form
 *   - mode: AjaxifyTriggerMode.Classic (default) or AjaxifyTriggerMode.PublishOnly
 *   - target: bleet-ajaxify id to publish the event to (publishOnly mode)
 *   - method: HTTP method for the ajaxify event (publishOnly mode, default: 'POST')
 */
@customAttribute({ name: 'bleet-ajaxify-trigger', defaultProperty: 'url' })
export class BleetAjaxifyTriggerCustomAttribute {

    @bindable url: string = '';
    @bindable() verb: string = '';
    @bindable() event: string = 'click';
    @bindable() id: string = '';
    @bindable() mode: string = AjaxifyTriggerMode.Classic;
    @bindable() target: string = '';
    @bindable() method: string = 'POST';

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-ajaxify-trigger'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly apiService: IApiService = resolve(IApiService),
        private readonly p: IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor');
    }

    public attached(): void {
        this.logger.trace('attached', { url: this.url, verb: this.verb, event: this.event, mode: this.mode });
        this.element.addEventListener(this.event, this.onTrigger);
    }

    public detached(): void {
        this.logger.trace('detached');
        this.element.removeEventListener(this.event, this.onTrigger);
    }

    private onTrigger = (event: Event): void => {
        event.preventDefault();
        event.stopPropagation();
        this.logger.trace('onTrigger', event);

        if (this.mode === AjaxifyTriggerMode.PublishOnly) {
            this.onTriggerPublishOnly();
            return;
        }

        this.onTriggerClassic(event);
    };

    /**
     * PublishOnly mode: enable disabled child inputs, publish ajaxify event.
     * No server call — bleet-ajaxify will collect the form and handle the request.
     *
     * Linked elements (data-ajaxify="{id}") can specify data-ajaxify-target
     * to copy their value into a matching hidden input inside this.element.
     */
    private onTriggerPublishOnly(): void {
        this.logger.debug('onTriggerPublishOnly', { target: this.target, method: this.method });

        if (this.id) {
            const form = this.element.closest('form');
            if (form) {
                const linkedElements = form.querySelectorAll(`[data-ajaxify="${this.id}"]`);
                for (const linked of Array.from(linkedElements)) {
                    if (linked instanceof HTMLSelectElement || linked instanceof HTMLInputElement || linked instanceof HTMLTextAreaElement) {
                        const targetName = linked.dataset.ajaxifyTarget;
                        if (targetName) {
                            const hidden = this.element.querySelector(`input[name="${targetName}"]`) as HTMLInputElement;
                            if (hidden) {
                                hidden.value = linked.value;
                            }
                        }
                    }
                }
            }
        }

        const disabledInputs = this.element.querySelectorAll('input:disabled, select:disabled, textarea:disabled');
        for (const input of Array.from(disabledInputs)) {
            (input as HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement).disabled = false;
        }

        this.ea.publish(Channels.Ajaxify, <IAjaxify>{
            action: AjaxifyAction.Run,
            id: this.target,
            url: this.url || undefined,
            method: this.method,
        });
    }

    /**
     * Classic mode: POST to server, process response (existing behavior).
     */
    private onTriggerClassic(event: Event): void {
        const form = this.element.closest('form');
        const url = this.resolveUrl(form);
        const verb = this.resolveVerb(form);

        if (!url) {
            this.logger.warn('No URL found for ajaxify-trigger');
            return;
        }

        this.logger.debug('onTriggerClassic', { url, verb });

        const formData = this.buildFormData(event);

        this.apiService
            .url(url)
            .fromMultipart(formData)
            .request<IAjaxifyResponse>(verb)
            .then((response) => {
                this.logger.debug('response', response.body);

                if (response.body.element) {
                    this.updateElement(response.body.element);
                }

                if (response.body.toast) {
                    this.ea.publish(Channels.Toaster, <IToaster>{
                        action: ToasterAction.Add,
                        toast: response.body.toast
                    });
                }

                if (response.body.ajaxify) {
                    this.ea.publish(Channels.Ajaxify, response.body.ajaxify);
                }
            })
            .catch((error) => {
                this.logger.error('AJAX request failed', error);

                this.ea.publish(Channels.Toaster, <IToaster>{
                    action: ToasterAction.Add,
                    toast: {
                        color: UiColor.Danger,
                        title: 'Erreur',
                        content: 'Une erreur est survenue.',
                        duration: 5000,
                    } as IToast
                });
            });
    }

    private buildFormData(event: Event): FormData {
        const formData = new FormData();

        const inputs = this.element.querySelectorAll('input, select, textarea');
        this.appendInputsToFormData(formData, inputs);

        if (event instanceof SubmitEvent && event.submitter) {
            const submitter = event.submitter as HTMLButtonElement | HTMLInputElement;
            if (submitter.name) {
                formData.append(submitter.name, submitter.value || '');
            }
        }

        const form = this.element.closest('form');
        if (form) {
            const csrfInput = form.querySelector('input[name="_csrf"]') as HTMLInputElement | null;
            if (csrfInput) {
                formData.append('_csrf', csrfInput.value);
            }

            if (this.id) {
                const ajaxifyInputs = form.querySelectorAll(`[data-ajaxify="${this.id}"]`);
                this.appendInputsToFormData(formData, ajaxifyInputs);
            }
        }

        return formData;
    }

    private appendInputsToFormData(formData: FormData, inputs: NodeListOf<Element>): void {
        for (const input of Array.from(inputs)) {
            if (input instanceof HTMLInputElement) {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    if (input.checked && input.name) {
                        formData.append(input.name, input.value || 'on');
                    }
                } else if (input.name) {
                    formData.append(input.name, input.value);
                }
            } else if (input instanceof HTMLSelectElement && input.name) {
                formData.append(input.name, input.value);
            } else if (input instanceof HTMLTextAreaElement && input.name) {
                formData.append(input.name, input.value);
            }
        }
    }

    private resolveUrl(form: HTMLFormElement | null): string {
        if (this.url) {
            return this.url;
        }
        if (form) {
            return form.action || '';
        }
        return '';
    }

    private resolveVerb(form: HTMLFormElement | null): string {
        if (this.verb) {
            return this.verb;
        }
        if (form) {
            return form.method || 'POST';
        }
        return 'POST';
    }

    private updateElement(html: string): void {
        const template = this.p.document.createElement('template');
        template.innerHTML = html.trim();
        const newElement = template.content.firstElementChild;

        if (newElement) {
            this.syncElement(this.element, newElement);
        }
    }

    private syncElement(current: Element, incoming: Element): void {
        for (const attr of Array.from(incoming.attributes)) {
            current.setAttribute(attr.name, attr.value);
        }

        for (const attr of Array.from(current.attributes)) {
            if (!incoming.hasAttribute(attr.name)) {
                current.removeAttribute(attr.name);
            }
        }

        if (current instanceof HTMLInputElement && incoming instanceof HTMLInputElement) {
            if (current.type === 'checkbox' || current.type === 'radio') {
                current.checked = incoming.checked;
            } else {
                current.value = incoming.value;
            }
        }

        const currentChildren = Array.from(current.children);
        const incomingChildren = Array.from(incoming.children);

        for (let i = 0; i < incomingChildren.length; i++) {
            if (i < currentChildren.length) {
                this.syncElement(currentChildren[i], incomingChildren[i]);
            }
        }
    }
}
