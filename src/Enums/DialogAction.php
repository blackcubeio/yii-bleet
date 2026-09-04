<?php

declare(strict_types=1);

/**
 * DialogAction.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Enums;

/**
 * Actions primaires dialog (mutuellement exclusives)
 */
enum DialogAction: string
{
    case Keep = 'keep';
    case Close = 'close';
    case RefreshAndClose = 'refreshAndClose';
}
