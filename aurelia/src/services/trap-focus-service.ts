import {DI, ILogger, IPlatform, resolve} from "aurelia";

export interface ITrapFocusService extends TrapFocusService { }
export const ITrapFocusService = /*@__PURE__*/DI.createInterface<ITrapFocusService>('ITrapFocusService', (x) => x.transient(TrapFocusService));

export class TrapFocusService
{

    public focusableElementsQuerySelector = '[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"]), [accesskey], summary, canvas, audio, video, details, iframe, [contenteditable]';

    private opener: HTMLElement|null = null;
    private target: HTMLElement|null = null;
    private globalElement: HTMLElement|null = null;
    private startCallback: Function|null = null;
    private stopCallback: Function|null = null;
    private focusableElements: HTMLElement[] = [];
    private lastFocusedElement: HTMLElement|null = null;
    private started: boolean = false;


    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('TrapFocusService'),
        private readonly platform:IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor')
    }

    private buildFocusableElements() {
        this.focusableElements = [];
        const focusableElements = this.target?.querySelectorAll(this.focusableElementsQuerySelector);
        focusableElements?.forEach((element: Element) => {
            const isDisabled = element.hasAttribute('disabled');
            const isAriaHidden = (element.getAttribute('aria-hidden') === 'true');
            const isNotTabbable = (element.getAttribute('tabindex') === '-1');
            if (isDisabled === false && isAriaHidden === false && isNotTabbable === false) {
                this.focusableElements.push(element as HTMLElement);
            }
        });
    }

    public start(opener: HTMLElement, target: HTMLElement, globalElement: HTMLElement, startCallback?: Function, stopCallback?: Function, initialFocusElement?: HTMLElement) {
        this.logger.trace('start', opener, target);
        this.startCallback = startCallback ?? null;
        this.stopCallback = stopCallback ?? null;
        this.opener = opener;
        this.target = target;
        this.globalElement = globalElement;
        this.buildFocusableElements();

        // Use provided initialFocusElement if valid, otherwise default to first focusable
        if (initialFocusElement && this.focusableElements.includes(initialFocusElement)) {
            this.lastFocusedElement = initialFocusElement;
        } else {
            this.lastFocusedElement = this.focusableElements[0] || undefined;
        }

        this.logger.trace('start: add keydown listener');
        this.platform.requestAnimationFrame(() => {
            this.logger.trace('start: focus initial element', this.lastFocusedElement);
            this.lastFocusedElement?.focus();

        });
        this.target.addEventListener('keydown', this.onKeyDown);
        this.platform.document.addEventListener('click', this.onClickOutside);
        this.started = true;
        if(this.startCallback) {
            const promise = this.startCallback();
            if (promise && promise instanceof Promise) {
                return promise;
            }
        }
        return Promise.resolve();

    }
    public stop() {
        this.logger.trace('stop');
        return new Promise((resolve, reject) => {

            if (this.started) {
                this.logger.trace('stop: remove keydown listener');
                this.target?.removeEventListener('keydown', this.onKeyDown);
                this.platform.document.removeEventListener('click', this.onClickOutside);

                if(this.stopCallback) {
                    const promise = this.stopCallback();
                    if (promise && promise instanceof Promise) {
                        this.platform.requestAnimationFrame(() => {
                            this.opener?.focus()
                            this.cleanup();
                            promise.then((res) => {
                                return resolve(res);
                            });
                        });
                        return;
                    }
                    return resolve(void 0);
                }

                this.platform.requestAnimationFrame(() => {
                    this.opener?.focus()
                    this.cleanup();
                    return resolve(void 0);
                });
                return;
            }

            return reject('TrapFocusService: not started');
        });
    }
    private cleanup() {
        this.logger.trace('cleanup');
        if (this.started) {
            this.opener = null;
            this.startCallback = null;
            this.stopCallback = null;
            this.target = null;
            this.lastFocusedElement = null;
            this.focusableElements = [];
            this.started = false;
        }
    }


    private focusedElementIndex() : number {
        let index = -1;
        if (this.lastFocusedElement) {
            index = this.focusableElements.indexOf(this.lastFocusedElement);
        }
        if (index === -1 && this.lastFocusedElement !== undefined) {
            this.lastFocusedElement = null;
        }
        return index;
    }
    private focusPreviousElement(loop = true): HTMLElement | null {

        const currentIndex = this.focusedElementIndex();
        if (currentIndex === -1) {
            return null;
        }
        let changed = false;
        if (currentIndex === 0 && loop === true) {
            this.lastFocusedElement = this.focusableElements[this.focusableElements.length - 1];
            changed = true;
        } else if (currentIndex > 0) {
            this.lastFocusedElement = this.focusableElements[currentIndex - 1];
            changed = true;
        }
        if (changed === true) {
            this.platform.requestAnimationFrame(() => {
                this.logger.trace('focusPreviousElement: focusing', this.lastFocusedElement);
                this.lastFocusedElement?.focus();
            });
        }
        return this.lastFocusedElement;
    }
    private focusNextElement(loop = true): HTMLElement | null {
        const currentIndex = this.focusedElementIndex();
        if (currentIndex === -1) {
            return null;
        }
        let changed = false;
        if (currentIndex === this.focusableElements.length - 1 && loop === true) {
            this.lastFocusedElement = this.focusableElements[0];
            changed = true;
        } else if (currentIndex < this.focusableElements.length - 1) {
            this.lastFocusedElement = this.focusableElements[currentIndex + 1];
            changed = true;
        }
        if (changed === true) {
            this.platform.requestAnimationFrame(() => {
                this.logger.trace('focusNextElement: focusing', this.lastFocusedElement);
                this.lastFocusedElement?.focus();
            });
        }
        return this.lastFocusedElement;
    }

    private onKeyDown = (event: KeyboardEvent) => {
        let changeFocus = false;
        if (event.key === 'Tab') {
            if (event.shiftKey) {
                // shift + tab loop backwards
                event.preventDefault();
                this.lastFocusedElement = this.focusPreviousElement(true);
            } else {
                // tab loop forwards
                event.preventDefault();
                this.lastFocusedElement = this.focusNextElement(true);
            }
        } else if (event.key === 'ArrowUp') {
            // up arrow, no loop
            event.preventDefault();
            this.lastFocusedElement = this.focusPreviousElement(false);
        } else if (event.key === 'ArrowDown') {
            // down arrow, no loop
            event.preventDefault();
            this.lastFocusedElement = this.focusNextElement(false);
        } else if (event.key === 'Escape') {
            // stop trap focus
            event.preventDefault();
            this.stop();
        }
    };
    private onClickOutside = (event: MouseEvent) => {
        this.logger.trace('onClickOutside', event);
        if (this.started && this.globalElement && event.target) {
            this.logger.trace('onClickOutside: checking if click is outside globalElement', event.target, this.globalElement);
            if (!this.globalElement.contains(event.target as Node)) {
                this.stop();
            }
        }
    }
}