<?php

declare(strict_types=1);

/**
 * Textarea.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Form\Field\Textarea as BaseTextarea;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldTrait;

/**
 * Textarea styled with the Bleet colors.
 *
 * The field mechanics live in blackcube/yii-form; what follows is only the
 * styling: the classes, the shades, the sizes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Textarea extends BaseTextarea
{
    use BleetColorTrait;
    use BleetFieldTrait;

    /**
     * Classes for textarea simple
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [
            'block',
            'w-full',
            'rounded-md',
            'bg-white',
            'px-3',
            'py-1.5',
            'text-base',
            'sm:text-sm/6',
            'outline-1',
            '-outline-offset-1',
            'focus:outline-2',
            'focus:-outline-offset-2',
            ...$this->inputColorClasses(),
        ];
    }

    /**
     * Classes for textarea en mode floating label
     * @return string[]
     */
    protected function prepareFloatingTextareaClasses(): array
    {
        return [
            'block',
            'w-full',
            'focus:outline-none',
            'sm:text-sm/6',
            ...$this->getFloatingTextareaColorClasses(),
        ];
    }

    /**
     * Classes for le wrapper floating label
     * @return string[]
     */
    protected function prepareFloatingWrapperClasses(): array
    {
        return [
            'rounded-md',
            'bg-white',
            'px-3',
            'pt-2.5',
            'pb-1.5',
            'outline-1',
            '-outline-offset-1',
            'focus-within:outline-2',
            'focus-within:-outline-offset-2',
            ...$this->getFloatingWrapperColorClasses(),
        ];
    }

    /**
     * Classes couleur pour textarea floating
     * @return string[]
     */
    protected function getFloatingTextareaColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'placeholder:text-primary-500'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'placeholder:text-secondary-500'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'placeholder:text-success-500'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'placeholder:text-danger-500'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'placeholder:text-warning-500'],
            Bleet::COLOR_INFO => ['text-info-700', 'placeholder:text-info-500'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'placeholder:text-accent-500'],
        };
    }

    /**
     * Classes couleur pour wrapper floating
     * @return string[]
     */
    protected function getFloatingWrapperColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['outline-primary-300', 'focus-within:outline-primary-600'],
            Bleet::COLOR_SECONDARY => ['outline-secondary-300', 'focus-within:outline-secondary-600'],
            Bleet::COLOR_SUCCESS => ['outline-success-300', 'focus-within:outline-success-600'],
            Bleet::COLOR_DANGER => ['outline-danger-300', 'focus-within:outline-danger-600'],
            Bleet::COLOR_WARNING => ['outline-warning-300', 'focus-within:outline-warning-600'],
            Bleet::COLOR_INFO => ['outline-info-300', 'focus-within:outline-info-600'],
            Bleet::COLOR_ACCENT => ['outline-accent-300', 'focus-within:outline-accent-600'],
        };
    }
}
