export enum Channels {
    Overlay = 'bleet:overlay',
    OverlayStatus = 'bleet:overlay:status',
    Modal = 'bleet:modal',
    ModalStatus = 'bleet:modal:status',
    Drawer = 'bleet:drawer',
    DrawerStatus = 'bleet:drawer:status',
    Toaster = 'bleet:toaster',
    ToasterStatus = 'bleet:toaster:status',
    Menu = 'bleet:menu',
    MenuStatus = 'bleet:menu:status',
    Badge = 'bleet:badge',
    Profile = 'bleet:profile',
    ProfileStatus = 'bleet:profile:status',
    Ajaxify = 'bleet:ajaxify',
    Popover = 'bleet:popover',
    PopoverStatus = 'bleet:popover:status',
}
export enum OverlayAction {
    Open = 'open',
    Close = 'close',
    Toggle = 'toggle',
}
export enum OverlayStatus {
    Opening = 'opening',
    Closing = 'closing',
    Opened = 'opened',
    Closed = 'closed',
}
export enum ProfileAction {
    Open = 'open',
    Close = 'close',
    Toggle = 'toggle',
}
export enum ProfileStatus {
    Opening = 'opening',
    Closing = 'closing',
    Opened = 'opened',
    Closed = 'closed',
}
export enum ModalAction {
    Open = 'open',
    Close = 'close',
    Toggle = 'toggle',
}
export enum ModalStatus {
    Opening = 'opening',
    Closing = 'closing',
    Opened = 'opened',
    Closed = 'closed',
}

export enum DrawerAction {
    Open = 'open',
    Close = 'close',
    Toggle = 'toggle',
}
export enum DrawerStatus {
    Opening = 'opening',
    Closing = 'closing',
    Opened = 'opened',
    Closed = 'closed',
}

export enum AjaxifyAction {
    Refresh = 'refresh',
}
export enum ToasterAction {
    Add = 'add',
    Remove = 'remove',
}
export enum ToasterStatus {
    Added = 'added',
    Removed = 'removed',
}

export enum MenuAction {
    Open = 'open',
    Close = 'close',
    Toggle = 'toggle',
}
export enum BadgeAction {
    Remove = 'remove',
}
export enum MenuStatus {
    Opening = 'opening',
    Closing = 'closing',
    Opened = 'opened',
    Closed = 'closed',
}

export enum PopoverAction {
    Open = 'open',
    Close = 'close',
    Toggle = 'toggle',
}
export enum PopoverStatus {
    Opening = 'opening',
    Closing = 'closing',
    Opened = 'opened',
    Closed = 'closed',
}

export enum UiColor {
    Primary = 'primary',
    Secondary = 'secondary',
    Success = 'success',
    Danger = 'danger',
    Warning = 'warning',
    Info = 'info',
    Accent = 'accent',
}
export enum UiToastIcon {
    Info = 'information-circle',
    Success = 'check-circle',
    Warning = 'exclamation-triangle',
    Danger = 'x-circle',
}

/**
 * Icônes pour toasts et dialogs
 * Double alias : court (Info) et long (InformationCircle) → même valeur
 */
export enum UiIcon {
    // Alias courts (usage dev)
    Info = 'information-circle',
    Success = 'check-circle',
    Warning = 'exclamation-triangle',
    Danger = 'x-circle',

    // Alias longs (match heroicon)
    InformationCircle = 'information-circle',
    CheckCircle = 'check-circle',
    ExclamationTriangle = 'exclamation-triangle',
    XCircle = 'x-circle',
}

/**
 * Actions primaires dialog (mutuellement exclusives)
 */
export enum DialogAction {
    Keep = 'keep',
    Close = 'close',
    RefreshAndClose = 'refreshAndClose',
}