import { DialogAction, UiColor } from '../enums/event-aggregator';
import { IAjaxify, IToast } from './event-aggregator';
/**
 * Réponse serveur pour load URL et submit form
 */
export interface IDialogResponse {
    color?: UiColor;
    icon?: string | null;
    header?: string;
    content?: string;
    footer?: string;
    action: DialogAction;
    toast?: IToast;
    ajaxify?: IAjaxify;
    redirect?: string;
    refresh?: boolean;
}
//# sourceMappingURL=dialog.d.ts.map