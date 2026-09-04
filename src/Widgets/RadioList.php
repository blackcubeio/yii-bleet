<?php

declare(strict_types=1);

/**
 * RadioList.php
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
use Blackcube\Form\Field\Radio as BaseRadio;
use Blackcube\Form\Field\RadioList as BaseRadioList;

/**
 * The radio button group styled with the Bleet colors.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class RadioList extends BaseRadioList
{
    use BleetColorTrait;
    use BleetFieldTrait;
    use BleetItemListTrait;

    protected function makeRadio(): BaseRadio
    {
        return Radio::widget()->color($this->color);
    }
}
