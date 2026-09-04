<?php

declare(strict_types=1);

/**
 * Radio.php
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
use Blackcube\Form\Field\Radio as BaseRadio;

/**
 * The radio button styled with the Bleet colors.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Radio extends BaseRadio
{
    use BleetColorTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    public function color(string $color): static
    {
        $new = clone $this;
        $new->color = $color;

        return $new;
    }

    /**
     * @return string[]
     */
    protected function wrapperClasses(): array
    {
        return [
            'flex',
            'items-center',
            'gap-2',
            'cursor-pointer',
        ];
    }

    protected function labelTextClasses(): string
    {
        return 'text-sm '.$this->textColorClass();
    }

    protected function requiredMarkClasses(): string
    {
        return $this->textMutedColorClass();
    }

    protected function descriptionClasses(): string
    {
        return 'text-sm '.$this->textMutedColorClass();
    }

    /**
     * Base classes (unused, required by AbstractWidget)
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    /**
     * Classes for the radio input
     * @return string[]
     */
    protected function getInputClasses(): array
    {
        return [
            'relative',
            'size-4',
            'appearance-none',
            'rounded-full',
            'border',
            'cursor-pointer',
            ...$this->getInputColorClasses(),
            ...$this->focusVisibleRingClasses(),
            'focus-visible:ring-offset-2',
            'forced-colors:appearance-auto',
            'forced-colors:before:hidden',
        ];
    }

    /**
     * Classes couleur pour l'input (toggle states with dot)
     * @return string[]
     */
    protected function getInputColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => [
                'border-primary-300',
                'bg-white',
                'before:absolute',
                'before:inset-1',
                'before:rounded-full',
                'before:bg-white',
                'not-checked:before:hidden',
                'checked:border-primary-600',
                'checked:bg-primary-600',
                'disabled:border-primary-300',
                'disabled:bg-primary-100',
                'disabled:before:bg-secondary-400',
            ],
            Bleet::COLOR_SECONDARY => [
                'border-secondary-300',
                'bg-white',
                'before:absolute',
                'before:inset-1',
                'before:rounded-full',
                'before:bg-white',
                'not-checked:before:hidden',
                'checked:border-secondary-600',
                'checked:bg-secondary-600',
                'disabled:border-secondary-300',
                'disabled:bg-secondary-100',
                'disabled:before:bg-secondary-400',
            ],
            Bleet::COLOR_SUCCESS => [
                'border-success-300',
                'bg-white',
                'before:absolute',
                'before:inset-1',
                'before:rounded-full',
                'before:bg-white',
                'not-checked:before:hidden',
                'checked:border-success-600',
                'checked:bg-success-600',
                'disabled:border-success-300',
                'disabled:bg-success-100',
                'disabled:before:bg-secondary-400',
            ],
            Bleet::COLOR_DANGER => [
                'border-danger-300',
                'bg-white',
                'before:absolute',
                'before:inset-1',
                'before:rounded-full',
                'before:bg-white',
                'not-checked:before:hidden',
                'checked:border-danger-600',
                'checked:bg-danger-600',
                'disabled:border-danger-300',
                'disabled:bg-danger-100',
                'disabled:before:bg-secondary-400',
            ],
            Bleet::COLOR_WARNING => [
                'border-warning-300',
                'bg-white',
                'before:absolute',
                'before:inset-1',
                'before:rounded-full',
                'before:bg-white',
                'not-checked:before:hidden',
                'checked:border-warning-600',
                'checked:bg-warning-600',
                'disabled:border-warning-300',
                'disabled:bg-warning-100',
                'disabled:before:bg-secondary-400',
            ],
            Bleet::COLOR_INFO => [
                'border-info-300',
                'bg-white',
                'before:absolute',
                'before:inset-1',
                'before:rounded-full',
                'before:bg-white',
                'not-checked:before:hidden',
                'checked:border-info-600',
                'checked:bg-info-600',
                'disabled:border-info-300',
                'disabled:bg-info-100',
                'disabled:before:bg-secondary-400',
            ],
            Bleet::COLOR_ACCENT => [
                'border-accent-300',
                'bg-white',
                'before:absolute',
                'before:inset-1',
                'before:rounded-full',
                'before:bg-white',
                'not-checked:before:hidden',
                'checked:border-accent-600',
                'checked:bg-accent-600',
                'disabled:border-accent-300',
                'disabled:bg-accent-100',
                'disabled:before:bg-secondary-400',
            ],
        };
    }
}
