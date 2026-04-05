import {
    UiColor,
    DrawerAction,
    ModalAction,
    OverlayAction,
    ToasterAction,
    UiToastIcon,
    MenuAction, OverlayStatus, ToasterStatus, MenuStatus, DrawerStatus, ModalStatus, BadgeAction, ProfileAction,
    ProfileStatus, AjaxifyAction, PopoverAction, PopoverStatus
} from '../enums/event-aggregator';

export interface IOverlay {
    action: OverlayAction;
}
export interface IOverlayStatus {
    status: OverlayStatus;
}

export interface IModal {
    action: ModalAction;
    id?: string;
    url?: string;
    color?: string;
    content?: string;
}
export interface IModalStatus {
    status: ModalStatus;
    id?: string;
}

export interface IDrawer {
    action: DrawerAction;
    id?: string;
    url?: string;
    color?: string;
    content?: string;
}
export interface IDrawerStatus {
    status: DrawerStatus;
    id?: string;
}

export interface IToaster {
    action: ToasterAction;
    toast?: IToast;
}
export interface IToasterStatus {
    status: ToasterStatus;
    id?: string;
}

export interface IToast {
    id?: string;
    icon?: UiToastIcon;
    color: UiColor;
    title?: string;
    content: string;
    duration?: number;
}
export interface IMenuStatus {
    status: MenuStatus;
}
export interface IMenu {
    action: MenuAction;
}
export interface IBadge {
    action: BadgeAction;
    id?: string;
}
export interface IProfileStatus {
    status: ProfileStatus;
    id?: string;
}
export interface IProfile {
    action: ProfileAction;
    id?: string;
}
export interface IPopover {
    action: PopoverAction;
    id?: string;
    rect?: DOMRect;
}
export interface IPopoverStatus {
    status: PopoverStatus;
    id?: string;
}

export interface IAjaxify {
    action: AjaxifyAction;
    id?: string;
    url?: string;
}
export interface IAjaxifyResponse {
    element?: string;
    checked?: boolean;
    toast?: IToast;
    ajaxify?: IAjaxify;
}