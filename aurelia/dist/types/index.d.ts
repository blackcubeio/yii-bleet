import { IContainer } from 'aurelia';
import { ConfigInterface } from './configure';
import { Transport, TransportConfig } from './enums/api';
import { ITransport } from './interfaces/api';
import { IHttpService } from './services/http-service';
import { ISvgService } from './services/svg-service';
import { IApiService } from './services/api-service';
import { ISocketioService } from './services/socketio-service';
import { IStorageService } from './services/storage-service';
import { ITransitionService } from './services/transition-service';
import { ITrapFocusService } from './services/trap-focus-service';
import { BleetAlertCustomAttribute, BleetBadgeCustomAttribute, BleetBurgerCustomAttribute, BleetDrawerTriggerCustomAttribute, BleetDropdownCustomAttribute, BleetMenuCustomAttribute, BleetModalTriggerCustomAttribute, BleetPagerCustomAttribute, BleetPasswordCustomAttribute, BleetProfileCustomAttribute, BleetSelectCustomAttribute, BleetTabsCustomAttribute, BleetToasterTriggerCustomAttribute, BleetUploadCustomAttribute, BleetAjaxifyTriggerCustomAttribute, BleetPopoverCustomAttribute, BleetPopoverTriggerCustomAttribute } from './attributes';
import { BleetOverlay, BleetToast, BleetToaster, BleetToasterTrigger, BleetModal, BleetDrawer, BleetAjaxify, BleetQuilljs } from './components';
import { Channels, OverlayAction, OverlayStatus, ProfileAction, ProfileStatus, ModalAction, ModalStatus, DrawerAction, DrawerStatus, ToasterAction, ToasterStatus, MenuAction, MenuStatus, BadgeAction, UiColor, UiToastIcon, UiIcon, DialogAction, AjaxifyAction, PopoverAction, PopoverStatus } from './enums/event-aggregator';
import { IDialogResponse } from './interfaces/dialog';
import { IOverlay, IOverlayStatus, IModal, IModalStatus, IDrawer, IDrawerStatus, IToaster, IToasterStatus, IToast, IMenu, IMenuStatus, IBadge, IProfile, IProfileStatus, IAjaxify, IPopover, IPopoverStatus } from './interfaces/event-aggregator';
import { AjaxifyCodec } from './codecs/ajaxify-codec';
import { RequestCodec } from './codecs/request-codec';
import { CsrfCodec } from './codecs/csrf-codec';
import { CsrfConfig } from './configure';
export { BleetAlertCustomAttribute, BleetBadgeCustomAttribute, BleetBurgerCustomAttribute, BleetDrawerTriggerCustomAttribute, BleetDropdownCustomAttribute, BleetMenuCustomAttribute, BleetModalTriggerCustomAttribute, BleetPagerCustomAttribute, BleetPasswordCustomAttribute, BleetProfileCustomAttribute, BleetSelectCustomAttribute, BleetTabsCustomAttribute, BleetToasterTriggerCustomAttribute, BleetUploadCustomAttribute, BleetAjaxifyTriggerCustomAttribute, BleetPopoverCustomAttribute, BleetPopoverTriggerCustomAttribute, BleetOverlay, BleetToast, BleetToaster, BleetToasterTrigger, BleetModal, BleetDrawer, BleetAjaxify, BleetQuilljs, Channels, OverlayAction, OverlayStatus, ProfileAction, ProfileStatus, ModalAction, ModalStatus, DrawerAction, DrawerStatus, ToasterAction, AjaxifyAction, PopoverAction, PopoverStatus, ToasterStatus, MenuAction, MenuStatus, BadgeAction, UiColor, UiToastIcon, UiIcon, DialogAction, Transport, TransportConfig, ITransport, IOverlay, IOverlayStatus, IModal, IModalStatus, IDrawer, IDrawerStatus, IToaster, IToasterStatus, IToast, IMenu, IMenuStatus, IBadge, IProfile, IProfileStatus, IDialogResponse, IAjaxify, IPopover, IPopoverStatus, AjaxifyCodec, RequestCodec, CsrfCodec, CsrfConfig, IHttpService, IApiService, ISocketioService, IStorageService, ITransitionService, ITrapFocusService, ISvgService };
export declare const BleetConfiguration: {
    register(container: IContainer): IContainer;
    customize(callback: (options: ConfigInterface) => void): /*elided*/ any;
};
//# sourceMappingURL=index.d.ts.map