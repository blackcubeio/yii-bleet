import {DI, IEventAggregator, ILogger, INode, IPlatform, resolve} from "aurelia";

export interface ITransitionService extends TransitionService { }
export const ITransitionService = /*@__PURE__*/DI.createInterface<ITransitionService>('ITransitionService', (x) => x.singleton(TransitionService));

export type TransitionCallback = (e: HTMLElement|HTMLDialogElement) => void
export class TransitionService
{
    public securityTimeout: number = 2000;
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('TransitionService'),
        private readonly platform:IPlatform = resolve(IPlatform),
    ) {
        this.logger.trace('constructor')
    }

    public run(element: HTMLElement, before: TransitionCallback, after?: TransitionCallback) {
        let securityTimeout: any = undefined;
        const endTransition = (evt: TransitionEvent) => {
            if (securityTimeout !== undefined) {
                this.platform.clearTimeout(securityTimeout);
                securityTimeout = undefined;
            }
            element.removeEventListener('transitionend', endTransition);
            if (after) {
                this.logger.trace('after()');
                after(element);
            }
        }
        if (before) {
            securityTimeout = this.platform.setTimeout(endTransition, this.securityTimeout);
            element.addEventListener('transitionend', endTransition);
            this.platform.requestAnimationFrame(() => {
                this.logger.trace('before()');
                before(element);
            });
        }
    }
}