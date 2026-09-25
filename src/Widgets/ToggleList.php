<?php

declare(strict_types=1);

/**
 * ToggleList.php
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
use Blackcube\Form\Field\Toggle as BaseToggle;
use Blackcube\Form\Field\ToggleList as BaseToggleList;

/**
 * The toggle group styled with the Bleet colors: one row per item, the label
 * on the left and the switch on the right, rows separated by a hairline, as
 * the permissions drawer of dboard lays them out.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ToggleList extends BaseToggleList
{
    use BleetColorTrait;
    use BleetFieldTrait;

    protected function makeToggle(): BaseToggle
    {
        return Toggle::widget()
            ->color($this->color)
            ->template("{label}\n{input}\n{error}")
            ->containerAttributes([
                'class' => 'px-4 py-2 flex items-center justify-between bg-white hover:bg-secondary-25',
            ])
            ->labelAttributes([
                'class' => 'text-sm text-secondary-700',
            ]);
    }

    /**
     * @return string[]
     */
    protected function listClasses(): array
    {
        return [
            'divide-y',
            'divide-secondary-100',
        ];
    }
}
