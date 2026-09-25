<?php

declare(strict_types=1);

/**
 * BleetExportableTrait.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

/**
 * Trait for the widgets exportable as an array
 * Used for: trigger attributes, EA messages, AJAX responses
 */
trait BleetExportableTrait
{
    /**
     * Returns the widget data as an array
     * @return array<string, mixed>
     */
    abstract public function asArray(): array;
}
