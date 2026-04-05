<?php

declare(strict_types=1);

/**
 * ShortcutCard.php
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
use Yiisoft\Html\Html;

/**
 * ShortcutCard widget - Quick action card with icon
 *
 * Displays a clickable card with:
 * - Icon in a colored rounded square
 * - Label
 * - Hover effect
 *
 * Usage:
 *   Bleet::shortcutCard('Nouveau contenu', '/content/new')->icon('plus')->render()
 *   Bleet::shortcutCard('Media library', '/media')->icon('photo')->success()->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ShortcutCard extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $label = '';
    private string $url = '#';
    private ?string $icon = null;

    public function __construct(string $label = '', string $url = '#')
    {
        $this->label = $label;
        $this->url = $url;
    }

    /**
     * Sets the label
     */
    public function label(string $label): self
    {
        $new = clone $this;
        $new->label = $label;
        return $new;
    }

    /**
     * Sets the URL
     */
    public function url(string $url): self
    {
        $new = clone $this;
        $new->url = $url;
        return $new;
    }

    /**
     * Sets the icon (nom heroicon outline)
     */
    public function icon(string $icon): self
    {
        $new = clone $this;
        $new->icon = $icon;
        return $new;
    }

    public function render(): string
    {
        $html = '';

        // Icon wrapper
        if ($this->icon !== null) {
            $iconHtml = Bleet::svg()->outline($this->icon)->addClass(...$this->getIconSvgClasses());
            $iconWrapper = Html::div($iconHtml, ['class' => $this->getIconWrapperClasses()])
                ->encode(false)
                ->render();
            $html .= $iconWrapper;
        }

        // Label
        $html .= Html::tag('p', Html::encode($this->label), ['class' => ['text-sm', 'font-medium', 'text-secondary-900']])
            ->encode(false)
            ->render();

        // Link container
        $attributes = $this->prepareTagAttributes(['href' => $this->url]);
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::a($html, $this->url)
            ->attributes($attributes)
            ->encode(false)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = [
            'bg-white',
            'rounded-lg',
            'shadow-sm',
            'p-4',
            'border',
            'hover:shadow',
            'transition-all',
            'group',
            'block',
            'no-underline',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-200', 'hover:border-primary-300'],
            Bleet::COLOR_SECONDARY => ['border-secondary-200', 'hover:border-secondary-300'],
            Bleet::COLOR_SUCCESS => ['border-success-200', 'hover:border-success-300'],
            Bleet::COLOR_DANGER => ['border-danger-200', 'hover:border-danger-300'],
            Bleet::COLOR_WARNING => ['border-warning-200', 'hover:border-warning-300'],
            Bleet::COLOR_INFO => ['border-info-200', 'hover:border-info-300'],
            Bleet::COLOR_ACCENT => ['border-accent-200', 'hover:border-accent-300'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getIconWrapperClasses(): array
    {
        $baseClasses = ['w-10', 'h-10', 'rounded-lg', 'flex', 'items-center', 'justify-center', 'mb-3', 'transition-colors'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-50', 'group-hover:bg-primary-100'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50', 'group-hover:bg-secondary-100'],
            Bleet::COLOR_SUCCESS => ['bg-success-50', 'group-hover:bg-success-100'],
            Bleet::COLOR_DANGER => ['bg-danger-50', 'group-hover:bg-danger-100'],
            Bleet::COLOR_WARNING => ['bg-warning-50', 'group-hover:bg-warning-100'],
            Bleet::COLOR_INFO => ['bg-info-50', 'group-hover:bg-info-100'],
            Bleet::COLOR_ACCENT => ['bg-accent-50', 'group-hover:bg-accent-100'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getIconSvgClasses(): array
    {
        $baseClasses = ['w-5', 'h-5'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };

        return [...$baseClasses, ...$colorClasses];
    }
}
