<?php

declare(strict_types=1);

/**
 * UiIcon.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Enums;

/**
 * UI icons - values are the keys of the ISvgService map on the TypeScript side
 * Short names matching the colors (Info, Success, Warning, Danger)
 */
enum UiIcon: string
{
    case Info = 'information-circle';
    case Success = 'check-circle';
    case Warning = 'exclamation-triangle';
    case Danger = 'x-circle';
}
