<?php

declare(strict_types=1);

/**
 * UiIcon.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Enums;

/**
 * Icones UI - valeurs = cles du map ISvgService cote TypeScript
 * Noms courts correspondant aux couleurs (Info, Success, Warning, Danger)
 */
enum UiIcon: string
{
    case Info = 'information-circle';
    case Success = 'check-circle';
    case Warning = 'exclamation-triangle';
    case Danger = 'x-circle';
}
