<?php

declare(strict_types=1);

/**
 * Radio.php
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
 * Radio widget - Bouton radio pour formulaires
 *
 * Usage:
 *   Bleet::radio()->name('plan')->value('small')->label('Small')->render()
 *   Bleet::radio()->name('plan')->value('medium')->label('Medium')->description('8 GB RAM...')->render()
 *   Bleet::radio()->active($model, 'plan')->value('small')->label('Small')->render() // model-bound
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Radio extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetColorTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private ?string $name = null;
    private ?string $value = null;
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
     * Selects the radio
     */
    public function checked(bool $checked = true): self
    {
        $new = clone $this;
        $new->checked = $checked;
        return $new;
    }

    /**
     * Disables the radio
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
     * Renders the radio
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
        $inputHtml = $this->renderRadioInput();

        // Label text
        $labelHtml = $this->renderLabelText();

        // Container label cliquable
        return Html::tag(
            'label',
            $inputHtml . $labelHtml,
            ['class' => 'flex items-center gap-2 cursor-pointer']
        )->encode(false)->render();
    }

    /**
     * Renders with description
     */
    private function renderWithDescription(): string
    {
        $inputHtml = $this->renderRadioInput();

        // Wrapper for vertical alignment
        $inputWrapper = Html::div(
            $inputHtml,
            ['class' => 'flex h-6 items-center']
        )->encode(false)->render();

        // Label + description
        $labelHtml = $this->renderLabelForDescription();
        $descriptionHtml = $this->renderDescription();

        $textWrapper = Html::div(
            $labelHtml . $descriptionHtml,
            ['class' => 'ml-3 text-sm/6']
        )->encode(false)->render();

        // Container flex
        return Html::div(
            $inputWrapper . $textWrapper,
            ['class' => 'relative flex items-start']
        )->encode(false)->render();
    }

    /**
     * Render the radio input
     */
    private function renderRadioInput(): string
    {
        // Value resolution : explicit > model > null
        $name = $this->name ?? $this->getInputName();
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $this->getInputId();
        $modelValue = $this->getValue();
        // For radio, checked if model value == radio value
        $checked = $this->checked || ($this->value !== null && $modelValue === $this->value);
        $required = $this->required || $this->isRequired();

        $defaults = [
            'type' => 'radio',
            'class' => implode(' ', $this->getInputClasses()),
        ];

        if ($name !== null) {
            $defaults['name'] = $name;
        }

        if ($id !== null) {
            $defaults['id'] = $id;
        }

        if ($this->value !== null) {
            $defaults['value'] = $this->value;
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

        return Html::input('radio', $name, $this->value)
            ->attributes($attributes)
            ->render();
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
            $id = $this->getTagAttributes()['id'] ?? $this->getInputId();
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
     * Classes for the radio input
     * @return string[]
     */
    private function getInputClasses(): array
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
    private function getInputColorClasses(): array
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
