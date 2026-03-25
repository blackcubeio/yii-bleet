<?php

declare(strict_types=1);

/**
 * DialogAction.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
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
