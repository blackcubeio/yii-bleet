<?php

declare(strict_types=1);

/**
 * AjaxifyTriggerMode.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Enums;

/**
 * Mode du trigger ajaxify
 */
enum AjaxifyTriggerMode: string
{
    case Classic = 'classic';
    case PublishOnly = 'publishOnly';
}
