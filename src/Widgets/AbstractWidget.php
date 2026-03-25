<?php

declare(strict_types=1);

/**
 * AbstractWidget.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use InvalidArgumentException;
use Yiisoft\Widget\Widget;

abstract class AbstractWidget extends Widget
{
    protected string $color = Bleet::COLOR_PRIMARY;
    protected string $size = Bleet::SIZE_MD;

    public function color(string $color): static
    {
        if (!in_array($color, Bleet::COLORS, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid color "%s". Valid: %s', $color, implode(', ', Bleet::COLORS))
            );
        }

        $new = clone $this;
        $new->color = $color;
        return $new;
    }

    public function size(string $size): static
    {
        if (!in_array($size, Bleet::SIZES, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid size "%s". Valid: %s', $size, implode(', ', Bleet::SIZES))
            );
        }

        $new = clone $this;
        $new->size = $size;
        return $new;
    }

    // Color shortcuts
    public function primary(): static
    {
        return $this->color(Bleet::COLOR_PRIMARY);
    }

    public function secondary(): static
    {
        return $this->color(Bleet::COLOR_SECONDARY);
    }

    public function success(): static
    {
        return $this->color(Bleet::COLOR_SUCCESS);
    }

    public function danger(): static
    {
        return $this->color(Bleet::COLOR_DANGER);
    }

    public function warning(): static
    {
        return $this->color(Bleet::COLOR_WARNING);
    }

    public function info(): static
    {
        return $this->color(Bleet::COLOR_INFO);
    }

    public function accent(): static
    {
        return $this->color(Bleet::COLOR_ACCENT);
    }

    // Size shortcuts
    public function xs(): static
    {
        return $this->size(Bleet::SIZE_XS);
    }

    public function sm(): static
    {
        return $this->size(Bleet::SIZE_SM);
    }

    public function md(): static
    {
        return $this->size(Bleet::SIZE_MD);
    }

    public function lg(): static
    {
        return $this->size(Bleet::SIZE_LG);
    }

    public function xl(): static
    {
        return $this->size(Bleet::SIZE_XL);
    }

    /**
     * Each component prepares its own classes
     * @return string[]
     */
    abstract protected function prepareClasses(): array;
}