<?php

declare(strict_types=1);

/**
 * Progress.php
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
use InvalidArgumentException;
use Yiisoft\Html\Html;

/**
 * Progress widget (progress bar with optional labels)
 *
 * Supported colors: primary, success, warning, danger (not secondary, info)
 *
 * Usage:
 *   Bleet::progress(45)->render();  // auto color based on percent
 *   Bleet::progress(75)->title('Progression')->render();
 *   Bleet::progress(33)->labels(['Étape 1', 'Étape 2', 'Étape 3'])->render();
 *   Bleet::progress(20)->success()->render();  // force color
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Progress extends AbstractWidget
{
    use BleetAttributesTrait;

    private const ALLOWED_COLORS = [
        Bleet::COLOR_PRIMARY,
        Bleet::COLOR_SUCCESS,
        Bleet::COLOR_WARNING,
        Bleet::COLOR_DANGER,
    ];

    private int $percent = 0;
    private ?string $title = null;
    private ?array $labels = null;
    private bool $autoColor = true;

    public function __construct(int $percent = 0)
    {
        $this->percent = max(0, min(100, $percent));
    }

    /**
     * Override color to restrict to 4 semantic colors
     */
    public function color(string $color): static
    {
        if (!in_array($color, self::ALLOWED_COLORS, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid color "%s" for Progress. Valid: %s', $color, implode(', ', self::ALLOWED_COLORS))
            );
        }

        $new = clone $this;
        $new->color = $color;
        $new->autoColor = false;
        return $new;
    }

    /**
     * Sets the percentage
     */
    public function percent(int $percent): self
    {
        $new = clone $this;
        $new->percent = max(0, min(100, $percent));
        return $new;
    }

    /**
     * Sets the title (above the bar)
     */
    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    /**
     * Sets the labels (steps below)
     * @param string[] $labels
     */
    public function labels(array $labels): self
    {
        $new = clone $this;
        $new->labels = $labels;
        return $new;
    }

    /**
     * Enables auto mode (default)
     */
    public function auto(): self
    {
        $new = clone $this;
        $new->autoColor = true;
        return $new;
    }

    public function render(): string
    {
        $color = $this->getResolvedColor();
        $html = '';

        // SR-only heading
        $html .= Html::tag('h4', 'Status')
            ->class('sr-only')
            ->render();

        // Title
        if ($this->title !== null) {
            $html .= Html::tag('p', $this->title)
                ->class('text-sm', 'font-medium', ...$this->getTitleClasses($color))
                ->render();
        }

        // Wrapper
        $wrapperContent = '';

        // Bar container (background)
        $barHtml = Html::div('')
            ->class('h-2', 'rounded-full', ...$this->getBarClasses($color))
            ->attribute('style', "width: {$this->percent}%")
            ->render();

        $wrapperContent .= Html::div($barHtml)
            ->encode(false)
            ->class('overflow-hidden', 'rounded-full', ...$this->getBarContainerClasses($color))
            ->render();

        // Labels
        if ($this->labels !== null && count($this->labels) > 0) {
            $wrapperContent .= $this->renderLabels($color);
        }

        $html .= Html::div($wrapperContent)
            ->encode(false)
            ->class('mt-6')
            ->attribute('aria-hidden', 'true')
            ->render();

        // Container
        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::div($html)
            ->attributes($attributes)
            ->encode(false)
            ->render();
    }

    private function getResolvedColor(): string
    {
        if (!$this->autoColor) {
            return $this->color;
        }

        // Auto mode
        if ($this->percent < 33) {
            return Bleet::COLOR_DANGER;
        } elseif ($this->percent < 66) {
            return Bleet::COLOR_WARNING;
        } else {
            return Bleet::COLOR_SUCCESS;
        }
    }

    private function renderLabels(string $color): string
    {
        $labelsHtml = '';
        $totalLabels = count($this->labels);

        foreach ($this->labels as $index => $label) {
            $alignClass = $this->getLabelAlignClass($index, $totalLabels);

            $labelsHtml .= Html::div($label)
                ->class('flex-1', $alignClass)
                ->render();
        }

        return Html::div($labelsHtml)
            ->encode(false)
            ->class('mt-6', 'hidden', 'sm:flex', 'justify-between', 'text-sm', 'font-medium', ...$this->getLabelClasses($color))
            ->render();
    }

    private function getLabelAlignClass(int $index, int $total): string
    {
        if ($total === 1) {
            return 'text-center';
        } elseif ($total === 2) {
            return $index === 0 ? 'text-left' : 'text-right';
        } else {
            if ($index === 0) {
                return 'text-left';
            } elseif ($index === $total - 1) {
                return 'text-right';
            } else {
                return 'text-center';
            }
        }
    }

    /**
     * @return string[]
     */
    private function getTitleClasses(string $color): array
    {
        return match ($color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900'],
            Bleet::COLOR_SUCCESS => ['text-success-900'],
            Bleet::COLOR_WARNING => ['text-warning-900'],
            Bleet::COLOR_DANGER => ['text-danger-900'],
            default => ['text-primary-900'],
        };
    }

    /**
     * @return string[]
     */
    private function getBarContainerClasses(string $color): array
    {
        return match ($color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-200'],
            Bleet::COLOR_SUCCESS => ['bg-success-200'],
            Bleet::COLOR_WARNING => ['bg-warning-200'],
            Bleet::COLOR_DANGER => ['bg-danger-200'],
            default => ['bg-primary-200'],
        };
    }

    /**
     * @return string[]
     */
    private function getBarClasses(string $color): array
    {
        return match ($color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-600'],
            Bleet::COLOR_WARNING => ['bg-warning-600'],
            Bleet::COLOR_DANGER => ['bg-danger-600'],
            default => ['bg-primary-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getLabelClasses(string $color): array
    {
        return match ($color) {
            Bleet::COLOR_PRIMARY => ['text-primary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-600'],
            Bleet::COLOR_WARNING => ['text-warning-600'],
            Bleet::COLOR_DANGER => ['text-danger-600'],
            default => ['text-primary-600'],
        };
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }
}
