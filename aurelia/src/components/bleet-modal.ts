import { bindable, customElement, IDisposable, IEventAggregator, ILogger, IPlatform, resolve } from 'aurelia';
import { Channels, DialogAction, ModalAction, ModalStatus, OverlayAction, ToasterAction, UiColor } from '../enums/event-aggregator';
import { IModal, IModalStatus, IOverlay, IToast, IToaster } from '../interfaces/event-aggregator';
import { IDialogResponse } from '../interfaces/dialog';
import { ITransitionService } from '../services/transition-service';
import { ISvgService } from '../services/svg-service';
import { IApiService } from '../services/api-service';
import template from './bleet-modal.html';

@customElement({ name: 'bleet-modal', template })
export class BleetModal {
    @bindable id: string = '';

    private dialogElement: HTMLDialogElement;
    private disposable?: IDisposable;

    // State
    private loading: boolean = false;
    private color: UiColor = UiColor.Primary;
    private icon: string | null = null;
    private headerView: string | null = null;
    private contentView: string | null = null;
    private footerView: string | null = null;

    // Color classes — no Tailwind interpolation
    private static readonly ICON_BG_CLASSES: Record<UiColor, string> = {
        [UiColor.Primary]: 'bg-primary-100',
        [UiColor.Secondary]: 'bg-secondary-100',
        [UiColor.Success]: 'bg-success-100',
        [UiColor.Danger]: 'bg-danger-100',
        [UiColor.Warning]: 'bg-warning-100',
        [UiColor.Info]: 'bg-info-100',
        [UiColor.Accent]: 'bg-accent-100',
    };

    private static readonly ICON_TEXT_CLASSES: Record<UiColor, string> = {
        [UiColor.Primary]: 'text-primary-600',
        [UiColor.Secondary]: 'text-secondary-600',
        [UiColor.Success]: 'text-success-600',
        [UiColor.Danger]: 'text-danger-600',
        [UiColor.Warning]: 'text-warning-600',
        [UiColor.Info]: 'text-info-600',
        [UiColor.Accent]: 'text-accent-600',
    };

    private static readonly HEADER_BG_CLASSES: Record<UiColor, string> = {
        [UiColor.Primary]: 'bg-primary-600',
        [UiColor.Secondary]: 'bg-secondary-600',
        [UiColor.Success]: 'bg-success-600',
        [UiColor.Danger]: 'bg-danger-600',
        [UiColor.Warning]: 'bg-warning-600',
        [UiColor.Info]: 'bg-info-600',
        [UiColor.Accent]: 'bg-accent-600',
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
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<bleet-modal>'),
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly transitionService: ITransitionService = resolve(ITransitionService),
        private readonly svgService: ISvgService = resolve(ISvgService),
        private readonly apiService: IApiService = resolve(IApiService),
    ) {}

    // Getters
    private get iconSvg(): string | null {
        if (!this.icon) return null;
        return this.svgService.get(this.icon);
    }

    private get iconBgClass(): string {
        return BleetModal.ICON_BG_CLASSES[this.color] ?? BleetModal.ICON_BG_CLASSES[UiColor.Primary];
    }

    private get iconTextClass(): string {
        return BleetModal.ICON_TEXT_CLASSES[this.color] ?? BleetModal.ICON_TEXT_CLASSES[UiColor.Primary];
    }

    private get headerBgClass(): string {
        return BleetModal.HEADER_BG_CLASSES[this.color] ?? BleetModal.HEADER_BG_CLASSES[UiColor.Primary];
    }

    private get closeButtonTextClass(): string {
        return BleetModal.CLOSE_BUTTON_TEXT_CLASSES[this.color] ?? BleetModal.CLOSE_BUTTON_TEXT_CLASSES[UiColor.Primary];
    }

    // Lifecycle
    public attached(): void {
        this.disposable = this.ea.subscribe(Channels.Modal, this.onModalEvent);
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
    private onModalEvent = (data: IModal): void => {
        if (this.id && this.id !== '' && data.id === this.id) {
            if (data.action === ModalAction.Open && this.dialogElement.open !== true) {
                if (data.url) {
                    this.loadFromUrl(data.url);
                }
            } else if (data.action === ModalAction.Close && this.dialogElement.open === true) {
                this.close();
            } else if (data.action === ModalAction.Toggle) {
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
        if (target.closest('[data-modal="close"]')) {
            event.preventDefault();
            this.close();
        }
    }

    private onFormSubmit = (event: SubmitEvent): void => {
        const form = (event.target as HTMLElement).closest('form');
        if (!form) return;

        event.preventDefault();

        const formData = new FormData(form);
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

    // AJAX
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
        // Style
        this.color = response.color ?? UiColor.Primary;
        this.icon = response.icon ?? null;

        // Content
        if (response.header) this.headerView = response.header;
        if (response.content) this.contentView = response.content;
        if (response.footer) this.footerView = response.footer;

        // Actions
        this.executeActions(response);
    }

    private executeActions(response: IDialogResponse): void {
        // 1. Primary action
        if (response.action === DialogAction.Close) {
            this.close();
        }
        // DialogAction.Keep → do nothing, dialog stays open

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

    // Open / Close
    private open(): void {
        this.bindDialogEvents();
        this.transitionService.run(this.dialogElement, (element) => {
            this.ea.publish(Channels.Overlay, <IOverlay>{ action: OverlayAction.Open });
            this.ea.publish(Channels.ModalStatus, <IModalStatus>{ status: ModalStatus.Opening, id: this.id });
            (element as HTMLDialogElement).showModal();
            this.platform.requestAnimationFrame(() => {
                element.classList.add('opacity-100');
                element.classList.remove('opacity-0');
            });
        }, () => {
            this.ea.publish(Channels.ModalStatus, <IModalStatus>{ status: ModalStatus.Opened, id: this.id });
        });
    }

    private close(): void {
        this.transitionService.run(this.dialogElement, (element) => {
            this.ea.publish(Channels.ModalStatus, <IModalStatus>{ status: ModalStatus.Closing, id: this.id });
            this.ea.publish(Channels.Overlay, <IOverlay>{ action: OverlayAction.Close });
            element.classList.add('opacity-0');
            element.classList.remove('opacity-100');
        }, (element) => {
            (element as HTMLDialogElement).close();
            this.unbindDialogEvents();
            // Reset
            this.headerView = null;
            this.contentView = null;
            this.footerView = null;
            this.icon = null;
            this.color = UiColor.Primary;
            this.loading = false;
            this.ea.publish(Channels.ModalStatus, <IModalStatus>{ status: ModalStatus.Closed, id: this.id });
        });
    }
}
