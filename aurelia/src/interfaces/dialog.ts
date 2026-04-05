import { DialogAction, UiColor } from '../enums/event-aggregator';
import {IAjaxify, IToast} from './event-aggregator';

/**
 * Réponse serveur pour load URL et submit form
 */
export interface IDialogResponse {
    // Style — optionnels avec défauts
    color?: UiColor;
    icon?: string | null;  // Clé UiIcon ou SVG inline

    // Contenu HTML à injecter
    header?: string;
    content?: string;
    footer?: string;

    // Action primaire (obligatoire)
    action: DialogAction;

    // Actions secondaires (combinables)
    toast?: IToast;
    ajaxify?: IAjaxify,
    redirect?: string;
    refresh?: boolean;
}
