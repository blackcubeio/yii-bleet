<?php

declare(strict_types=1);

/**
 * Checkbox.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Form\Field\Checkbox as BaseCheckbox;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldTrait;
use Blackcube\Icons\Svg;
use Yiisoft\Html\Html;

/**
 * Checkbox styled with the Bleet colors.
 *
 * The field mechanics live in blackcube/yii-form; what follows is only the
 * styling: the classes, the shades, the sizes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Checkbox extends BaseCheckbox
{
    use BleetColorTrait;
    use BleetFieldTrait;

    protected string $template = "{input}\n<div class=\"text-sm/6\">{label}{hint}</div>\n{error}";
    protected array $containerAttributes = ['class' => 'flex gap-3'];
    protected array $inputContainerAttributes = ['class' => 'flex h-6 shrink-0 items-center'];
    /**
     * The label and the hint sit beside the box, not above: they are therefore
     * quieter than elsewhere.
     */
    public function bleetParts(): static
    {
        return $this
            ->fieldParts()
            ->labelClass('font-medium text-secondary-900')
            ->hintClass('text-secondary-500')
            ->errorClass(self::ERROR_CLASS);
    }

    /**
     * Classes for l'input checkbox
     * @return string[]
     */
    protected function getInputClasses(): array
    {
        return [
            'col-start-1',
            'row-start-1',
            'appearance-none',
            'rounded-sm',
            'border',
            'cursor-pointer',
            ...$this->getInputColorClasses(),
            'focus:outline-none',
            ...$this->focusVisibleRingClasses(),
            'focus-visible:ring-offset-2',
            'forced-colors:appearance-auto',
        ];
    }

    /**
     * Classes couleur pour l'input (toggle states)
     * @return string[]
     */
    protected function getInputColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => [
                'border-primary-300',
                'bg-white',
                'checked:border-primary-600',
                'checked:bg-primary-600',
                'indeterminate:border-primary-600',
                'indeterminate:bg-primary-600',
                'disabled:border-primary-300',
                'disabled:bg-primary-100',
                'disabled:checked:bg-primary-100',
            ],
            Bleet::COLOR_SECONDARY => [
                'border-secondary-300',
                'bg-white',
                'checked:border-secondary-600',
                'checked:bg-secondary-600',
                'indeterminate:border-secondary-600',
                'indeterminate:bg-secondary-600',
                'disabled:border-secondary-300',
                'disabled:bg-secondary-100',
                'disabled:checked:bg-secondary-100',
            ],
            Bleet::COLOR_SUCCESS => [
                'border-success-300',
                'bg-white',
                'checked:border-success-600',
                'checked:bg-success-600',
                'indeterminate:border-success-600',
                'indeterminate:bg-success-600',
                'disabled:border-success-300',
                'disabled:bg-success-100',
                'disabled:checked:bg-success-100',
            ],
            Bleet::COLOR_DANGER => [
                'border-danger-300',
                'bg-white',
                'checked:border-danger-600',
                'checked:bg-danger-600',
                'indeterminate:border-danger-600',
                'indeterminate:bg-danger-600',
                'disabled:border-danger-300',
                'disabled:bg-danger-100',
                'disabled:checked:bg-danger-100',
            ],
            Bleet::COLOR_WARNING => [
                'border-warning-300',
                'bg-white',
                'checked:border-warning-600',
                'checked:bg-warning-600',
                'indeterminate:border-warning-600',
                'indeterminate:bg-warning-600',
                'disabled:border-warning-300',
                'disabled:bg-warning-100',
                'disabled:checked:bg-warning-100',
            ],
            Bleet::COLOR_INFO => [
                'border-info-300',
                'bg-white',
                'checked:border-info-600',
                'checked:bg-info-600',
                'indeterminate:border-info-600',
                'indeterminate:bg-info-600',
                'disabled:border-info-300',
                'disabled:bg-info-100',
                'disabled:checked:bg-info-100',
            ],
            Bleet::COLOR_ACCENT => [
                'border-accent-300',
                'bg-white',
                'checked:border-accent-600',
                'checked:bg-accent-600',
                'indeterminate:border-accent-600',
                'indeterminate:bg-accent-600',
                'disabled:border-accent-300',
                'disabled:bg-accent-100',
                'disabled:checked:bg-accent-100',
            ],
        };
    }

    /**
     * Disabled stroke class for the SVG
     */
    protected function getSvgDisabledStrokeClass(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'group-has-disabled:stroke-primary-950/25',
            Bleet::COLOR_SECONDARY => 'group-has-disabled:stroke-secondary-950/25',
            Bleet::COLOR_SUCCESS => 'group-has-disabled:stroke-success-950/25',
            Bleet::COLOR_DANGER => 'group-has-disabled:stroke-danger-950/25',
            Bleet::COLOR_WARNING => 'group-has-disabled:stroke-warning-950/25',
            Bleet::COLOR_INFO => 'group-has-disabled:stroke-info-950/25',
            Bleet::COLOR_ACCENT => 'group-has-disabled:stroke-accent-950/25',
        };
    }

    /**
     * @return string[]
     */
    protected function checkmarkClasses(): array
    {
        return [
            'pointer-events-none',
            'col-start-1',
            'row-start-1',
            'size-3.5',
            'self-center',
            'justify-self-center',
            'stroke-white',
        ];
    }

    /**
     * The Bleet box: a native box, and the check drawn over it.
     */
    protected function decorateBox(string $box): string
    {
        return Html::div($box . $this->renderCheckmarkSvg(), ['class' => 'group grid size-4 grid-cols-1'])
            ->encode(false)
            ->render();
    }

    protected function renderCheckmarkSvg(): string
    {
        $classes = $this->checkmarkClasses();

        $disabled = $this->getSvgDisabledStrokeClass();
        if ($disabled !== '') {
            $classes[] = $disabled;
        }

        return Svg::icon()->ui('checkbox')->addClass(...$classes)->render();
    }
}
