<?php

declare(strict_types=1);

/**
 * Checkbox.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldDataTrait;
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Yiisoft\Html\Html;

/**
 * Checkbox widget - Checkbox for forms
 *
 * Usage:
 *   Bleet::checkbox()->name('accept')->label('J\'accepte')->render()
 *   Bleet::checkbox()->name('comments')->label('Comments')->description('Get notified...')->render()
 *   Bleet::checkbox()->active($model, 'acceptTerms')->render() // model-bound
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Checkbox extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetColorTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private ?string $name = null;
    private ?string $value = null;
    private ?string $uncheckValue = null;
    private bool $checked = false;
    private bool $disabled = false;
    private bool $required = false;
    private string|Label|null $label = null;
    private ?string $description = null;

    /**
     * Sets the name
     */
    public function name(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Sets the value
     */
    public function value(string $value): self
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }

    /**
     * Sets the value that will be submitted when the checkbox is NOT checked.
     * A hidden input with this value will be rendered before the checkbox.
     */
    public function uncheckValue(string $value): self
    {
        $new = clone $this;
        $new->uncheckValue = $value;
        return $new;
    }

    /**
     * Coche la checkbox
     */
    public function checked(bool $checked = true): self
    {
        $new = clone $this;
        $new->checked = $checked;
        return $new;
    }

    /**
     * Disables la checkbox
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    /**
     * Marks comme requis
     */
    public function required(bool $required = true): self
    {
        $new = clone $this;
        $new->required = $required;
        return $new;
    }

    /**
     * Sets the label
     */
    public function label(string|Label $label): self
    {
        $new = clone $this;
        $new->label = $label;
        return $new;
    }

    /**
     * Sets the description (enables description mode)
     */
    public function description(string $description): self
    {
        $new = clone $this;
        $new->description = $description;
        return $new;
    }

    /**
     * Renders the checkbox
     */
    public function render(): string
    {
        if ($this->description !== null) {
            return $this->renderWithDescription();
        }

        return $this->renderSimple();
    }

    /**
     * Render simple : label inline
     */
    private function renderSimple(): string
    {
        $hiddenHtml = $this->renderHiddenInput();
        $checkboxHtml = $this->renderCheckboxInput();
        $svgHtml = $this->renderCheckmarkSvg();

        // Grid container for checkbox + svg (PAS le hidden)
        $gridHtml = Html::div(
            $checkboxHtml . $svgHtml,
            ['class' => 'group grid size-4 grid-cols-1']
        )->encode(false)->render();

        // Wrapper for vertical alignment
        $inputWrapper = Html::div(
            $gridHtml,
            ['class' => 'flex h-6 shrink-0 items-center']
        )->encode(false)->render();

        // Label text
        $labelHtml = $this->renderLabelText();

        // Container label cliquable (hidden input AVANT le label)
        return $hiddenHtml . Html::tag(
            'label',
            $inputWrapper . $labelHtml,
            ['class' => 'flex items-start gap-2 cursor-pointer']
        )->encode(false)->render();
    }

    /**
     * Renders with description
     */
    private function renderWithDescription(): string
    {
        $hiddenHtml = $this->renderHiddenInput();
        $checkboxHtml = $this->renderCheckboxInput();
        $svgHtml = $this->renderCheckmarkSvg();

        // Grid container for checkbox + svg (PAS le hidden)
        $gridHtml = Html::div(
            $checkboxHtml . $svgHtml,
            ['class' => 'group grid size-4 grid-cols-1']
        )->encode(false)->render();

        // Wrapper for vertical alignment
        $inputWrapper = Html::div(
            $gridHtml,
            ['class' => 'flex h-6 shrink-0 items-center']
        )->encode(false)->render();

        // Label + description
        $labelHtml = $this->renderLabelForDescription();
        $descriptionHtml = $this->renderDescription();

        $textWrapper = Html::div(
            $labelHtml . $descriptionHtml,
            ['class' => 'text-sm/6']
        )->encode(false)->render();

        // Container flex (hidden input AVANT le container)
        return $hiddenHtml . Html::div(
            $inputWrapper . $textWrapper,
            ['class' => 'flex gap-3']
        )->encode(false)->render();
    }

    /**
     * Render le hidden input pour uncheckValue (hors du grid)
     */
    private function renderHiddenInput(): string
    {
        if ($this->uncheckValue === null) {
            return '';
        }

        $name = $this->name ?? $this->getInputName();
        if ($name === null) {
            return '';
        }

        $hiddenName = Html::getNonArrayableName($name);
        $hiddenAttributes = ['value' => $this->uncheckValue];
        if ($this->disabled) {
            $hiddenAttributes['disabled'] = true;
        }

        return Html::hiddenInput($hiddenName, $this->uncheckValue)
            ->addAttributes($hiddenAttributes)
            ->render();
    }

    /**
     * Render l'input checkbox (sans le hidden)
     */
    private function renderCheckboxInput(): string
    {
        // Value resolution : explicit > '1' for model-bound > null
        $name = $this->name ?? $this->getInputName();
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $this->getInputId();
        $modelValue = $this->getValue();
        $checked = $this->checked || (bool) $modelValue;
        $required = $this->required || $this->isRequired();

        // When model-bound, default to '1' for BooleanValue compatibility
        $value = $this->value;
        if ($value === null && $this->hasModel()) {
            $value = '1';
        }

        $defaults = [
            'type' => 'checkbox',
            'class' => implode(' ', $this->getInputClasses()),
        ];

        if ($name !== null) {
            $defaults['name'] = $name;
        }

        if ($id !== null) {
            $defaults['id'] = $id;
        }

        if ($value !== null) {
            $defaults['value'] = $value;
        }

        if ($checked) {
            $defaults['checked'] = true;
        }

        if ($this->disabled) {
            $defaults['disabled'] = true;
        }

        if ($required) {
            $defaults['required'] = true;
        }

        // aria-describedby if description
        if ($this->description !== null && $id !== null) {
            $defaults['aria-describedby'] = $id . '-description';
        }

        // User attributes override defaults
        $attributes = $this->prepareTagAttributes($defaults);

        // Field data-* attributes
        $attributes = [...$attributes, ...$this->getFieldDataAttributes()];

        return Html::input('checkbox', $name, $value)
            ->attributes($attributes)
            ->render();
    }

    /**
     * Renders the SVG checkmark
     */
    private function renderCheckmarkSvg(): string
    {
        $disabledStrokeClass = $this->getSvgDisabledStrokeClass();

        $classes = [
            'pointer-events-none',
            'col-start-1',
            'row-start-1',
            'size-3.5',
            'self-center',
            'justify-self-center',
            'stroke-white',
        ];
        if ($disabledStrokeClass !== '') {
            $classes[] = $disabledStrokeClass;
        }

        return Bleet::icon()->ui('checkbox')->addClass(...$classes)->render();
    }

    /**
     * Renders the label text (mode simple)
     */
    private function renderLabelText(): string
    {
        // Label resolution: explicit > model > empty
        $labelContent = null;
        if ($this->label instanceof Label) {
            $labelContent = $this->label->getContent();
        } elseif ($this->label !== null) {
            $labelContent = $this->label;
        } else {
            $labelContent = $this->getLabel();
        }

        if ($labelContent === null || $labelContent === '') {
            return '';
        }

        // Add required marker if needed
        $required = $this->required || $this->isRequired();
        $innerHtml = Html::encode($labelContent);
        if ($required) {
            $innerHtml .= Html::tag('span', ' *', ['class' => $this->textMutedColorClass()])->encode(false);
        }

        return Html::span($innerHtml, [
            'class' => 'text-sm ' . $this->textColorClass(),
        ])->encode(false)->render();
    }

    /**
     * Renders the label (mode avec description)
     */
    private function renderLabelForDescription(): string
    {
        // Label resolution: explicit > model > empty
        $labelContent = null;
        if ($this->label instanceof Label) {
            $labelContent = $this->label->getContent();
        } elseif ($this->label !== null) {
            $labelContent = $this->label;
        } else {
            $labelContent = $this->getLabel();
        }

        if ($labelContent === null || $labelContent === '') {
            return '';
        }

        // Create label widget with active() if model-bound
        $labelWidget = Bleet::label($labelContent)->color($this->color);
        if ($this->hasModel()) {
            $labelWidget = $labelWidget->active($this->getModel(), $this->getProperty());
        } else {
            // If no model, manually bind to id and required
            $tagAttrs = $this->getTagAttributes();
            $id = $tagAttrs['id'] ?? $this->getInputId();
            if ($id !== null) {
                $labelWidget = $labelWidget->for($id);
            }
            if ($this->required) {
                $labelWidget = $labelWidget->required();
            }
        }

        return $labelWidget->render();
    }

    /**
     * Renders the description
     */
    private function renderDescription(): string
    {
        // Description resolution: explicit > model hint > empty
        $description = $this->description ?? $this->getHint();

        if ($description === null || $description === '') {
            return '';
        }

        // Id resolution: explicit > model > null
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $this->getInputId();

        $attributes = [
            'class' => $this->textMutedColorClass(),
        ];

        if ($id !== null) {
            $attributes['id'] = $id . '-description';
        }

        return Html::p($description, $attributes)->render();
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
     * Classes for l'input checkbox
     * @return string[]
     */
    private function getInputClasses(): array
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
    private function getInputColorClasses(): array
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
     * Classe stroke disabled pour le SVG
     */
    private function getSvgDisabledStrokeClass(): string
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
}
