<?php

declare(strict_types=1);

/**
 * toaster.php
 *
 * PHP Version 8.1
 *
 * Fixed container at top right for all toasts
 * Toasts are added dynamically by the Aurelia2 component <bleet-toaster>
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var array $containerAttributes
 */

use Blackcube\Bleet\Aurelia;
use Yiisoft\Html\Html;

echo Html::tag('bleet-toaster', '', Aurelia::attributesCustomElement($containerAttributes))->render();
