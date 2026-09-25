<?php

declare(strict_types=1);

/**
 * AjaxifyAction.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Enums;

/**
 * Action secondaire ajaxify
 */
enum AjaxifyAction: string
{
    case Refresh = 'refresh';
    case Run = 'run';
}
