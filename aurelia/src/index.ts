import { IContainer, IRegistry } from 'aurelia';
import { ConfigInterface, IBleetConfiguration } from './configure';
import { Transport, TransportConfig } from './enums/api';
import { ITransport } from './interfaces/api';
import { IHttpService } from './services/http-service';
import { ISvgService } from './services/svg-service';
import { IApiService } from './services/api-service';
import { ISocketioService } from './services/socketio-service';
import { IStorageService } from './services/storage-service';
import { ITransitionService } from './services/transition-service';
import { ITrapFocusService } from './services/trap-focus-service';

import {
    BleetAlertCustomAttribute,
    BleetBadgeCustomAttribute,
    BleetBurgerCustomAttribute,
    BleetDrawerTriggerCustomAttribute,
    BleetDropdownCustomAttribute,
    BleetMenuCustomAttribute,
    BleetModalTriggerCustomAttribute,
    BleetPagerCustomAttribute,
    BleetPasswordCustomAttribute,
    BleetProfileCustomAttribute,
    BleetSelectCustomAttribute,
    BleetTabsCustomAttribute,
    BleetToasterTriggerCustomAttribute,
    BleetUploadCustomAttribute,
    BleetAjaxifyTriggerCustomAttribute,
    BleetPopoverCustomAttribute,
    BleetPopoverTriggerCustomAttribute
} from './attributes';

import {
    BleetOverlay,
    BleetToast,
    BleetToaster,
    BleetToasterTrigger,
    BleetModal,
    BleetDrawer,
    BleetAjaxify,
    BleetQuilljs
} from './components';

import {
    Channels,
    OverlayAction,
    OverlayStatus,
    ProfileAction,
    ProfileStatus,
    ModalAction,
    ModalStatus,
    DrawerAction,
    DrawerStatus,
    ToasterAction,
    ToasterStatus,
    MenuAction,
    MenuStatus,
    BadgeAction,
    UiColor,
    UiToastIcon,
    UiIcon,
    DialogAction,
    AjaxifyAction,
    PopoverAction,
    PopoverStatus
} from './enums/event-aggregator';

import { IDialogResponse } from './interfaces/dialog';

import {
    IOverlay,
    IOverlayStatus,
    IModal,
    IModalStatus,
    IDrawer,
    IDrawerStatus,
    IToaster,
    IToasterStatus,
    IToast,
    IMenu,
    IMenuStatus,
    IBadge,
    IProfile,
    IProfileStatus,
    IAjaxify,
    IPopover,
    IPopoverStatus
} from './interfaces/event-aggregator';

import { AjaxifyCodec } from './codecs/ajaxify-codec';
import { RequestCodec } from './codecs/request-codec';
import { CsrfCodec } from './codecs/csrf-codec';
import { CsrfConfig } from './configure';

export {
    // attributes
    BleetAlertCustomAttribute,
    BleetBadgeCustomAttribute,
    BleetBurgerCustomAttribute,
    BleetDrawerTriggerCustomAttribute,
    BleetDropdownCustomAttribute,
    BleetMenuCustomAttribute,
    BleetModalTriggerCustomAttribute,
    BleetPagerCustomAttribute,
    BleetPasswordCustomAttribute,
    BleetProfileCustomAttribute,
    BleetSelectCustomAttribute,
    BleetTabsCustomAttribute,
    BleetToasterTriggerCustomAttribute,
    BleetUploadCustomAttribute,
    BleetAjaxifyTriggerCustomAttribute,
    BleetPopoverCustomAttribute,
    BleetPopoverTriggerCustomAttribute,
    // components
    BleetOverlay,
    BleetToast,
    BleetToaster,
    BleetToasterTrigger,
    BleetModal,
    BleetDrawer,
    BleetAjaxify,
    BleetQuilljs,
    // enums
    Channels,
    OverlayAction,
    OverlayStatus,
    ProfileAction,
    ProfileStatus,
    ModalAction,
    ModalStatus,
    DrawerAction,
    DrawerStatus,
    ToasterAction,
    AjaxifyAction,
    PopoverAction,
    PopoverStatus,
    ToasterStatus,
    MenuAction,
    MenuStatus,
    BadgeAction,
    UiColor,
    UiToastIcon,
    UiIcon,
    DialogAction,
    Transport,
    // types
    TransportConfig,
    // interfaces
    ITransport,
    IOverlay,
    IOverlayStatus,
    IModal,
    IModalStatus,
    IDrawer,
    IDrawerStatus,
    IToaster,
    IToasterStatus,
    IToast,
    IMenu,
    IMenuStatus,
    IBadge,
    IProfile,
    IProfileStatus,
    IDialogResponse,
    IAjaxify,
    IPopover,
    IPopoverStatus,
    AjaxifyCodec,
    RequestCodec,
    CsrfCodec,
    CsrfConfig,
    // services
    IHttpService,
    IApiService,
    ISocketioService,
    IStorageService,
    ITransitionService,
    ITrapFocusService,
    ISvgService
};
const DefaultComponents: IRegistry[] = [
    // attributes
    BleetAlertCustomAttribute as unknown as IRegistry,
    BleetBurgerCustomAttribute as unknown as IRegistry,
    BleetDrawerTriggerCustomAttribute as unknown as IRegistry,
    BleetDropdownCustomAttribute as unknown as IRegistry,
    BleetMenuCustomAttribute as unknown as IRegistry,
    BleetModalTriggerCustomAttribute as unknown as IRegistry,
    BleetPagerCustomAttribute as unknown as IRegistry,
    BleetProfileCustomAttribute as unknown as IRegistry,
    BleetPasswordCustomAttribute as unknown as IRegistry,
    BleetSelectCustomAttribute as unknown as IRegistry,
    BleetBadgeCustomAttribute as unknown as IRegistry,
    BleetTabsCustomAttribute as unknown as IRegistry,
    BleetToasterTriggerCustomAttribute as unknown as IRegistry,
    BleetUploadCustomAttribute as unknown as IRegistry,
    BleetAjaxifyTriggerCustomAttribute as unknown as IRegistry,
    BleetPopoverCustomAttribute as unknown as IRegistry,
    BleetPopoverTriggerCustomAttribute as unknown as IRegistry,
    // components
    BleetOverlay as unknown as IRegistry,
    BleetToast as unknown as IRegistry,
    BleetToaster as unknown as IRegistry,
    BleetToasterTrigger as unknown as IRegistry,
    BleetModal as unknown as IRegistry,
    BleetDrawer as unknown as IRegistry,
    BleetAjaxify as unknown as IRegistry,
    BleetQuilljs as unknown as IRegistry
];

function createBleetConfiguration(optionsCallback?: (options: ConfigInterface) => void) {
    return {
        register(container: IContainer) {
            const configClass = container.get(IBleetConfiguration);
            configClass.setContainer(container);

            if (optionsCallback) {
                const options = configClass.getConfig();
                optionsCallback(options);
            }

            // Enregistrer l'interface HTTP (toujours disponible)
            configClass.registerTransportInterface(Transport.Http, IHttpService);
            // Socketio sera résolu à la demande dans getTransport()

            return container.register(...DefaultComponents);
        },
        customize(callback: (options: ConfigInterface) => void) {
            return createBleetConfiguration(callback);
        }
    };
}

export const BleetConfiguration = createBleetConfiguration();
