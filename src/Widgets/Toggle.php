<?php

declare(strict_types=1);

/**
 * Toggle.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Form\Field\Toggle as BaseToggle;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldTrait;

/**
 * Toggle styled with the Bleet colors.
 *
 * The field mechanics live in blackcube/yii-form; what follows is only the
 * styling: the classes, the shades, the sizes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Toggle extends BaseToggle
{
    use BleetColorTrait;
    use BleetFieldTrait;

    protected string $template = "{input}\n<div class=\"text-sm/6\">{label}{hint}</div>\n{error}";
    protected array $containerAttributes = ['class' => 'flex items-center gap-3'];
    /**
     * Returns switch container classes
     */
    protected function getSwitchClasses(): array
    {
        return [
            'group',
            'relative',
            'inline-flex',
            'w-11',
            'shrink-0',
            'cursor-pointer',
            'rounded-full',
            $this->getSwitchBgColor(),
            'p-0.5',
            'inset-ring',
            $this->getSwitchRingColor(),
            'ring-offset-2',
            $this->getSwitchFocusRingColor(),
            'transition-colors',
            'duration-200',
            'ease-in-out',
            $this->getSwitchCheckedBgColor(),
            'has-focus-visible:ring-2',
        ];
    }

    /**
     * Returns the knob classes
     */
    protected function getKnobClasses(): array
    {
        return [
            'pointer-events-none',
            'size-5',
            'rounded-full',
            'bg-white',
            'shadow-xs',
            'ring-1',
            $this->getKnobRingColor(),
            'transition-transform',
            'duration-200',
            'ease-in-out',
            'group-has-checked:translate-x-5',
        ];
    }

    /**
     * Returns the input classes
     */
    protected function getInputClasses(): array
    {
        return [
            'absolute',
            'inset-0',
            'cursor-pointer',
            'appearance-none',
            'focus:outline-hidden',
        ];
    }

    /**
     * Returns the switch background color (off)
     */
    protected function getSwitchBgColor(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'bg-primary-200',
            Bleet::COLOR_SECONDARY => 'bg-secondary-200',
            Bleet::COLOR_SUCCESS => 'bg-success-200',
            Bleet::COLOR_WARNING => 'bg-warning-200',
            Bleet::COLOR_DANGER => 'bg-danger-200',
            Bleet::COLOR_INFO => 'bg-info-200',
            Bleet::COLOR_ACCENT => 'bg-accent-200',
        };
    }

    /**
     * Returns the switch background color (on)
     */
    protected function getSwitchCheckedBgColor(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'has-checked:bg-primary-600',
            Bleet::COLOR_SECONDARY => 'has-checked:bg-secondary-600',
            Bleet::COLOR_SUCCESS => 'has-checked:bg-success-600',
            Bleet::COLOR_WARNING => 'has-checked:bg-warning-600',
            Bleet::COLOR_DANGER => 'has-checked:bg-danger-600',
            Bleet::COLOR_INFO => 'has-checked:bg-info-600',
            Bleet::COLOR_ACCENT => 'has-checked:bg-accent-600',
        };
    }

    /**
     * Returns the switch ring color
     */
    protected function getSwitchRingColor(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'inset-ring-primary-900/5',
            Bleet::COLOR_SECONDARY => 'inset-ring-secondary-900/5',
            Bleet::COLOR_SUCCESS => 'inset-ring-success-900/5',
            Bleet::COLOR_WARNING => 'inset-ring-warning-900/5',
            Bleet::COLOR_DANGER => 'inset-ring-danger-900/5',
            Bleet::COLOR_INFO => 'inset-ring-info-900/5',
            Bleet::COLOR_ACCENT => 'inset-ring-accent-900/5',
        };
    }

    /**
     * Returns the switch focus ring color
     */
    protected function getSwitchFocusRingColor(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'has-focus-visible:ring-primary-600',
            Bleet::COLOR_SECONDARY => 'has-focus-visible:ring-secondary-600',
            Bleet::COLOR_SUCCESS => 'has-focus-visible:ring-success-600',
            Bleet::COLOR_WARNING => 'has-focus-visible:ring-warning-600',
            Bleet::COLOR_DANGER => 'has-focus-visible:ring-danger-600',
            Bleet::COLOR_INFO => 'has-focus-visible:ring-info-600',
            Bleet::COLOR_ACCENT => 'has-focus-visible:ring-accent-600',
        };
    }

    /**
     * Returns the knob ring color
     */
    protected function getKnobRingColor(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'ring-primary-900/5',
            Bleet::COLOR_SECONDARY => 'ring-secondary-900/5',
            Bleet::COLOR_SUCCESS => 'ring-success-900/5',
            Bleet::COLOR_WARNING => 'ring-warning-900/5',
            Bleet::COLOR_DANGER => 'ring-danger-900/5',
            Bleet::COLOR_INFO => 'ring-info-900/5',
            Bleet::COLOR_ACCENT => 'ring-accent-900/5',
        };
    }

    protected function prepareClasses(): array
    {
        return [];
    }
}
