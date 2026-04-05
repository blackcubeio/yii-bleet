<?php

declare(strict_types=1);

/**
 * ActivityItem.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

/**
 * ActivityItem widget - Single item in an ActivityFeed
 *
 * Usage:
 *   Bleet::activityItem('Order #1234 validated')->icon('check')->success()->timestamp('2 minutes ago')->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ActivityItem extends AbstractWidget
{
    protected string $color = Bleet::COLOR_PRIMARY;

    private string $content = '';
    private ?string $icon = null;
    private ?string $timestamp = null;
    private bool $encode = true;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Sets the content
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->content = $content;
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
     * Sets the timestamp
     */
    public function timestamp(string $timestamp): self
    {
        $new = clone $this;
        $new->timestamp = $timestamp;
        return $new;
    }

    /**
     * Disables HTML encoding of the content
     */
    public function encode(bool $encode = true): self
    {
        $new = clone $this;
        $new->encode = $encode;
        return $new;
    }

    /**
     * Returns the content (pour ActivityFeed)
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns the icon (pour ActivityFeed)
     */
    public function getIcon(): ?string
    {
        return $this->icon;
    }

    /**
     * Returns the timestamp (pour ActivityFeed)
     */
    public function getTimestamp(): ?string
    {
        return $this->timestamp;
    }

    /**
     * Returns la couleur (pour ActivityFeed)
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Returns si l'encodage est actif (pour ActivityFeed)
     */
    public function isEncoded(): bool
    {
        return $this->encode;
    }

    /**
     * Renders the formatted content
     */
    public function renderContent(): string
    {
        return $this->encode ? Html::encode($this->content) : $this->content;
    }

    public function render(): string
    {
        // Standalone render (without feed context)
        return $this->renderItem(false);
    }

    /**
     * Renders item with or without connection line
     */
    public function renderItem(bool $hasNext): string
    {
        $html = '';

        // Connection line (if not last)
        if ($hasNext) {
            $html .= Html::tag('span', '', [
                'class' => ['absolute', 'top-4', 'left-4', '-ml-px', 'h-full', 'w-0.5', 'bg-secondary-200'],
                'aria-hidden' => 'true',
            ])->render();
        }

        // Content flex
        $flexContent = '';

        // Icon
        $iconHtml = $this->icon !== null
            ? Bleet::svg()->outline($this->icon)->addClass('w-4', 'h-4', 'text-white')
            : '';

        $iconWrapper = Html::tag('span', $iconHtml, ['class' => $this->getIconWrapperClasses()])
            ->encode(false)
            ->render();

        $flexContent .= Html::div($iconWrapper)->encode(false)->render();

        // Text content
        $textHtml = Html::tag('p', $this->renderContent(), ['class' => ['text-sm', 'text-secondary-900']])
            ->encode(false)
            ->render();

        if ($this->timestamp !== null) {
            $textHtml .= Html::tag('p', Html::encode($this->timestamp), ['class' => ['text-xs', 'text-secondary-500', 'mt-0.5']])
                ->encode(false)
                ->render();
        }

        $flexContent .= Html::div($textHtml, ['class' => ['flex-1', 'min-w-0']])
            ->encode(false)
            ->render();

        $html .= Html::div($flexContent, ['class' => ['relative', 'flex', 'space-x-3']])
            ->encode(false)
            ->render();

        return $html;
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    private function getIconWrapperClasses(): array
    {
        $baseClasses = ['h-8', 'w-8', 'rounded-full', 'flex', 'items-center', 'justify-center', 'ring-8', 'ring-white'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-600'],
            Bleet::COLOR_DANGER => ['bg-danger-600'],
            Bleet::COLOR_WARNING => ['bg-warning-600'],
            Bleet::COLOR_INFO => ['bg-info-600'],
            Bleet::COLOR_ACCENT => ['bg-accent-600'],
        };

        return [...$baseClasses, ...$colorClasses];
    }
}
