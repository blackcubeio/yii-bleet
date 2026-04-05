<?php

declare(strict_types=1);

/**
 * ActivityFeed.php
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
 * ActivityFeed widget - Timeline of activity items
 *
 * Usage:
 *   Bleet::activityFeed()
 *       ->addItem(Bleet::activityItem('Order validated')->icon('check')->success()->timestamp('2 minutes ago'))
 *       ->addItem(Bleet::activityItem('Nouveau client')->icon('user')->primary()->timestamp('Il y a 15 min'))
 *       ->render()
 *
 *   Bleet::activityFeed()
 *       ->title('Recent activity')
 *       ->addItem(...)
 *       ->unstyled()  // Without container styles
 *       ->linkUrl('/activity')
 *       ->linkLabel('View all activity')
 *       ->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ActivityFeed extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    /** @var ActivityItem[] */
    private array $items = [];
    private ?string $title = null;
    private ?string $linkUrl = null;
    private ?string $linkLabel = null;
    private bool $showConnector = true;
    private bool $styled = true;

    /**
     * Sets the title
     */
    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    /**
     * Adds un item
     */
    public function addItem(ActivityItem $item): self
    {
        $new = clone $this;
        $new->items[] = $item;
        return $new;
    }

    /**
     * Sets tous les items
     * @param ActivityItem[] $items
     */
    public function items(array $items): self
    {
        $new = clone $this;
        $new->items = $items;
        return $new;
    }

    /**
     * Sets the URL du lien "voir tout"
     */
    public function linkUrl(string $url): self
    {
        $new = clone $this;
        $new->linkUrl = $url;
        return $new;
    }

    /**
     * Sets the label du lien "voir tout"
     */
    public function linkLabel(string $label): self
    {
        $new = clone $this;
        $new->linkLabel = $label;
        return $new;
    }

    /**
     * Disables la ligne de connexion entre les items
     */
    public function withoutConnector(): self
    {
        $new = clone $this;
        $new->showConnector = false;
        return $new;
    }

    /**
     * Disables container styles (bg, border, shadow, padding, rounded)
     */
    public function unstyled(): self
    {
        $new = clone $this;
        $new->styled = false;
        return $new;
    }

    public function render(): string
    {
        $html = '';

        // Title
        if ($this->title !== null) {
            $html .= Html::tag('h3', Html::encode($this->title), ['class' => ['text-base', 'font-semibold', 'text-secondary-900', 'mb-4']])
                ->encode(false)
                ->render();
        }

        // Items list
        $html .= $this->renderItems();

        // Link
        if ($this->linkUrl !== null && $this->linkLabel !== null) {
            $linkHtml = Html::a(Html::encode($this->linkLabel) . ' →', $this->linkUrl, ['class' => $this->getLinkClasses()])
                ->encode(false)
                ->render();

            $html .= Html::div($linkHtml, ['class' => ['mt-6']])
                ->encode(false)
                ->render();
        }

        // Container
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
        if (!$this->styled) {
            return [];
        }

        $baseClasses = [
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

    private function renderItems(): string
    {
        if (empty($this->items)) {
            return '';
        }

        $listHtml = '';
        $count = count($this->items);

        foreach ($this->items as $index => $item) {
            $isLast = ($index === $count - 1);
            // If showConnector is false, never show connection line
            $hasNext = $this->showConnector && !$isLast;
            $paddingClass = $isLast ? '' : 'pb-6';

            $itemHtml = Html::div($item->renderItem($hasNext), ['class' => array_filter(['relative', $paddingClass])])
                ->encode(false)
                ->render();

            $listHtml .= Html::tag('li', $itemHtml)->encode(false)->render();
        }

        return Html::div(
            Html::tag('ul', $listHtml)->encode(false)->render(),
            ['class' => ['flow-root']]
        )->encode(false)->render();
    }

    /**
     * @return string[]
     */
    private function getLinkClasses(): array
    {
        $baseClasses = ['text-sm', 'font-medium'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:text-primary-800'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:text-secondary-800'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:text-accent-800'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:text-success-800'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'hover:text-danger-800'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'hover:text-warning-800'],
            Bleet::COLOR_INFO => ['text-info-700', 'hover:text-info-800'],
        };

        return [...$baseClasses, ...$colorClasses];
    }
}
