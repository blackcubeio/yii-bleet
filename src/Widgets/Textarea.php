<?php

declare(strict_types=1);

/**
 * Textarea.php
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
use Blackcube\Bleet\Traits\BleetWrapperAttributesTrait;
use Yiisoft\Html\Html;

/**
 * Textarea widget - Zone de texte multi-lignes pour formulaires
 *
 * Usage:
 *   Bleet::textarea()->name('comment')->placeholder('Votre commentaire...')->render()
 *   Bleet::textarea()->name('message')->rows(6)->floatingLabel('Message')->render()
 *   Bleet::textarea()->active($model, 'message')->render() // model-bound
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Textarea extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetColorTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;
    use BleetWrapperAttributesTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private ?string $name = null;
    private ?string $value = null;
    private ?string $placeholder = null;
    private int $rows = 4;
    private ?int $cols = null;
    private bool $disabled = false;
    private bool $readonly = false;
    private bool $required = false;
    private ?string $labelledBy = null;
    private ?string $describedBy = null;
    private string|Label|null $floatingLabel = null;

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
     * Sets the value (contenu)
     */
    public function value(string $value): self
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }

    /**
     * Sets le placeholder
     */
    public function placeholder(string $placeholder): self
    {
        $new = clone $this;
        $new->placeholder = $placeholder;
        return $new;
    }

    /**
     * Sets le nombre de lignes visibles
     */
    public function rows(int $rows): self
    {
        $new = clone $this;
        $new->rows = $rows;
        return $new;
    }

    /**
     * Sets la largeur en colonnes
     */
    public function cols(int $cols): self
    {
        $new = clone $this;
        $new->cols = $cols;
        return $new;
    }

    /**
     * Disables le textarea
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    /**
     * Rend le textarea en lecture seule
     */
    public function readonly(bool $readonly = true): self
    {
        $new = clone $this;
        $new->readonly = $readonly;
        return $new;
    }

    /**
     * Marks le textarea comme requis
     */
    public function required(bool $required = true): self
    {
        $new = clone $this;
        $new->required = $required;
        return $new;
    }

    /**
     * Sets aria-labelledby
     */
    public function labelledBy(string $id): self
    {
        $new = clone $this;
        $new->labelledBy = $id;
        return $new;
    }

    /**
     * Sets aria-describedby
     */
    public function describedBy(string $id): self
    {
        $new = clone $this;
        $new->describedBy = $id;
        return $new;
    }

    /**
     * Sets an integrated floating label
     */
    public function floatingLabel(string|Label $label): self
    {
        $new = clone $this;
        $new->floatingLabel = $label;
        return $new;
    }

    /**
     * Renders the textarea
     */
    public function render(): string
    {
        // Floating label mode
        if ($this->floatingLabel !== null) {
            return $this->renderFloatingLabel();
        }

        // Simple mode
        return $this->renderTextarea($this->prepareClasses());
    }

    /**
     * Renders the textarea HTML
     */
    private function renderTextarea(array $classes): string
    {
        // Value resolution : explicit > model > null
        $name = $this->name ?? $this->getInputName();
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $this->getInputId();
        $value = $this->value ?? $this->getValue();
        $placeholder = $this->placeholder ?? $this->getPlaceholder();
        $required = $this->required || $this->isRequired();

        $defaults = [
            'rows' => $this->rows,
        ];

        if ($name !== null) {
            $defaults['name'] = $name;
        }

        if ($id !== null) {
            $defaults['id'] = $id;
        }

        if ($placeholder !== null) {
            $defaults['placeholder'] = $placeholder;
        }

        if ($this->cols !== null) {
            $defaults['cols'] = $this->cols;
        }

        if ($this->disabled) {
            $defaults['disabled'] = true;
        }

        if ($this->readonly) {
            $defaults['readonly'] = true;
        }

        if ($required) {
            $defaults['required'] = true;
        }

        if ($this->labelledBy !== null) {
            $defaults['aria-labelledby'] = $this->labelledBy;
        }

        if ($this->describedBy !== null) {
            $defaults['aria-describedby'] = $this->describedBy;
        }

        // User attributes override defaults
        $attributes = $this->prepareTagAttributes($defaults);

        // Classes
        Html::addCssClass($attributes, $classes);

        // HTML5 attributes from validation rules (minlength, maxlength, etc.)
        if ($this->hasModel()) {
            $attributes = [...$attributes, ...$this->getInputAttributes()];
        }

        // Field data-* attributes
        $attributes = [...$attributes, ...$this->getFieldDataAttributes()];

        return Html::textarea($name ?? '', $value ?? '')
            ->attributes($attributes)
            ->render();
    }

    /**
     * Renders with label flottant
     */
    private function renderFloatingLabel(): string
    {
        // Id resolution: explicit > model > null
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $this->getInputId();

        // Prepare the label
        $label = $this->floatingLabel;
        if (is_string($label)) {
            $label = new Label($label);
        }

        // Auto-set for if id present
        if ($id !== null) {
            $label = $label->for($id);
        }

        // Apply parent color to label
        $label = $label->color($this->color);

        // Classes label flottant : text-xs au lieu de text-sm
        $label = $label->addClass('!text-xs');

        // Render the label
        $labelHtml = $label->render();

        // Textarea with simplified classes
        $textareaClasses = $this->prepareFloatingTextareaClasses();
        $textareaHtml = $this->renderTextarea($textareaClasses);

        $content = $labelHtml . $textareaHtml;

        // Wrapper attributes
        $wrapperAttributes = $this->prepareWrapperAttributes();
        Html::addCssClass($wrapperAttributes, $this->prepareFloatingWrapperClasses());

        return Html::div($content, $wrapperAttributes)->encode(false)->render();
    }

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
    private function prepareFloatingTextareaClasses(): array
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
    private function prepareFloatingWrapperClasses(): array
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
    private function getFloatingTextareaColorClasses(): array
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
    private function getFloatingWrapperColorClasses(): array
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
