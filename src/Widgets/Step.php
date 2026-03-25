<?php

declare(strict_types=1);

/**
 * Step.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;

/**
 * Step widget - Multi-step progress navigation
 *
 * Displays workflow progression with 3 states: completed (clickable), current (active), upcoming (disabled).
 * Status is automatically calculated based on current step index.
 *
 * Usage:
 *   Bleet::step()
 *       ->current(1)
 *       ->steps([
 *           ['label' => 'Details', 'url' => '/step/1'],
 *           ['label' => 'Form', 'url' => '/step/2'],
 *           ['label' => 'Preview', 'url' => '/step/3'],
 *       ])
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Step extends AbstractWidget
{
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private int $currentIndex = 0;
    private array $steps = [];
    private array $containerAttributes = [];

    /**
     * Sets the current step index (0-based)
     */
    public function current(int $index): self
    {
        $new = clone $this;
        $new->currentIndex = $index;
        return $new;
    }

    /**
     * Sets the steps
     * @param array<array{label: string, url?: string}> $steps
     */
    public function steps(array $steps): self
    {
        $new = clone $this;
        $new->steps = $steps;
        return $new;
    }

    /**
     * Adds a step
     */
    public function addStep(string $label, ?string $url = null): self
    {
        $new = clone $this;
        $step = ['label' => $label];
        if ($url !== null) {
            $step['url'] = $url;
        }
        $new->steps[] = $step;
        return $new;
    }

    /**
     * Sets the id attribute
     */
    public function id(string $id): self
    {
        $new = clone $this;
        $new->containerAttributes['id'] = $id;
        return $new;
    }

    /**
     * Adds a CSS class
     */
    public function addClass(string ...$classes): self
    {
        $new = clone $this;
        $existing = $new->containerAttributes['class'] ?? '';
        $new->containerAttributes['class'] = trim($existing . ' ' . implode(' ', $classes));
        return $new;
    }

    /**
     * Sets an HTML attribute
     */
    public function attribute(string $name, mixed $value): self
    {
        $new = clone $this;
        $new->containerAttributes[$name] = $value;
        return $new;
    }

    public function render(): string
    {
        $stepsData = $this->prepareStepsData();

        return $this->renderView('step', [
            'steps' => $stepsData,
            'navAttributes' => array_merge($this->containerAttributes, ['aria-label' => 'Progress']),
            'listClasses' => $this->getListClasses(),
            'itemClasses' => ['relative', 'lg:flex', 'lg:flex-1'],
            'separatorClasses' => ['absolute', 'top-0', 'right-0', 'hidden', 'h-full', 'w-5', 'lg:block'],
            'separatorIconClasses' => ['size-full', ...$this->getSeparatorIconColorClasses()],
            // Completed
            'completedLinkClasses' => ['group', 'flex', 'w-full', 'items-center'],
            'completedContentClasses' => ['flex', 'items-center', 'px-6', 'py-4', 'text-sm', 'font-medium'],
            'completedBadgeClasses' => $this->getCompletedBadgeClasses(),
            'completedIconClasses' => ['size-6', 'text-white'],
            'completedLabelClasses' => ['ml-4', 'text-sm', 'font-medium', ...$this->getCompletedLabelColorClasses()],
            // Current
            'currentSpanClasses' => ['flex', 'items-center', 'px-6', 'py-4', 'text-sm', 'font-medium'],
            'currentBadgeClasses' => $this->getCurrentBadgeClasses(),
            'currentNumberClasses' => $this->getCurrentNumberClasses(),
            'currentLabelClasses' => ['ml-4', 'text-sm', 'font-medium', ...$this->getCurrentLabelColorClasses()],
            // Upcoming
            'upcomingSpanClasses' => ['group', 'flex', 'items-center'],
            'upcomingContentClasses' => ['flex', 'items-center', 'px-6', 'py-4', 'text-sm', 'font-medium'],
            'upcomingBadgeClasses' => $this->getUpcomingBadgeClasses(),
            'upcomingNumberClasses' => $this->getUpcomingNumberClasses(),
            'upcomingLabelClasses' => ['ml-4', 'text-sm', 'font-medium', ...$this->getUpcomingLabelClasses()],
        ]);
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    /**
     * @return array<array{label: string, status: string, url: string|null, number: int}>
     */
    private function prepareStepsData(): array
    {
        $stepsData = [];
        foreach ($this->steps as $index => $step) {
            $status = match (true) {
                $index < $this->currentIndex => 'completed',
                $index === $this->currentIndex => 'current',
                default => 'upcoming',
            };

            $stepsData[] = [
                'label' => $step['label'],
                'status' => $status,
                'url' => $step['url'] ?? null,
                'number' => $index + 1,
            ];
        }
        return $stepsData;
    }

    /**
     * @return string[]
     */
    private function getListClasses(): array
    {
        $baseClasses = ['overflow-x-auto', 'divide-y', 'rounded-md', 'border', 'lg:flex', 'lg:divide-y-0'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['divide-primary-300', 'border-primary-300'],
            Bleet::COLOR_SECONDARY => ['divide-secondary-300', 'border-secondary-300'],
            Bleet::COLOR_SUCCESS => ['divide-success-300', 'border-success-300'],
            Bleet::COLOR_DANGER => ['divide-danger-300', 'border-danger-300'],
            Bleet::COLOR_WARNING => ['divide-warning-300', 'border-warning-300'],
            Bleet::COLOR_INFO => ['divide-info-300', 'border-info-300'],
            Bleet::COLOR_ACCENT => ['divide-accent-300', 'border-accent-300'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getSeparatorIconColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-300'],
            Bleet::COLOR_SECONDARY => ['text-secondary-300'],
            Bleet::COLOR_SUCCESS => ['text-success-300'],
            Bleet::COLOR_DANGER => ['text-danger-300'],
            Bleet::COLOR_WARNING => ['text-warning-300'],
            Bleet::COLOR_INFO => ['text-info-300'],
            Bleet::COLOR_ACCENT => ['text-accent-300'],
        };
    }

    /**
     * @return string[]
     */
    private function getCompletedBadgeClasses(): array
    {
        // Completed uses success
        return ['flex', 'size-10', 'shrink-0', 'items-center', 'justify-center', 'rounded-full', 'bg-success-700', 'group-hover:bg-success-800'];
    }

    /**
     * @return string[]
     */
    private function getCompletedLabelColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };
    }

    /**
     * @return string[]
     */
    private function getCurrentBadgeClasses(): array
    {
        $baseClasses = ['flex', 'size-10', 'shrink-0', 'items-center', 'justify-center', 'rounded-full', 'border-2'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-600'],
            Bleet::COLOR_SECONDARY => ['border-secondary-600'],
            Bleet::COLOR_SUCCESS => ['border-success-600'],
            Bleet::COLOR_DANGER => ['border-danger-600'],
            Bleet::COLOR_WARNING => ['border-warning-600'],
            Bleet::COLOR_INFO => ['border-info-600'],
            Bleet::COLOR_ACCENT => ['border-accent-600'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getCurrentNumberClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };
    }

    /**
     * @return string[]
     */
    private function getCurrentLabelColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };
    }

    /**
     * @return string[]
     */
    private function getUpcomingBadgeClasses(): array
    {
        // Upcoming uses secondary
        return ['flex', 'size-10', 'shrink-0', 'items-center', 'justify-center', 'rounded-full', 'border-2', 'border-secondary-300', 'group-hover:border-secondary-400'];
    }

    /**
     * @return string[]
     */
    private function getUpcomingNumberClasses(): array
    {
        return ['text-secondary-500', 'group-hover:text-secondary-700'];
    }

    /**
     * @return string[]
     */
    private function getUpcomingLabelClasses(): array
    {
        return ['text-secondary-500', 'group-hover:text-secondary-700'];
    }
}
