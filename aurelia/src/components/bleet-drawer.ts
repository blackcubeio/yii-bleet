import { bindable, customElement, IDisposable, IEventAggregator, ILogger, IPlatform, resolve } from 'aurelia';
import { Channels, DialogAction, DrawerAction, DrawerStatus, OverlayAction, ToasterAction, UiColor } from '../enums/event-aggregator';
import { IDrawer, IDrawerStatus, IOverlay, IToast, IToaster } from '../interfaces/event-aggregator';
import { IDialogResponse } from '../interfaces/dialog';
import { ITransitionService } from '../services/transition-service';
import { ISvgService } from '../services/svg-service';
import { IApiService } from '../services/api-service';
import template from './bleet-drawer.html';

@customElement({ name: 'bleet-drawer', template })
export class BleetDrawer {
    @bindable id: string = '';

    private dialogElement: HTMLDialogElement;
    private disposable?: IDisposable;

    // State
    private loading: boolean = false;
    private color: UiColor = UiColor.Primary;
    private headerView: string | null = null;
    private contentView: string | null = null;
    private footerView: string | null = null;

    // Color classes for header
    private static readonly HEADER_BG_CLASSES: Record<UiColor, string> = {
        [UiColor.Primary]: 'bg-primary-700',
        [UiColor.Secondary]: 'bg-secondary-700',
        [UiColor.Success]: 'bg-success-700',
        [UiColor.Danger]: 'bg-danger-700',
        [UiColor.Warning]: 'bg-warning-700',
        [UiColor.Info]: 'bg-info-700',
        [UiColor.Accent]: 'bg-accent-700',
    };

