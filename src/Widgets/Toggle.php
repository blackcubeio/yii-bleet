<?php

declare(strict_types=1);

/**
 * Toggle.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Blackcube\Bleet\Traits\BleetFieldDataTrait;
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Blackcube\Bleet\Traits\BleetWrapperAttributesTrait;
use Yiisoft\Html\Html;

/**
 * Toggle widget - Interrupteur on/off pour formulaires
 *
 * Usage:
 *   Bleet::toggle()->name('setting')->render()
 *   Bleet::toggle()->name('notifications')->checked()->render()
 *   Bleet::toggle()->name('notifications')->label('Enable notifications')->checked()->render()
 *   Bleet::toggle()->name('terms')->label(Bleet::label('Activer')->required())->render()
 *   Bleet::toggle()->active($model, 'notifications')->render() // model-bound
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Toggle extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;
    use BleetWrapperAttributesTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private ?string $name = null;
    private ?string $value = null;
    private bool $checked = false;
    private bool $disabled = false;
    private string|Label|null $label = null;
    private ?string $ariaLabel = null;

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
     * Enables le toggle
     */
    public function checked(bool $checked = true): self
    {
        $new = clone $this;
        $new->checked = $checked;
        return $new;
    }

    /**
     * Disables le toggle
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    /**
     * Sets the label (string ou Label widget)
     */
    public function label(string|Label $label): self
    {
        $new = clone $this;
        $new->label = $label;
        return $new;
    }

    /**
     * Sets aria-label for mode without visible label
     */
    public function ariaLabel(string $ariaLabel): self
    {
        $new = clone $this;
        $new->ariaLabel = $ariaLabel;
        return $new;
    }

    /**
     * Returns switch container classes
     */
    private function getSwitchClasses(): array
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
     * Returns les classes du knob
     */
    private function getKnobClasses(): array
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
     * Returns les classes de l'input
     */
    private function getInputClasses(): array
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
     * Returns la couleur de fond du switch (off)
     */
    private function getSwitchBgColor(): string
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
     * Returns la couleur de fond du switch (on)
     */
    private function getSwitchCheckedBgColor(): string
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
     * Returns la couleur du ring du switch
     */
    private function getSwitchRingColor(): string
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
     * Returns la couleur du ring de focus du switch
     */
    private function getSwitchFocusRingColor(): string
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
     * Returns la couleur du ring du knob
     */
    private function getKnobRingColor(): string
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

    /**
     * Rendu du switch (toggle graphique)
     */
    private function renderSwitch(): string
    {
        $switchClasses = implode(' ', $this->getSwitchClasses());
        $knobClasses = implode(' ', $this->getKnobClasses());
        $inputClasses = implode(' ', $this->getInputClasses());

        // Value resolution : explicit > model > null
        $name = $this->name ?? $this->getInputName();
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $this->getInputId();
        $value = $this->value ?? '1';
        $modelValue = $this->getValue();
        $checked = $this->checked || (bool) $modelValue;

        // Attributs de l'input
        $inputDefaults = [
            'type' => 'checkbox',
            'class' => $inputClasses,
        ];

        if ($name !== null) {
            $inputDefaults['name'] = $name;
        }

        if ($id !== null) {
            $inputDefaults['id'] = $id;
        }

        $inputDefaults['value'] = $value;

        if ($checked) {
            $inputDefaults['checked'] = true;
        }

        if ($this->disabled) {
            $inputDefaults['disabled'] = true;
        }

        // aria-label if no visible label
        if ($this->label === null && $this->ariaLabel !== null) {
            $inputDefaults['aria-label'] = $this->ariaLabel;
        } elseif ($this->label === null && $name !== null) {
            // Fallback: utiliser the name comme aria-label
            $inputDefaults['aria-label'] = ucfirst(str_replace(['_', '-'], ' ', $name));
        }

        // User attributes override defaults
        $inputAttributes = $this->prepareTagAttributes($inputDefaults);

        // Field data-* attributes
        $inputAttributes = [...$inputAttributes, ...$this->getFieldDataAttributes()];

        $input = Html::input('checkbox')
            ->attributes($inputAttributes)
            ->render();

        // Hidden input for unchecked value (must come BEFORE checkbox)
        $hiddenInput = '';

        // Wrapper attributes
        $wrapperAttributes = $this->prepareWrapperAttributes();
        Html::addCssClass($wrapperAttributes, explode(' ', $switchClasses));

        return Html::div(
            $hiddenInput . '<span class="' . $knobClasses . '"></span>' . $input,
            $wrapperAttributes
        )->encode(false)->render();
    }

    /**
     * Rendu of the label
     */
    private function renderLabel(): string
    {
        // Label resolution: explicit > model > null
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
            // If no model, manually bind to id
            $tagAttrs = $this->getTagAttributes();
            $id = $tagAttrs['id'] ?? $this->getInputId();
            if ($id !== null) {
                $labelWidget = $labelWidget->for($id);
            }
        }

        return $labelWidget->render();
    }

    /**
     * Simple render (without label)
     */
    private function renderSimple(): string
    {
        return $this->renderSwitch();
    }

    /**
     * Rendu avec label
     */
    private function renderWithLabel(): string
    {
        $switch = $this->renderSwitch();
        $label = $this->renderLabel();

        return <<<HTML
<div class="flex items-center gap-3">
    {$switch}
    {$label}
</div>
HTML;
    }

    /**
     * Implementation required by AbstractWidget
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    /**
     * Rendu du widget
     */
    public function render(): string
    {
        // Display with label if explicit label or model label
        if ($this->label !== null || $this->getLabel() !== null) {
            return $this->renderWithLabel();
        }

        return $this->renderSimple();
    }
}
