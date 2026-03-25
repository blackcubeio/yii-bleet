import {customAttribute, IDisposable, IEventAggregator, ILogger, INode, IPlatform, resolve} from "aurelia";
import {Channels, MenuAction, MenuStatus, OverlayAction, OverlayStatus} from '../enums/event-aggregator';
import {IMenu, IMenuStatus, IOverlay, IOverlayStatus} from '../interfaces/event-aggregator';
import {ITransitionService} from '../services/transition-service';
import {IStorageService} from '../services/storage-service';

@customAttribute('bleet-menu')
export class BleetMenuCustomAttribute
{
    private disposable?: IDisposable;
    private disposableOverlay?: IDisposable;
    private closeButton?: HTMLButtonElement;
    private toggleButtons?: NodeListOf<HTMLButtonElement>;
    private sublists: Map<HTMLButtonElement, {list: HTMLElement, svg: HTMLElement}> = new Map();
    private isOpen: boolean = false;
    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('bleet-menu'),
        private readonly ea:IEventAggregator = resolve(IEventAggregator),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
        private readonly storageService: IStorageService = resolve(IStorageService),
    ) {
        this.logger.trace('constructor')
    }

    public attaching()
    {
        this.logger.trace('attaching');
        this.closeButton = this.element.querySelector('[data-menu=close]') as HTMLButtonElement;
        this.toggleButtons = this.element.querySelectorAll('[data-menu^="toggle-button"]');
        this.initMenuButtons();
    }
    public attached()
    {
        this.logger.trace('attached');
        this.disposable = this.ea.subscribe(Channels.Menu, this.onMenuEvent);
        this.disposableOverlay = this.ea.subscribe(Channels.OverlayStatus, this.onOverlayStatus);
        this.closeButton?.addEventListener('click', this.onClickClose);
        this.element.addEventListener('click', this.onClickToggleButtons)
    }

    public detached()
    {
        this.logger.trace('detached');
        this.closeButton?.removeEventListener('click', this.onClickClose);
        this.element.removeEventListener('click', this.onClickToggleButtons)
        this.disposableOverlay?.dispose();
        this.disposable?.dispose();
    }
    public dispose() {
        this.logger.trace('dispose');
        this.disposableOverlay?.dispose();
        this.disposable?.dispose();
    }

    private open(fromOverlay: boolean = false) {
        if (!this.isOpen) {
            this.logger.trace('open');
            this.isOpen = true;
            this.transitionService.run(this.element, (element: HTMLElement) => {
                if (!fromOverlay) {
                    this.ea.publish(Channels.Overlay, <IOverlay>{action: OverlayAction.Open});
                }
                this.ea.publish(Channels.MenuStatus, <IMenuStatus>{status: MenuStatus.Opening});
                element.classList.add('translate-x-0');
                element.classList.remove('-translate-x-full');
                element.ariaHidden = 'false';
            }, (element: HTMLElement) => {
                this.ea.publish(Channels.MenuStatus, <IMenuStatus>{status: MenuStatus.Opened});
            });
        }
    }
    private close(fromOverlay: boolean = false) {
        if (this.isOpen) {
            this.logger.trace('close');
            this.isOpen = false;
            this.transitionService.run(this.element, (element: HTMLElement) => {
                this.ea.publish(Channels.MenuStatus, <IMenuStatus>{status: MenuStatus.Closing});
                if (!fromOverlay) {
                    this.ea.publish(Channels.Overlay, <IOverlay>{action: OverlayAction.Close});
                }
                element.classList.add('-translate-x-full');
                element.classList.remove('translate-x-0');
                element.ariaHidden = 'true';
            }, (element: HTMLElement) => {
                this.ea.publish(Channels.MenuStatus, <IMenuStatus>{status: MenuStatus.Closed});
            });
        }
    }

    private onClickClose = (event: MouseEvent) => {
        this.logger.trace('onClickClose', event);
        event.preventDefault();
        this.close();
    }

    private onClickToggleButtons = (event: MouseEvent) => {
        const target = event.target as HTMLElement;
        const btn = target.closest('[data-menu^="toggle-button"]') as HTMLButtonElement;
        if (btn && btn.matches('[data-menu^="toggle-button"]')) {
            event.preventDefault();
            this.toggleButton(btn);
        }
    }

    private initMenuButtons() {
        this.logger.trace('initMenu');
        this.toggleButtons?.forEach((btn: HTMLButtonElement) => {
            if (!this.sublists.has(btn)) {
                const id = btn.dataset.menu?.replace('toggle-button-', '');
                const isOpen = this.storageService.load(`toggle-list-${id}`, false);
                const list = this.element.querySelector(`[data-menu="toggle-list-${id}"]`) as HTMLUListElement;
                const svg = btn.querySelector('svg[data-menu=icon]') as HTMLElement;
                this.sublists.set(btn, {list, svg});
                if (!isOpen) {
                    svg.classList.remove('rotate-180');
                    list.classList.add('hidden');
                    btn.ariaExpanded = 'false';
                } else {
                    svg.classList.add('rotate-180');
                    list.classList.remove('hidden');
                    btn.ariaExpanded = 'true';
                }
            }
        });
    }

    private toggleButton(btn: HTMLButtonElement) {
        if (this.sublists.has(btn)) {
            const sublist = this.sublists.get(btn);
            const id = btn.dataset.menu?.replace('toggle-button-', '');
            if (sublist?.list.classList.contains('hidden')) {
                sublist?.list.classList.remove('hidden');
                sublist?.svg.classList.add('rotate-180');
                btn.ariaExpanded = 'true';
                this.storageService.save(`toggle-list-${id}`, true);
            } else {
                sublist?.list.classList.add('hidden');
                sublist?.svg.classList.remove('rotate-180');
                btn.ariaExpanded = 'false';
                this.storageService.save(`toggle-list-${id}`, false);
            }
        }
    }
    private closeOtherButtons(except: HTMLButtonElement) {
        this.sublists.forEach((value, key) => {
            if (key !== except) {
                const id = key.dataset.menu?.replace('toggle-button-', '');
                this.storageService.save(`toggle-list-${id}`, false);
                value.list.classList.add('hidden');
                value.svg.classList.remove('rotate-180');
            }
        });
    }
    private onMenuEvent = (data: IMenu) => {
        this.logger.trace('onMenuEvent', data);
        if (data.action === MenuAction.Close) {
            // this.element.classList.add('-translate-x-full');
            this.logger.trace('Menu Close action received');
            this.close();
        } else if (data.action === MenuAction.Open) {
            // this.element.classList.remove('-translate-x-full');
            this.logger.trace('Menu Open action received');
            this.open();
        } else if (data.action === MenuAction.Toggle) {
            // this.element.classList.toggle('-translate-x-full');
            this.logger.trace('Menu Toggle action received');
        }
    }
    private onOverlayStatus = (data: IOverlayStatus) => {
        if (data.status === OverlayStatus.Closing) {
            this.logger.trace('Overlay Close action received');
            this.close(true);
        } else {
            this.logger.trace('onOverlayStatus unhandled', data);
        }
    }
}