    private static readonly CLOSE_BUTTON_TEXT_CLASSES: Record<UiColor, string> = {
        [UiColor.Primary]: 'text-primary-200',
        [UiColor.Secondary]: 'text-secondary-200',
        [UiColor.Success]: 'text-success-200',
        [UiColor.Danger]: 'text-danger-200',
        [UiColor.Warning]: 'text-warning-200',
        [UiColor.Info]: 'text-info-200',
        [UiColor.Accent]: 'text-accent-200',
    };

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<bleet-drawer>'),
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
        private readonly svgService: ISvgService = resolve(ISvgService),
        private readonly apiService: IApiService = resolve(IApiService),
    ) {}

    // Getters
    private get headerBgClass(): string {
        return BleetDrawer.HEADER_BG_CLASSES[this.color] ?? BleetDrawer.HEADER_BG_CLASSES[UiColor.Primary];
    }

    private get closeButtonTextClass(): string {
        return BleetDrawer.CLOSE_BUTTON_TEXT_CLASSES[this.color] ?? BleetDrawer.CLOSE_BUTTON_TEXT_CLASSES[UiColor.Primary];
    }

    // Lifecycle
    public attached(): void {
        this.disposable = this.ea.subscribe(Channels.Drawer, this.onDrawerEvent);
        this.dialogElement.addEventListener('close', this.onCloseEvent);
        this.dialogElement.addEventListener('cancel', this.onCancelEvent);
    }

    public detached(): void {
        this.dialogElement.removeEventListener('close', this.onCloseEvent);
        this.dialogElement.removeEventListener('cancel', this.onCancelEvent);
        this.disposable?.dispose();
    }

    private bindDialogEvents(): void {
        this.dialogElement.addEventListener('submit', this.onFormSubmit);
        this.dialogElement.addEventListener('click', this.onDialogClick);
    }

    private unbindDialogEvents(): void {
        this.dialogElement.removeEventListener('submit', this.onFormSubmit);
        this.dialogElement.removeEventListener('click', this.onDialogClick);
    }

    // Handlers
    private onDrawerEvent = (data: IDrawer): void => {
        if (this.id && this.id !== '' && data.id === this.id) {
            if (data.action === DrawerAction.Open && this.dialogElement.open !== true) {
                if (data.url) {
                    this.loadFromUrl(data.url);
                }
            } else if (data.action === DrawerAction.Close && this.dialogElement.open === true) {
                this.close();
            } else if (data.action === DrawerAction.Toggle) {
                if (this.dialogElement.open === true) {
                    this.close();
                } else if (data.url) {
                    this.loadFromUrl(data.url);
                }
            }
        }
    }

    private onCloseEvent = (event: Event): void => {
        this.ea.publish(Channels.Overlay, <IOverlay>{ action: OverlayAction.Close });
    }

    private onCancelEvent = (event: Event): void => {
        this.close();
    }

    private onDialogClick = (event: MouseEvent): void => {
        const target = event.target as HTMLElement;
        if (target.closest('[data-drawer="close"]')) {
            event.preventDefault();
            this.close();
        }
    }

    private onFormSubmit = (event: SubmitEvent): void => {
        const form = (event.target as HTMLElement).closest('form');
        if (!form) return;

        event.preventDefault();

        const formData = new FormData(form);

        // Capture submitter button name/value (not included by default in FormData)
        if (event.submitter instanceof HTMLButtonElement && event.submitter.name) {
            formData.append(event.submitter.name, event.submitter.value || '');
        }

        const method = (formData.get('_method') as string) || form.getAttribute('method') || 'POST';

        this.apiService
            .url(form.action)
            .fromMultipart(formData)
            .request<IDialogResponse>(method)
            .then((response) => {
                this.applyResponse(response.body);
            })
            .catch((error) => {
                this.logger.error('form submit failed', error);
                this.ea.publish(Channels.Toaster, <IToaster>{
                    action: ToasterAction.Add,
                    toast: { color: UiColor.Danger, content: 'Erreur lors de l\'envoi' } as IToast
                });
            });
    }

    private loadFromUrl(url: string): void {
        this.loading = true;
        this.open();

        this.apiService.url(url).get<IDialogResponse>()
            .then((response) => {
                this.applyResponse(response.body);
            })
            .catch((error) => {
                this.logger.error('loadFromUrl failed', error);
                this.close();
                this.ea.publish(Channels.Toaster, <IToaster>{
                    action: ToasterAction.Add,
                    toast: { color: UiColor.Danger, content: 'Erreur de chargement' } as IToast
                });
            })
            .finally(() => {
                this.loading = false;
            });
    }

    private applyResponse(response: IDialogResponse): void {
        this.color = response.color ?? UiColor.Primary;
        if (response.header) this.headerView = response.header;
        if (response.content) this.contentView = response.content;
        if (response.footer) this.footerView = response.footer;
        this.executeActions(response);
    }

    private executeActions(response: IDialogResponse): void {
        // 1. Primary action
        if (response.action === DialogAction.Close) {
            this.close();
        }

        // 2. Secondary actions (combinable)
        if (response.toast) {
            this.ea.publish(Channels.Toaster, <IToaster>{
                action: ToasterAction.Add,
                toast: response.toast
            });
        }
        if (response.ajaxify) {
            this.ea.publish(Channels.Ajaxify, response.ajaxify);
        } else if (response.redirect) {
            this.platform.window.location.href = response.redirect;
        } else if (response.refresh) {
            this.platform.window.location.reload();
        }
    }

    // Open / Close — translate-x
    private open(): void {
        this.bindDialogEvents();
        this.transitionService.run(this.dialogElement, (element) => {
            this.ea.publish(Channels.Overlay, <IOverlay>{ action: OverlayAction.Open });
            this.ea.publish(Channels.DrawerStatus, <IDrawerStatus>{ status: DrawerStatus.Opening, id: this.id });
            (element as HTMLDialogElement).showModal();
            this.platform.requestAnimationFrame(() => {
                element.classList.add('translate-x-0');
                element.classList.remove('translate-x-full');
            });
        }, () => {
            this.ea.publish(Channels.DrawerStatus, <IDrawerStatus>{ status: DrawerStatus.Opened, id: this.id });
        });
    }

    private close(): void {
        this.transitionService.run(this.dialogElement, (element) => {
            this.ea.publish(Channels.DrawerStatus, <IDrawerStatus>{ status: DrawerStatus.Closing, id: this.id });
            this.ea.publish(Channels.Overlay, <IOverlay>{ action: OverlayAction.Close });
            element.classList.add('translate-x-full');
            element.classList.remove('translate-x-0');
        }, (element) => {
            (element as HTMLDialogElement).close();
            this.unbindDialogEvents();
            this.headerView = null;
            this.contentView = null;
            this.footerView = null;
            this.color = UiColor.Primary;
            this.loading = false;
            this.ea.publish(Channels.DrawerStatus, <IDrawerStatus>{ status: DrawerStatus.Closed, id: this.id });
        });
    }
}
