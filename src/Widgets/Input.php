<?php

declare(strict_types=1);

/**
 * Input.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Icons\Svg;
use Blackcube\Form\Field\Input as BaseInput;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldTrait;
use Yiisoft\Html\Html;

/**
 * Input styled with the Bleet colors.
 *
 * The field mechanics live in blackcube/yii-form; what follows is only the
 * styling: the classes, the shades, the sizes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Input extends BaseInput
{
    use BleetColorTrait;
    use BleetFieldTrait;

    protected string|Svg|null $icon = null;
    protected string|Svg|null $iconLeft = null;
    protected bool $showable = false;

    protected function generateInput(): string
    {
        if ($this->hasFieldError() === true) {
            $input = $this->renderWithError();
        } elseif ($this->floatingLabel !== null) {
            $input = $this->renderFloatingLabel();
        } elseif ($this->showable === true) {
            $input = $this->renderShowablePassword();
        } elseif ($this->icon !== null || $this->iconLeft !== null) {
            $input = $this->renderWithIcon();
        } else {
            $input = $this->renderInput($this->prepareClasses());
        }

        return $input;
    }

    protected function renderFloatingControl(): string
    {
        $classes = $this->prepareFloatingInputClasses();

        if ($this->icon !== null || $this->iconLeft !== null) {
            $control = $this->renderFloatingInputWithIcon($classes);
        } else {
            $control = $this->renderInput($classes);
        }

        return $control;
    }

    /**
     * Sets a trailing icon (right)
     */
    public function icon(string|Svg $icon): self
    {
        $new = clone $this;
        $new->icon = $icon;
        return $new;
    }

    /**
     * Sets a leading icon (left)
     */
    public function iconLeft(string|Svg $icon): self
    {
        $new = clone $this;
        $new->iconLeft = $icon;
        return $new;
    }

    /**
     * Enables the show/hide toggle for password fields
     */
    public function showable(bool $showable = true): self
    {
        $new = clone $this;
        $new->showable = $showable;
        return $new;
    }

    /**
     * @return string[]
     */
    protected function overlayWrapperClasses(): array
    {
        return [
            'grid',
            'grid-cols-1',
        ];
    }

    /**
     * @return string[]
     */
    protected function overlayCellClasses(): array
    {
        return [
            'col-start-1',
            'row-start-1',
        ];
    }

    /**
     * @return string[]
     */
    protected function errorIconClasses(): array
    {
        return [
            'pointer-events-none',
            'col-start-1',
            'row-start-1',
            'mr-3',
            'size-5',
            'self-center',
            'justify-self-end',
            'text-danger-500',
            'sm:size-4',
        ];
    }

    /**
     * @return string[]
     */
    protected function errorWrapperClasses(): array
    {
        return $this->overlayWrapperClasses();
    }

    private function renderWithError(): string
    {
        $inputHtml = $this->renderInput($this->fieldClasses($this->prepareErrorClasses()));

        $icon = Svg::heroicon()->mini('exclamation-circle')
            ->addClass(...$this->errorIconClasses())
            ->render();

        return Html::div($inputHtml . $icon, ['class' => implode(' ', $this->errorWrapperClasses())])
            ->encode(false)
            ->render();
    }

    /**
     * Classes de l'input en erreur.
     *
     * @return string[]
     */
    protected function prepareErrorClasses(): array
    {
        return [
            'col-start-1',
            'row-start-1',
            'block',
            'w-full',
            'rounded-md',
            'bg-white',
            'py-1.5',
            'pr-10',
            'pl-3',
            'text-base',
            'text-danger-900',
            'outline-1',
            '-outline-offset-1',
            'outline-danger-300',
            'placeholder:text-danger-300',
            'focus:outline-2',
            'focus:-outline-offset-2',
            'focus:outline-danger-600',
            'sm:pr-9',
            'sm:text-sm/6',
        ];
    }

    /**
     * Render password avec toggle show/hide
     */
    private function renderShowablePassword(): string
    {
        $hasLeftIcon = $this->iconLeft !== null;

        $inputClasses = $this->prepareIconInputClasses($hasLeftIcon, true);
        $inputHtml = $this->renderInput($inputClasses);

        $content = $inputHtml;

        if ($hasLeftIcon) {
            $content .= $this->renderIconSvg($this->iconLeft, 'left');
        }

        $content .= $this->renderPasswordToggleButton();

        $wrapperAttributes = $this->prepareWrapperAttributes();
        Html::addCssClass($wrapperAttributes, $this->overlayWrapperClasses());
        $wrapperAttributes['bleet-password'] = '';

        return Html::div($content, $wrapperAttributes)->encode(false)->render();
    }

    /**
     * @return string[]
     */
    protected function passwordIconClasses(): array
    {
        return [
            'size-5',
            'sm:size-4',
            $this->textMutedColorClass(),
            $this->getGroupHoverIconColorClass(),
        ];
    }

    /**
     * @return string[]
     */
    protected function hiddenClasses(): array
    {
        return ['hidden'];
    }

    /**
     * @return string[]
     */
    protected function passwordToggleClasses(): array
    {
        return [
            'group',
            'col-start-1',
            'row-start-1',
            'self-center',
            'justify-self-end',
            'mr-3',
            'cursor-pointer',
            'focus:outline-none',
        ];
    }

    private function renderPasswordToggleButton(): string
    {
        $iconClasses = $this->passwordIconClasses();

        $eyeIcon = Svg::heroicon()->solid('eye')->addClass(...$iconClasses);

        $eyeSlashIcon = Svg::heroicon()->solid('eye-slash')->addClass(...$iconClasses);

        $buttonClasses = $this->passwordToggleClasses();

        return Html::button(
            Html::span($eyeIcon->render(), ['data-password' => 'icon-hidden'])->encode(false).
            Html::span($eyeSlashIcon->render(), ['data-password' => 'icon-visible', 'class' => implode(' ', $this->hiddenClasses())])->encode(false),
            [
                'type' => 'button',
                'class' => implode(' ', $buttonClasses),
                'data-password' => 'toggle',
            ]
        )->encode(false)->render();
    }

    /**
     * Renders with icon(s) - grid structure
     */
    private function renderWithIcon(): string
    {
        $hasLeftIcon = $this->iconLeft !== null;
        $hasRightIcon = $this->icon !== null;

        $inputClasses = $this->prepareIconInputClasses($hasLeftIcon, $hasRightIcon);
        $inputHtml = $this->renderInput($inputClasses);

        $content = $inputHtml;

        if ($hasLeftIcon) {
            $content .= $this->renderIconSvg($this->iconLeft, 'left');
        }

        if ($hasRightIcon) {
            $content .= $this->renderIconSvg($this->icon, 'right');
        }

        $wrapperAttributes = $this->prepareWrapperAttributes();
        Html::addCssClass($wrapperAttributes, $this->overlayWrapperClasses());

        return Html::div($content, $wrapperAttributes)->encode(false)->render();
    }

    /**
     * Render input with icon in floating label context
     */
    private function renderFloatingInputWithIcon(array $inputClasses): string
    {
        $hasLeftIcon = $this->iconLeft !== null;
        $hasRightIcon = $this->icon !== null;

        $inputClasses = $this->addIconPadding($inputClasses, $hasLeftIcon, $hasRightIcon);
        $inputClasses = [...$inputClasses, ...$this->overlayCellClasses()];

        $inputHtml = $this->renderInput($inputClasses);

        $content = $inputHtml;

        if ($hasLeftIcon) {
            $content .= $this->renderIconSvg($this->iconLeft, 'left');
        }

        if ($hasRightIcon) {
            $content .= $this->renderIconSvg($this->icon, 'right');
        }

        return Html::div($content, ['class' => implode(' ', $this->overlayWrapperClasses())])
            ->encode(false)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function iconClasses(string $position): array
    {
        $classes = [
            'pointer-events-none',
            'col-start-1',
            'row-start-1',
            'size-5',
            'sm:size-4',
            'self-center',
        ];

        if ($position === 'left') {
            $classes[] = 'justify-self-start';
            $classes[] = 'ml-3';
        } else {
            $classes[] = 'justify-self-end';
            $classes[] = 'mr-3';
        }

        $classes[] = $this->textMutedColorClass();

        return $classes;
    }

    private function renderIconSvg(string|Svg $icon, string $position): string
    {
        if (is_string($icon) === true) {
            $svg = Svg::heroicon()->solid($icon);
        } else {
            $svg = $icon;
        }

        return $svg->addClass(...$this->iconClasses($position))->render();
    }

    /**
     * Classes for input with icon(s)
     * @return string[]
     */
    protected function prepareIconInputClasses(bool $hasLeftIcon, bool $hasRightIcon): array
    {
        $classes = [
            'col-start-1',
            'row-start-1',
            'block',
            'w-full',
            'rounded-md',
            'bg-white',
            'py-1.5',
            'text-base',
            'sm:text-sm/6',
            'outline-1',
            '-outline-offset-1',
            'focus:outline-2',
            'focus:-outline-offset-2',
            ...$this->inputColorClasses(),
        ];

        return $this->addIconPadding($classes, $hasLeftIcon, $hasRightIcon);
    }

    /**
     * Adds padding classes based on icons
     * @return string[]
     */
    /**
     * The field leaves room for the icons: the styling decides the
     * l'espacement.
     *
     * @param string[] $classes
     * @return string[]
     */
    protected function addIconPadding(array $classes, bool $hasLeftIcon, bool $hasRightIcon): array
    {
        if ($hasLeftIcon === true && $hasRightIcon === true) {
            $classes[] = 'pl-10';
            $classes[] = 'sm:pl-9';
            $classes[] = 'pr-10';
            $classes[] = 'sm:pr-9';
        } elseif ($hasLeftIcon === true) {
            $classes[] = 'pr-3';
            $classes[] = 'pl-10';
            $classes[] = 'sm:pl-9';
        } elseif ($hasRightIcon === true) {
            $classes[] = 'pr-10';
            $classes[] = 'pl-3';
            $classes[] = 'sm:pr-9';
        }

        return $classes;
    }

    /**
     * Color class for the placeholder
     */
    protected function getPlaceholderColorClass(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'placeholder:text-primary-500',
            Bleet::COLOR_SECONDARY => 'placeholder:text-secondary-500',
            Bleet::COLOR_SUCCESS => 'placeholder:text-success-500',
            Bleet::COLOR_DANGER => 'placeholder:text-danger-500',
            Bleet::COLOR_WARNING => 'placeholder:text-warning-500',
            Bleet::COLOR_INFO => 'placeholder:text-info-500',
            Bleet::COLOR_ACCENT => 'placeholder:text-accent-500',
        };
    }

    /**
     * Color class group-hover for icons (specific to password toggle)
     */
    protected function getGroupHoverIconColorClass(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'group-hover:text-primary-600',
            Bleet::COLOR_SECONDARY => 'group-hover:text-secondary-600',
            Bleet::COLOR_SUCCESS => 'group-hover:text-success-600',
            Bleet::COLOR_DANGER => 'group-hover:text-danger-600',
            Bleet::COLOR_WARNING => 'group-hover:text-warning-600',
            Bleet::COLOR_INFO => 'group-hover:text-info-600',
            Bleet::COLOR_ACCENT => 'group-hover:text-accent-600',
        };
    }

    /**
     * Classes for input simple
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
     * Classes for input en mode floating label
     * @return string[]
     */
    protected function prepareFloatingInputClasses(): array
    {
        return [
            'block',
            'w-full',
            'focus:outline-none',
            'sm:text-sm/6',
            $this->textColorClass(),
            $this->getPlaceholderColorClass(),
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
            $this->getFloatingOutlineColorClass(),
            $this->getFloatingFocusWithinOutlineColorClass(),
        ];
    }

    /**
     * Classe couleur outline pour wrapper floating (bordure statique)
     */
    protected function getFloatingOutlineColorClass(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'outline-primary-300',
            Bleet::COLOR_SECONDARY => 'outline-secondary-300',
            Bleet::COLOR_SUCCESS => 'outline-success-300',
            Bleet::COLOR_DANGER => 'outline-danger-300',
            Bleet::COLOR_WARNING => 'outline-warning-300',
            Bleet::COLOR_INFO => 'outline-info-300',
            Bleet::COLOR_ACCENT => 'outline-accent-300',
        };
    }

    /**
     * focus-within outline color class for the floating wrapper (border on focus)
     */
    protected function getFloatingFocusWithinOutlineColorClass(): string
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => 'focus-within:outline-primary-600',
            Bleet::COLOR_SECONDARY => 'focus-within:outline-secondary-600',
            Bleet::COLOR_SUCCESS => 'focus-within:outline-success-600',
            Bleet::COLOR_DANGER => 'focus-within:outline-danger-600',
            Bleet::COLOR_WARNING => 'focus-within:outline-warning-600',
            Bleet::COLOR_INFO => 'focus-within:outline-info-600',
            Bleet::COLOR_ACCENT => 'focus-within:outline-accent-600',
        };
    }

    /**
     * @return string[]
     */
    protected function floatingLabelClasses(): array
    {
        return ['!text-xs'];
    }
}
