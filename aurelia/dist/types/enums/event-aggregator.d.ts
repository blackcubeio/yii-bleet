export declare enum Channels {
    Overlay = "bleet:overlay",
    OverlayStatus = "bleet:overlay:status",
    Modal = "bleet:modal",
    ModalStatus = "bleet:modal:status",
    Drawer = "bleet:drawer",
    DrawerStatus = "bleet:drawer:status",
    Toaster = "bleet:toaster",
    ToasterStatus = "bleet:toaster:status",
    Menu = "bleet:menu",
    MenuStatus = "bleet:menu:status",
    Badge = "bleet:badge",
    Profile = "bleet:profile",
    ProfileStatus = "bleet:profile:status",
    Ajaxify = "bleet:ajaxify",
    Popover = "bleet:popover",
    PopoverStatus = "bleet:popover:status"
}
export declare enum OverlayAction {
    Open = "open",
    Close = "close",
    Toggle = "toggle"
}
export declare enum OverlayStatus {
    Opening = "opening",
    Closing = "closing",
    Opened = "opened",
    Closed = "closed"
}
export declare enum ProfileAction {
    Open = "open",
    Close = "close",
    Toggle = "toggle"
}
export declare enum ProfileStatus {
    Opening = "opening",
    Closing = "closing",
    Opened = "opened",
    Closed = "closed"
}
export declare enum ModalAction {
    Open = "open",
    Close = "close",
    Toggle = "toggle"
}
export declare enum ModalStatus {
    Opening = "opening",
    Closing = "closing",
    Opened = "opened",
    Closed = "closed"
}
export declare enum DrawerAction {
    Open = "open",
    Close = "close",
    Toggle = "toggle"
}
export declare enum DrawerStatus {
    Opening = "opening",
    Closing = "closing",
    Opened = "opened",
    Closed = "closed"
}
export declare enum AjaxifyAction {
    Refresh = "refresh"
}
export declare enum ToasterAction {
    Add = "add",
    Remove = "remove"
}
export declare enum ToasterStatus {
    Added = "added",
    Removed = "removed"
}
export declare enum MenuAction {
    Open = "open",
    Close = "close",
    Toggle = "toggle"
}
export declare enum BadgeAction {
    Remove = "remove"
}
export declare enum MenuStatus {
    Opening = "opening",
    Closing = "closing",
    Opened = "opened",
    Closed = "closed"
}
export declare enum PopoverAction {
    Open = "open",
    Close = "close",
    Toggle = "toggle"
}
export declare enum PopoverStatus {
    Opening = "opening",
    Closing = "closing",
    Opened = "opened",
    Closed = "closed"
}
export declare enum UiColor {
    Primary = "primary",
    Secondary = "secondary",
    Success = "success",
    Danger = "danger",
    Warning = "warning",
    Info = "info",
    Accent = "accent"
}
export declare enum UiToastIcon {
    Info = "information-circle",
    Success = "check-circle",
    Warning = "exclamation-triangle",
    Danger = "x-circle"
}
/**
 * Icônes pour toasts et dialogs
 * Double alias : court (Info) et long (InformationCircle) → même valeur
 */
export declare enum UiIcon {
    Info = "information-circle",
    Success = "check-circle",
    Warning = "exclamation-triangle",
    Danger = "x-circle",
    InformationCircle = "information-circle",
    CheckCircle = "check-circle",
    ExclamationTriangle = "exclamation-triangle",
    XCircle = "x-circle"
}
/**
 * Actions primaires dialog (mutuellement exclusives)
 */
export declare enum DialogAction {
    Keep = "keep",
    Close = "close",
    RefreshAndClose = "refreshAndClose"
}
//# sourceMappingURL=event-aggregator.d.ts.map