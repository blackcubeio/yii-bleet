<?php

declare(strict_types=1);

/**
 * ButtonGroup.php
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
use Yiisoft\Widget\Widget;
use Blackcube\Form\Field\ButtonGroup as BaseButtonGroup;

/**
 * The button group styled with the Bleet colors.
 *
 * The group styles its children: the ring, the corners and the overlap come
 * from here, the shade stays the one of each button.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ButtonGroup extends BaseButtonGroup
{
    use BleetColorTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    /**
     * @return string[]
     */
    protected function itemClasses(Widget $button, int $position, int $last): array
    {
        $classes = [
            'relative',
            'inline-flex',
            'items-center',
            'bg-white',
            'px-2',
            'py-2',
            'inset-ring-1',
            'cursor-pointer',
            'focus:z-10',
            $this->getRingColorClass(),
            $this->getHoverColorClass(),
            $this->getTextColorClass($button->getColor()),
        ];

        if ($position === 0) {
            $classes[] = 'rounded-l-md';
        } else {
            $classes[] = '-ml-px';
        }

        if ($position === $last) {
            $classes[] = 'rounded-r-md';
        }

        return $classes;
    }

    /**
     * The ring belongs to the group: a single shade for the whole block.
     */
    protected function getRingColorClass(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'inset-ring-primary-300',
            Bleet::COLOR_SECONDARY => 'inset-ring-secondary-300',
            Bleet::COLOR_SUCCESS => 'inset-ring-success-300',
            Bleet::COLOR_WARNING => 'inset-ring-warning-300',
            Bleet::COLOR_DANGER => 'inset-ring-danger-300',
            Bleet::COLOR_INFO => 'inset-ring-info-300',
            Bleet::COLOR_ACCENT => 'inset-ring-accent-300',
        };
    }

    protected function getHoverColorClass(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'hover:bg-primary-50',
            Bleet::COLOR_SECONDARY => 'hover:bg-secondary-50',
            Bleet::COLOR_SUCCESS => 'hover:bg-success-50',
            Bleet::COLOR_WARNING => 'hover:bg-warning-50',
            Bleet::COLOR_DANGER => 'hover:bg-danger-50',
            Bleet::COLOR_INFO => 'hover:bg-info-50',
            Bleet::COLOR_ACCENT => 'hover:bg-accent-50',
        };
    }

    /**
     * The content shade stays the one of the button.
     */
    protected function getTextColorClass(string $color): string
    {
        return match ($color) {
            Bleet::COLOR_PRIMARY => 'text-primary-600',
            Bleet::COLOR_SECONDARY => 'text-secondary-600',
            Bleet::COLOR_SUCCESS => 'text-success-600',
            Bleet::COLOR_WARNING => 'text-warning-600',
            Bleet::COLOR_DANGER => 'text-danger-600',
            Bleet::COLOR_INFO => 'text-info-600',
            Bleet::COLOR_ACCENT => 'text-accent-600',
        };
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [
            'isolate',
            'inline-flex',
            'rounded-md',
            'shadow-xs',
        ];
    }

    protected function renderButton(Widget $button, int $position, int $last): string
    {
        $classes = $this->itemClasses($button, $position, $last);

        if ($classes !== []) {
            $button = $button->class(...$classes);
        }

        return $button->render();
    }
}
