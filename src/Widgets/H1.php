<?php

declare(strict_types=1);

/**
 * H1.php
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
 * H1 widget - Page header with gradient background
 *
 * Usage:
 *   Bleet::h1('Mon titre')->render();
 *   Bleet::h1('Mon titre')->subtitle('Description')->render();
 *   Bleet::h1('Mon titre')
 *       ->subtitle('Description')
 *       ->primaryCta('Commencer', '/start')
 *       ->secondaryCta('En savoir plus', '/about')
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class H1 extends AbstractWidget
{
    use BleetAttributesTrait;
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $title = '';
    private ?string $subtitle = null;
    private ?array $primaryCta = null;
    private ?array $secondaryCta = null;

    public function __construct(string $title = '')
    {
        $this->title = $title;
    }

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
     * Sets le sous-titre
     */
    public function subtitle(string $subtitle): self
    {
        $new = clone $this;
        $new->subtitle = $subtitle;
        return $new;
    }

    /**
     * Sets le CTA principal (bouton blanc)
     */
    public function primaryCta(string $label, ?string $url = null): self
    {
        $new = clone $this;
        $new->primaryCta = ['label' => $label, 'url' => $url];
        return $new;
    }

    /**
     * Sets the secondary CTA (colored button)
     */
    public function secondaryCta(string $label, ?string $url = null): self
    {
        $new = clone $this;
        $new->secondaryCta = ['label' => $label, 'url' => $url];
        return $new;
    }

    public function render(): string
    {
        $containerAttributes = $this->prepareTagAttributes();
        Html::addCssClass($containerAttributes, $this->getContainerClasses());

        return $this->renderView('h1', [
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'primaryCta' => $this->primaryCta,
            'secondaryCta' => $this->secondaryCta,
            'containerAttributes' => $containerAttributes,
            'contentClasses' => ['max-w-4xl', 'mx-auto', 'text-center'],
            'titleClasses' => ['text-3xl', 'font-bold', 'text-white', 'sm:text-4xl', 'lg:text-5xl'],
            'subtitleClasses' => $this->getSubtitleClasses(),
            'ctaContainerClasses' => ['mt-8', 'flex', 'flex-col', 'sm:flex-row', 'items-center', 'justify-center', 'gap-4'],
            'primaryCtaClasses' => $this->getPrimaryCtaClasses(),
            'secondaryCtaClasses' => $this->getSecondaryCtaClasses(),
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
     * @return string[]
     */
    private function getContainerClasses(): array
    {
        $baseClasses = ['bg-gradient-to-r', 'rounded-lg', 'px-6', 'py-12', 'sm:px-8', 'sm:py-16', 'lg:px-12', 'lg:py-20', 'mb-8'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['from-primary-800', 'to-primary-700'],
            Bleet::COLOR_SECONDARY => ['from-secondary-800', 'to-secondary-700'],
            Bleet::COLOR_SUCCESS => ['from-success-800', 'to-success-700'],
            Bleet::COLOR_DANGER => ['from-danger-800', 'to-danger-700'],
            Bleet::COLOR_WARNING => ['from-warning-800', 'to-warning-700'],
            Bleet::COLOR_INFO => ['from-info-800', 'to-info-700'],
            Bleet::COLOR_ACCENT => ['from-accent-800', 'to-accent-700'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getSubtitleClasses(): array
    {
        $baseClasses = ['mt-4', 'text-lg', 'sm:text-xl'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-100'],
            Bleet::COLOR_SECONDARY => ['text-secondary-100'],
            Bleet::COLOR_SUCCESS => ['text-success-100'],
            Bleet::COLOR_DANGER => ['text-danger-100'],
            Bleet::COLOR_WARNING => ['text-warning-100'],
            Bleet::COLOR_INFO => ['text-info-100'],
            Bleet::COLOR_ACCENT => ['text-accent-100'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getPrimaryCtaClasses(): array
    {
        $baseClasses = ['rounded-md', 'bg-white', 'px-4', 'py-3', 'text-base', 'font-semibold', 'shadow-xs', 'focus-visible:ring-2', 'focus-visible:ring-offset-2', 'focus-visible:ring-white'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:bg-primary-50'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:bg-secondary-50'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:bg-success-50'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'hover:bg-danger-50'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'hover:bg-warning-50'],
            Bleet::COLOR_INFO => ['text-info-700', 'hover:bg-info-50'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:bg-accent-50'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getSecondaryCtaClasses(): array
    {
        $baseClasses = ['rounded-md', 'px-4', 'py-3', 'text-base', 'font-semibold', 'text-white', 'shadow-xs', 'focus-visible:ring-2', 'focus-visible:ring-offset-2'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600', 'hover:bg-primary-700', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-600', 'hover:bg-secondary-700', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-600', 'hover:bg-success-700', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['bg-danger-600', 'hover:bg-danger-700', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['bg-warning-600', 'hover:bg-warning-700', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['bg-info-600', 'hover:bg-info-700', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['bg-accent-600', 'hover:bg-accent-700', 'focus-visible:ring-accent-600'],
        };
        return [...$baseClasses, ...$colorClasses];
    }
}
