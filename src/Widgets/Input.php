<?php

declare(strict_types=1);

/**
 * Input.php
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
 * Input widget - Champ de saisie pour formulaires
 *
 * Usage:
 *   Bleet::input()->name('email')->placeholder('you@example.com')->render()
 *   Bleet::input()->password()->name('pwd')->render()
 *   Bleet::input()->hidden()->name('token')->value('abc123')->render()
 *   Bleet::input()->active($model, 'email')->render() // model-bound
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Input extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetColorTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;
    use BleetWrapperAttributesTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private string $type = 'text';
    private ?string $name = null;
    private ?string $value = null;
    private ?string $placeholder = null;
    private bool $disabled = false;
    private bool $readonly = false;
    private bool $required = false;
    private ?string $labelledBy = null;
    private ?string $describedBy = null;
    private ?string $autocomplete = null;
    private string|Label|null $floatingLabel = null;
    private string|Svg|null $icon = null;
    private string|Svg|null $iconLeft = null;
    private bool $showable = false;

    /**
     * Sets the input type (generic)
     */
    public function type(string $type): self
    {
        $new = clone $this;
        $new->type = $type;
        return $new;
    }

    /**
     * Text type (default)
     */
    public function text(): self
    {
        return $this->type('text');
    }

    /**
     * Type password
     */
    public function password(): self
    {
        return $this->type('password');
    }

    /**
     * Type email
     */
    public function email(): self
    {
        return $this->type('email');
    }

    /**
     * Type number
     */
    public function number(): self
    {
        return $this->type('number');
    }

    /**
     * Type date
     */
    public function date(): self
    {
        return $this->type('date');
    }

    /**
     * Type hidden (aucune classe)
     */
    public function hidden(): self
    {
        return $this->type('hidden');
    }

    /**
     * Type tel (autocomplete implicite: tel)
     */
    public function tel(): self
    {
        return $this->type('tel');
    }

    /**
     * Sets le nom du champ
     */
    public function name(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Sets the value du champ
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
     * Marks the field as disabled
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    /**
     * Marks le champ comme readonly
     */
    public function readonly(bool $readonly = true): self
    {
        $new = clone $this;
        $new->readonly = $readonly;
        return $new;
    }

    /**
     * Marks the field as required
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
     * Sets l'attribut autocomplete (override the value implicite)
     */
    public function autocomplete(string $value): self
    {
        $new = clone $this;
        $new->autocomplete = $value;
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
     * Enables le toggle show/hide pour les champs password
     */
    public function showable(bool $showable = true): self
    {
        $new = clone $this;
        $new->showable = $showable;
        return $new;
    }

    public function render(): string
    {
        // Floating label mode
        if ($this->floatingLabel !== null) {
            return $this->renderFloatingLabel();
        }

        // Showable password mode (force icon mode with toggle button)
        if ($this->showable) {
            return $this->renderShowablePassword();
        }

        // Icon mode (avec grid wrapper)
        if ($this->icon !== null || $this->iconLeft !== null) {
            return $this->renderWithIcon();
        }

        // Simple mode
        return $this->renderInput($this->prepareClasses());
    }

    /**
     * Render l'input simple
     */
    private function renderInput(array $classes): string
    {
        // Value resolution : explicit > model > null
        $name = $this->name ?? $this->getInputName();
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $this->getInputId();
        $value = $this->value ?? $this->getValue();
        $placeholder = $this->placeholder ?? $this->getPlaceholder();
        $required = $this->required || $this->isRequired();

        $defaults = [
            'type' => $this->type,
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

        if ($placeholder !== null && $this->type !== 'hidden') {
            $defaults['placeholder'] = $placeholder;
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

        // Autocomplete: explicit or implicit based on type
        $autocomplete = $this->autocomplete ?? $this->getImplicitAutocomplete();
        if ($autocomplete !== null) {
            $defaults['autocomplete'] = $autocomplete;
        }

        // User attributes override defaults
        $attributes = $this->prepareTagAttributes($defaults);

        // Hidden : aucune classe
        if ($this->type !== 'hidden') {
            Html::addCssClass($attributes, $classes);
        }

        if($this->type === 'date' && $value !== null && $value instanceof \DateTimeImmutable) {
            $value = $value->format('Y-m-d');
            $attributes['value'] = $value;
        } elseif($this->type === 'datetime-local' && $value !== null && $value instanceof \DateTimeImmutable) {
            $value = $value->format('Y-m-d\TH:i');
            $attributes['value'] = $value;
        }

        // HTML5 attributes from validation rules (minlength, maxlength, pattern, etc.)
        if ($this->hasModel()) {
            $attributes = [...$attributes, ...$this->getInputAttributes()];
        }

        // Field data-* attributes
        $attributes = [...$attributes, ...$this->getFieldDataAttributes()];

        return Html::input($this->type, $name, $value, $attributes)->render();
    }

    /**
     * Render password avec toggle show/hide
     */
    private function renderShowablePassword(): string
    {
        $hasLeftIcon = $this->iconLeft !== null;

        // Adapted input classes
        $inputClasses = $this->prepareIconInputClasses($hasLeftIcon, true); // always right icon for toggle
        $inputHtml = $this->renderInput($inputClasses);

        $content = $inputHtml;

        // Left icon if defined
        if ($hasLeftIcon) {
            $content .= $this->renderIconSvg($this->iconLeft, 'left');
        }

        // Toggle button with both icons
        $content .= $this->renderPasswordToggleButton();

        $wrapperAttributes = $this->prepareWrapperAttributes();
        Html::addCssClass($wrapperAttributes, ['grid', 'grid-cols-1']);
        $wrapperAttributes['bleet-password'] = '';

        return Html::div($content, $wrapperAttributes)->encode(false)->render();
    }

    /**
     * Renders the bouton toggle pour password showable
     */
    private function renderPasswordToggleButton(): string
    {
        $iconClasses = [
            'size-5',
            'sm:size-4',
            $this->textMutedColorClass(),
            $this->getGroupHoverIconColorClass(),
        ];

        // Eye icon (visible when password hidden)
        $eyeIcon = Bleet::svg()->solid('eye')->addClass(...$iconClasses);

        // Eye-slash icon (visible when password shown)
        $eyeSlashIcon = Bleet::svg()->solid('eye-slash')->addClass(...$iconClasses);

        $buttonClasses = [
            'group',
            'col-start-1',
            'row-start-1',
            'self-center',
            'justify-self-end',
            'mr-3',
            'cursor-pointer',
            'focus:outline-none',
        ];

        return Html::button(
            Html::span($eyeIcon->render(), ['data-password' => 'icon-hidden'])->encode(false) .
            Html::span($eyeSlashIcon->render(), ['data-password' => 'icon-visible', 'class' => 'hidden'])->encode(false),
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

        // Adapted input classes for icons
        $inputClasses = $this->prepareIconInputClasses($hasLeftIcon, $hasRightIcon);
        $inputHtml = $this->renderInput($inputClasses);

        $content = $inputHtml;

        // Left icon
        if ($hasLeftIcon) {
            $content .= $this->renderIconSvg($this->iconLeft, 'left');
        }

        // Right icon
        if ($hasRightIcon) {
            $content .= $this->renderIconSvg($this->icon, 'right');
        }

        $wrapperAttributes = $this->prepareWrapperAttributes();
        Html::addCssClass($wrapperAttributes, ['grid', 'grid-cols-1']);

        return Html::div($content, $wrapperAttributes)->encode(false)->render();
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

        // Classes label flottant : text-xs au lieu de text-sm
        $label = $label->addClass('!text-xs');

        // Render the label
        $labelHtml = $label->render();

        // Input with simplified classes (no rounded, outline on wrapper)
        $inputClasses = $this->prepareFloatingInputClasses();

        // If we have icons, we must wrap input in a grid
        if ($this->icon !== null || $this->iconLeft !== null) {
            $inputHtml = $this->renderFloatingInputWithIcon($inputClasses);
        } else {
            $inputHtml = $this->renderInput($inputClasses);
        }

        $content = $labelHtml . $inputHtml;

        // Wrapper attributes
        $wrapperAttributes = $this->prepareWrapperAttributes();
        Html::addCssClass($wrapperAttributes, $this->prepareFloatingWrapperClasses());

        return Html::div($content, $wrapperAttributes)->encode(false)->render();
    }

    /**
     * Render input with icon in floating label context
     */
    private function renderFloatingInputWithIcon(array $inputClasses): string
    {
        $hasLeftIcon = $this->iconLeft !== null;
        $hasRightIcon = $this->icon !== null;

        // Adapt classes for icons
        $inputClasses = $this->addIconPadding($inputClasses, $hasLeftIcon, $hasRightIcon);
        $inputClasses[] = 'col-start-1';
        $inputClasses[] = 'row-start-1';

        $inputHtml = $this->renderInput($inputClasses);

        $content = $inputHtml;

        // Left icon
        if ($hasLeftIcon) {
            $content .= $this->renderIconSvg($this->iconLeft, 'left');
        }

        // Right icon
        if ($hasRightIcon) {
            $content .= $this->renderIconSvg($this->icon, 'right');
        }

        return Html::div($content, ['class' => 'grid grid-cols-1'])->encode(false)->render();
    }

    /**
     * Renders an SVG icon
     */
    private function renderIconSvg(string|Svg $icon, string $position): string
    {
        if (is_string($icon)) {
            $svg = Bleet::svg()->solid($icon);
        } else {
            $svg = $icon;
        }

        // Base classes pour the icon
        $iconClasses = [
            'pointer-events-none',
            'col-start-1',
            'row-start-1',
            'size-5',
            'sm:size-4',
            'self-center',
        ];

        // Position
        if ($position === 'left') {
            $iconClasses[] = 'justify-self-start';
            $iconClasses[] = 'ml-3';
        } else {
            $iconClasses[] = 'justify-self-end';
            $iconClasses[] = 'mr-3';
        }

        // Couleur
        $iconClasses[] = $this->textMutedColorClass();

        return $svg->addClass(...$iconClasses)->render();
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
     * Classes for input with icon(s)
     * @return string[]
     */
    private function prepareIconInputClasses(bool $hasLeftIcon, bool $hasRightIcon): array
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

        // Padding adapted for icons
        return $this->addIconPadding($classes, $hasLeftIcon, $hasRightIcon);
    }

    /**
     * Adds padding classes based on icons
     * @return string[]
     */
    private function addIconPadding(array $classes, bool $hasLeftIcon, bool $hasRightIcon): array
    {
        if ($hasLeftIcon && $hasRightIcon) {
            $classes[] = 'pl-10';
            $classes[] = 'sm:pl-9';
            $classes[] = 'pr-10';
            $classes[] = 'sm:pr-9';
        } elseif ($hasLeftIcon) {
            $classes[] = 'pl-10';
            $classes[] = 'sm:pl-9';
            $classes[] = 'pr-3';
        } elseif ($hasRightIcon) {
            $classes[] = 'pl-3';
            $classes[] = 'pr-10';
            $classes[] = 'sm:pr-9';
        }

        return $classes;
    }

    /**
     * Classes for input en mode floating label
     * @return string[]
     */
    private function prepareFloatingInputClasses(): array
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
            $this->getFloatingOutlineColorClass(),
            $this->getFloatingFocusWithinOutlineColorClass(),
        ];
    }

    /**
     * Classe couleur outline pour wrapper floating (bordure statique)
     */
    private function getFloatingOutlineColorClass(): string
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
     * Classe couleur focus-within outline pour wrapper floating (bordure sur focus)
     */
    private function getFloatingFocusWithinOutlineColorClass(): string
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
     * Classe couleur pour le placeholder
     */
    private function getPlaceholderColorClass(): string
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
    private function getGroupHoverIconColorClass(): string
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
     * Returns the value autocomplete implicite selon le type
     */
    private function getImplicitAutocomplete(): ?string
    {
        return match ($this->type) {
            'email' => 'email',
            'password' => 'current-password',
            'tel' => 'tel',
            default => null,
        };
    }
}
