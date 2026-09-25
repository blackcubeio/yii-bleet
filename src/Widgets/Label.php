<?php

declare(strict_types=1);

/**
 * Label.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Form\Field\Label as BaseLabel;
use Yiisoft\FormModel\FormModelInterface;

/**
 * The label styled with the Bleet colors.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Label extends BaseLabel
{
    use BleetColorTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    public function color(string $color): static
    {
        $new = clone $this;
        $new->color = $color;

        return $new;
    }

    public function primary(): static
    {
        return $this->color(Bleet::COLOR_PRIMARY);
    }

    public function secondary(): static
    {
        return $this->color(Bleet::COLOR_SECONDARY);
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [
            'block',
            'text-sm/6',
            'font-medium',
            $this->textStrongColorClass(),
        ];
    }

    /**
     * @return string
     */
    protected function getRequiredClasses(): string
    {
        return $this->textMutedColorClass();
    }
}
