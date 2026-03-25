<?php

declare(strict_types=1);

/**
 * EmptyState.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

/**
 * EmptyState widget (placeholder for empty content with CTA)
 *
 * Usage:
 *   Bleet::emptyState()
 *       ->icon('document-plus')
 *       ->title('Aucun contact')
 *       ->description('Start by creating a new contact.')
 *       ->button('Nouveau contact', '/contacts/create')
 *       ->primary()
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class EmptyState extends Card
{
    private ?string $icon = null;
    private ?string $buttonLabel = null;
    private ?string $buttonUrl = null;

    /**
     * Sets the icon (heroicon outline)
     */
    public function icon(string $icon): self
    {
        $new = clone $this;
        $new->icon = $icon;
        return $new;
    }

    /**
     * Sets the button CTA
     */
    public function button(string $label, string $url): self
    {
        $new = clone $this;
        $new->buttonLabel = $label;
        $new->buttonUrl = $url;
        return $new;
    }

    public function render(): string
    {
        $innerHtml = '';

        // Icon
        if ($this->icon !== null) {
            $innerHtml .= $this->renderIcon();
        }

        // Title
        if ($this->title !== null) {
            $innerHtml .= Html::tag('h3', $this->title)
                ->class('mt-2', 'text-sm', 'font-medium', ...$this->getTitleColorClasses())
                ->render();
        }

        // Description
        if ($this->description !== null) {
            $innerHtml .= Html::tag('p', $this->description)
                ->class('mt-1', 'text-sm', 'text-secondary-600')
                ->render();
        }

        // Button
        if ($this->buttonLabel !== null) {
            $innerHtml .= $this->renderButton();
        }

        // Center container
        $centerHtml = Html::div($innerHtml)
            ->encode(false)
            ->class('text-center', 'py-12')
            ->render();

        // Container
        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::div($centerHtml)
            ->attributes($attributes)
            ->encode(false)
            ->render();
    }

    private function renderIcon(): string
    {
        $iconColorClasses = $this->getIconColorClasses();

        return Bleet::svg()
            ->outline($this->icon)
            ->addClass('mx-auto', 'h-12', 'w-12', ...$iconColorClasses)
            ->render();
    }

    private function renderButton(): string
    {
        // Icon + in the button
        $plusIcon = Bleet::svg()
            ->mini('plus')
            ->addClass('-ml-0.5', 'size-5')
            ->render();

        $buttonClasses = [
            'inline-flex',
            'items-center',
            'gap-x-1.5',
            ...$this->getButtonClasses(),
        ];

        $buttonHtml = Html::a($plusIcon . $this->buttonLabel, $this->buttonUrl)
            ->encode(false)
            ->class(...$buttonClasses)
            ->render();

        return Html::div($buttonHtml)
            ->encode(false)
            ->class('mt-6')
            ->render();
    }

    /**
     * @return string[]
     */
    private function getIconColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-500'],
            Bleet::COLOR_SECONDARY => ['text-secondary-500'],
            Bleet::COLOR_SUCCESS => ['text-success-500'],
            Bleet::COLOR_DANGER => ['text-danger-500'],
            Bleet::COLOR_WARNING => ['text-warning-500'],
            Bleet::COLOR_INFO => ['text-info-500'],
            Bleet::COLOR_ACCENT => ['text-accent-500'],
        };
    }

    /**
     * @return string[]
     */
    private function getTitleColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900'],
            Bleet::COLOR_SECONDARY => ['text-secondary-900'],
            Bleet::COLOR_SUCCESS => ['text-success-900'],
            Bleet::COLOR_DANGER => ['text-danger-900'],
            Bleet::COLOR_WARNING => ['text-warning-900'],
            Bleet::COLOR_INFO => ['text-info-900'],
            Bleet::COLOR_ACCENT => ['text-accent-900'],
        };
    }
}
