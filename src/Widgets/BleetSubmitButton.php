<?php

declare(strict_types=1);

/**
 * BleetSubmitButton.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

/**
 * Button of type submit, styled by Bleet.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class BleetSubmitButton extends AbstractBleetButton
{
    protected function getType(): string
    {
        return 'submit';
    }
}
