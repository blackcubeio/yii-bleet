<?php

declare(strict_types=1);

/**
 * CheckboxList.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldTrait;
use Blackcube\Bleet\Traits\BleetItemListTrait;
use Blackcube\Form\Field\Checkbox as BaseCheckbox;
use Blackcube\Form\Field\CheckboxList as BaseCheckboxList;

/**
 * The checkbox group styled with the Bleet colors.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class CheckboxList extends BaseCheckboxList
{
    use BleetColorTrait;
    use BleetFieldTrait;
    use BleetItemListTrait;

    protected function makeCheckbox(): BaseCheckbox
    {
        return Checkbox::widget()->color($this->color);
    }
}
