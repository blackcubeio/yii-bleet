import { DialogAction, UiColor } from '../enums/event-aggregator';
import {IAjaxify, IToast} from './event-aggregator';

/**
 * Server response for load URL and submit form
 */
export interface IDialogResponse {
    color?: UiColor;
    icon?: string | null;  // UiIcon key or inline SVG

    header?: string;
    content?: string;
    footer?: string;

    action: DialogAction;

    toast?: IToast;
    ajaxify?: IAjaxify,
    redirect?: string;
    refresh?: boolean;
}
