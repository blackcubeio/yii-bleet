<?php

declare(strict_types=1);

/**
 * BleetItemListTrait.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

/**
 * The styling shared by the item lists: the spacing between the items.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait BleetItemListTrait
{
    /**
     * @return string[]
     */
    protected function listClasses(): array
    {
        return ['space-y-2'];
    }
}
