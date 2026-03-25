<?php

declare(strict_types=1);

/**
 * StatCard.php
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
use Yiisoft\Html\Html;

/**
 * StatCard widget - Statistics card for dashboards
 *
 * Displays a stat card with:
 * - Label (what the stat represents)
 * - Value (the main number/text)
 * - Icon (optional, in a colored circle)
 * - Trend (optional, +/-% with color)
 *
 * Usage:
 *   Bleet::statCard('Visiteurs', '12,458')->render()
 *   Bleet::statCard('Visiteurs', '12,458')->icon('users')->render()
 *   Bleet::statCard('Visiteurs', '12,458')->icon('users')->trend('+12.5%', 'vs mois dernier')->render()
 *   Bleet::statCard('Commandes', '284')->icon('shopping-bag')->trend('-3.2%', 'vs mois dernier')->trendDown()->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class StatCard extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $label = '';
    private string $value = '';
    private ?string $icon = null;
    private ?string $trendValue = null;
    private ?string $trendLabel = null;
    private ?string $trendDirection = null; // 'up', 'down', null (neutral)

    public function __construct(string $label = '', string $value = '')
    {
        $this->label = $label;
        $this->value = $value;
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
     * Sets the value
     */
    public function value(string $value): self
    {
        $new = clone $this;
        $new->value = $value;
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

    /**
     * Sets the trend (float: auto-format avec % et direction)
     */
    public function trend(float $value, ?string $label = null): self
    {
        $new = clone $this;
        $new->trendLabel = $label;

        if ($value > 0) {
            $new->trendValue = '+' . number_format($value, 1) . '%';
            $new->trendDirection = 'up';
        } elseif ($value < 0) {
            $new->trendValue = number_format($value, 1) . '%';
            $new->trendDirection = 'down';
        } else {
            $new->trendValue = 'Stable';
            $new->trendDirection = null;
        }

        return $new;
    }

    public function render(): string
    {
        $html = '';

        // Top row: label + icon
        $topContent = Html::tag('p', Html::encode($this->label), ['class' => $this->getLabelClasses()])
            ->encode(false)
            ->render();

        if ($this->icon !== null) {
            $iconHtml = Bleet::svg()->outline($this->icon)->addClass(...$this->getIconSvgClasses());
            $topContent .= Html::div($iconHtml, ['class' => $this->getIconWrapperClasses()])
                ->encode(false)
                ->render();
        }

        $html .= Html::div($topContent, ['class' => ['flex', 'items-start', 'justify-between']])
            ->encode(false)
            ->render();

        // Bottom: value (pushed to bottom via flex-1 spacer or mt-auto)
        $html .= Html::tag('p', Html::encode($this->value), ['class' => $this->getValueClasses()])
            ->encode(false)
            ->render();

        // Trend
        if ($this->trendValue !== null) {
            $html .= $this->renderTrend();
        }

        // Container with flex column
        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::div($html)
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
            'flex',
            'flex-col',
            'bg-white',
            'rounded-lg',
            'shadow-sm',
            'p-6',
            'border',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-200'],
            Bleet::COLOR_SECONDARY => ['border-secondary-200'],
            Bleet::COLOR_ACCENT => ['border-accent-200'],
            Bleet::COLOR_SUCCESS => ['border-success-200'],
            Bleet::COLOR_DANGER => ['border-danger-200'],
            Bleet::COLOR_WARNING => ['border-warning-200'],
            Bleet::COLOR_INFO => ['border-info-200'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getLabelClasses(): array
    {
        return ['text-sm', 'font-medium', 'text-secondary-500'];
    }

    /**
     * @return string[]
     */
    private function getValueClasses(): array
    {
        return ['text-2xl', 'font-bold', 'text-secondary-900', 'mt-auto', 'pt-2'];
    }

    /**
     * @return string[]
     */
    private function getIconWrapperClasses(): array
    {
        $baseClasses = ['w-12', 'h-12', 'rounded-full', 'flex', 'items-center', 'justify-center'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-50'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50'],
            Bleet::COLOR_ACCENT => ['bg-accent-50'],
            Bleet::COLOR_SUCCESS => ['bg-success-50'],
            Bleet::COLOR_DANGER => ['bg-danger-50'],
            Bleet::COLOR_WARNING => ['bg-warning-50'],
            Bleet::COLOR_INFO => ['bg-info-50'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getIconSvgClasses(): array
    {
        $baseClasses = ['w-6', 'h-6'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    private function renderTrend(): string
    {
        $trendHtml = '';

        // Arrow + value
        $trendClasses = $this->getTrendClasses();

        $arrowAndValue = '';
        if ($this->trendDirection === 'up') {
            $arrowAndValue .= Bleet::svg()->outline('arrow-up')->addClass('w-4', 'h-4', 'mr-1');
        } elseif ($this->trendDirection === 'down') {
            $arrowAndValue .= Bleet::svg()->outline('arrow-down')->addClass('w-4', 'h-4', 'mr-1');
        }
        $arrowAndValue .= Html::encode($this->trendValue);

        $trendHtml .= Html::tag('span', $arrowAndValue, ['class' => $trendClasses])
            ->encode(false)
            ->render();

        // Label
        if ($this->trendLabel !== null) {
            $trendHtml .= Html::tag('span', Html::encode($this->trendLabel), ['class' => ['text-secondary-500', 'ml-2']])
                ->encode(false)
                ->render();
        }

        return Html::div($trendHtml, ['class' => ['mt-4', 'flex', 'items-center', 'text-sm']])
            ->encode(false)
            ->render();
    }

    /**
     * @return string[]
     */
    private function getTrendClasses(): array
    {
        $baseClasses = ['font-medium', 'flex', 'items-center'];

        $colorClasses = match ($this->trendDirection) {
            'up' => ['text-success-700'],
            'down' => ['text-danger-700'],
            default => ['text-secondary-500'],
        };

        return [...$baseClasses, ...$colorClasses];
    }
}
