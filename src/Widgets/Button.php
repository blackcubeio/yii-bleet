<?php

declare(strict_types=1);

/**
 * Button.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Blackcube\Bleet\Traits\BleetFieldDataTrait;
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Yiisoft\Html\Html;

/**
 * Button widget
 *
 * Usage:
 *   Bleet::button('OK')->submit()->render()
 *   Bleet::button('Delete')->danger()->icon('trash')->render()
 *   Bleet::button('Save')->submit()->attribute('data-confirm', 'true')->render()
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Button extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;

    private string $type = 'button';
    private ?string $iconName = null;
    private string $iconType = 'outline';
    private ?int $badge = null;
    private ?string $originalContent = null;
    private bool $encode = true;
    private bool $outline = false;
    private bool $ghost = false;
    private bool $inverse = false;
    private mixed $buttonValue = null;
    private bool $disabled = false;

    public function __construct(string $content = '')
    {
        if ($content !== '') {
            $this->originalContent = $content;
        }
    }

    /**
     * Sets the button content
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->originalContent = $content;
        return $new;
    }

    /**
     * Controls HTML encoding of content
     */
    public function encode(bool $encode = true): self
    {
        $new = clone $this;
        $new->encode = $encode;
        return $new;
    }

    /**
     * Sets the button type
     */
    public function type(string $type): self
    {
        $new = clone $this;
        $new->type = $type;
        return $new;
    }

    /**
     * Sets the button as disabled
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    /**
     * Sets the icon (outline by default)
     */
    public function icon(string $name, string $type = 'outline'): self
    {
        $new = clone $this;
        $new->iconName = $name;
        $new->iconType = $type;
        return $new;
    }

    /**
     * Sets le badge (compteur)
     */
    public function badge(int $count): self
    {
        $new = clone $this;
        $new->badge = $count;
        return $new;
    }

    /**
     * Sets outline mode (no background, colored text, bg on hover)
     */
    public function outline(bool $outline = true): self
    {
        $new = clone $this;
        $new->outline = $outline;
        return $new;
    }

    /**
     * Sets ghost mode (border, no background, bg on hover)
     */
    public function ghost(bool $ghost = true): self
    {
        $new = clone $this;
        $new->ghost = $ghost;
        return $new;
    }

    /**
     * Sets inverse mode (light background, colored icon/text)
     */
    public function inverse(bool $inverse = true): self
    {
        $new = clone $this;
        $new->inverse = $inverse;
        return $new;
    }

    /**
     * Sets the button value attribute.
     */
    public function value(mixed $value): self
    {
        $new = clone $this;
        $new->buttonValue = $value;
        return $new;
    }

    /**
     * Sets the button type to submit
     */
    public function submit(): self
    {
        return $this->type('submit');
    }

    /**
     * Sets the button type to reset
     */
    public function reset(): self
    {
        return $this->type('reset');
    }

    /**
     * Sets the button type to button
     */
    public function button(): self
    {
        return $this->type('button');
    }

    /**
     * @return bool
     */
    public function hasIcon(): bool
    {
        return $this->iconName !== null;
    }

    /**
     * @return bool
     */
    public function hasBadge(): bool
    {
        return $this->badge !== null;
    }

    public function render(): string
    {
        // Build content with icon and badge if present
        $content = $this->buildContent();

        $defaults = ['type' => $this->type];
        if ($this->disabled) {
            $defaults['disabled'] = true;
        }

        $attributes = $this->prepareTagAttributes($defaults);
        Html::addCssClass($attributes, $this->prepareClasses());

        // Add fieldData attributes
        $attributes = array_merge($attributes, $this->getFieldDataAttributes());

        // Model binding: name + value on the button
        if ($this->hasModel()) {
            $attributes['name'] = $this->getInputName();
        }
        if ($this->buttonValue !== null) {
            $attributes['value'] = (string) $this->buttonValue;
        }

        $hasHtmlContent = $this->iconName !== null || $this->badge !== null;
        $encode = $hasHtmlContent ? false : $this->encode;

        return Html::tag('button', $content ?? '', $attributes)
            ->encode($encode)
            ->render();
    }

    /**
     * Render as menu item (without button styling)
     * @param string ...$classes Additional classes to apply
     */
    public function renderAsMenuItem(string ...$classes): string
    {
        $content = $this->originalContent;
        $attributes = ['type' => 'button'];
        Html::addCssClass($attributes, $classes);

        return Html::tag('button', $content ?? '', $attributes)->render();
    }

    /**
     * Build content with icon and/or badge
     */
    private function buildContent(): ?string
    {
        $parts = [];

        // Icon
        if ($this->iconName !== null) {
            $iconSize = $this->outline ? 'size-4' : 'size-6';
            $svg = $this->iconType === 'solid'
                ? Bleet::svg()->solid($this->iconName)->addClass($iconSize)
                : Bleet::svg()->outline($this->iconName)->addClass($iconSize);
            $parts[] = $svg->render();
        }

        // Original content
        if ($this->originalContent !== null) {
            $parts[] = Html::encode($this->originalContent);
        }

        // Badge
        if ($this->badge !== null) {
            $parts[] = Html::span((string) $this->badge)
                ->addClass('absolute', '-top-1', '-right-1', 'size-4', 'text-xs', 'font-bold', 'text-white', 'bg-danger-600', 'rounded-full', 'flex', 'items-center', 'justify-center')
                ->render();
        }

        if (empty($parts)) {
            return null;
        }

        return implode('', $parts);
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        // Add flex classes when icon + content
        $hasIconAndContent = $this->iconName !== null && $this->originalContent !== null;
        $flexClasses = $hasIconAndContent ? ['inline-flex', 'items-center', 'gap-2'] : [];

        if ($this->outline) {
            $baseClasses = [
                'rounded-md',
                'cursor-pointer',
                'disabled:cursor-not-allowed',
                'disabled:opacity-50',
            ];
        } elseif ($this->ghost) {
            $baseClasses = [
                'rounded-md',
                'font-semibold',
                'border',
                'cursor-pointer',
                'disabled:cursor-not-allowed',
                'disabled:opacity-50',
                'focus-visible:ring-2',
                'focus-visible:ring-offset-2',
            ];
        } elseif ($this->inverse) {
            $baseClasses = [
                'rounded-md',
                'font-semibold',
                'shadow-xs',
                'cursor-pointer',
                'disabled:cursor-not-allowed',
                'disabled:opacity-50',
                'focus-visible:ring-2',
                'focus-visible:ring-offset-2',
            ];
        } else {
            $baseClasses = [
                'rounded-md',
                'font-semibold',
                'text-white',
                'shadow-xs',
                'cursor-pointer',
                'disabled:cursor-not-allowed',
                'disabled:opacity-50',
                'focus-visible:ring-2',
                'focus-visible:ring-offset-2',
            ];
        }

        return [...$flexClasses, ...$baseClasses, ...$this->getColorClasses(), ...$this->getSizeClasses()];
    }

    /**
     * @return string[]
     */
    private function getColorClasses(): array
    {
        if ($this->outline) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:bg-primary-700', 'hover:text-white'],
                Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:bg-secondary-700', 'hover:text-white'],
                Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:bg-success-700', 'hover:text-white'],
                Bleet::COLOR_DANGER => ['text-danger-700', 'hover:bg-danger-700', 'hover:text-white'],
                Bleet::COLOR_WARNING => ['text-warning-700', 'hover:bg-warning-700', 'hover:text-white'],
                Bleet::COLOR_INFO => ['text-info-700', 'hover:bg-info-700', 'hover:text-white'],
                Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:bg-accent-700', 'hover:text-white'],
            };
        }

        if ($this->ghost) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['text-primary-700', 'border-primary-300', 'hover:bg-primary-600', 'hover:text-white', 'hover:border-primary-600', 'focus-visible:ring-primary-600'],
                Bleet::COLOR_SECONDARY => ['text-secondary-700', 'border-secondary-300', 'hover:bg-secondary-600', 'hover:text-white', 'hover:border-secondary-600', 'focus-visible:ring-secondary-600'],
                Bleet::COLOR_SUCCESS => ['text-success-700', 'border-success-300', 'hover:bg-success-600', 'hover:text-white', 'hover:border-success-600', 'focus-visible:ring-success-600'],
                Bleet::COLOR_DANGER => ['text-danger-700', 'border-danger-300', 'hover:bg-danger-600', 'hover:text-white', 'hover:border-danger-600', 'focus-visible:ring-danger-600'],
                Bleet::COLOR_WARNING => ['text-warning-700', 'border-warning-300', 'hover:bg-warning-600', 'hover:text-white', 'hover:border-warning-600', 'focus-visible:ring-warning-600'],
                Bleet::COLOR_INFO => ['text-info-700', 'border-info-300', 'hover:bg-info-600', 'hover:text-white', 'hover:border-info-600', 'focus-visible:ring-info-600'],
                Bleet::COLOR_ACCENT => ['text-accent-700', 'border-accent-300', 'hover:bg-accent-600', 'hover:text-white', 'hover:border-accent-600', 'focus-visible:ring-accent-600'],
            };
        }

        if ($this->inverse) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['bg-white/90', 'text-primary-700', 'hover:bg-white', 'hover:text-primary-800', 'focus-visible:ring-primary-600'],
                Bleet::COLOR_SECONDARY => ['bg-white/90', 'text-secondary-700', 'hover:bg-white', 'hover:text-secondary-800', 'focus-visible:ring-secondary-600'],
                Bleet::COLOR_SUCCESS => ['bg-white/90', 'text-success-700', 'hover:bg-white', 'hover:text-success-800', 'focus-visible:ring-success-600'],
                Bleet::COLOR_DANGER => ['bg-white/90', 'text-danger-700', 'hover:bg-white', 'hover:text-danger-800', 'focus-visible:ring-danger-600'],
                Bleet::COLOR_WARNING => ['bg-white/90', 'text-warning-700', 'hover:bg-white', 'hover:text-warning-800', 'focus-visible:ring-warning-600'],
                Bleet::COLOR_INFO => ['bg-white/90', 'text-info-700', 'hover:bg-white', 'hover:text-info-800', 'focus-visible:ring-info-600'],
                Bleet::COLOR_ACCENT => ['bg-white/90', 'text-accent-700', 'hover:bg-white', 'hover:text-accent-800', 'focus-visible:ring-accent-600'],
            };
        }

        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600', 'hover:bg-primary-700', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-600', 'hover:bg-secondary-700', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-600', 'hover:bg-success-700', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['bg-danger-600', 'hover:bg-danger-700', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['bg-warning-600', 'hover:bg-warning-700', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['bg-info-600', 'hover:bg-info-700', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['bg-accent-600', 'hover:bg-accent-700', 'focus-visible:ring-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getSizeClasses(): array
    {
        if ($this->outline) {
            return match ($this->size) {
                Bleet::SIZE_XS => ['p-1'],
                Bleet::SIZE_SM => ['p-1.5'],
                Bleet::SIZE_MD => ['p-2'],
                Bleet::SIZE_LG => ['p-2.5'],
                Bleet::SIZE_XL => ['p-3'],
            };
        }

        // Ghost uses same sizes as default button
        return match ($this->size) {
            Bleet::SIZE_XS => ['px-2', 'py-1', 'text-xs'],
            Bleet::SIZE_SM => ['px-3', 'py-2', 'text-sm'],
            Bleet::SIZE_MD => ['px-4', 'py-2.5', 'text-sm'],
            Bleet::SIZE_LG => ['px-5', 'py-3', 'text-base'],
            Bleet::SIZE_XL => ['px-6', 'py-4', 'text-lg'],
        };
    }
}
