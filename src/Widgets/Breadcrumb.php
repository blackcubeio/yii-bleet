<?php

declare(strict_types=1);

/**
 * Breadcrumb.php
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
 * Breadcrumb widget - Fil d'Ariane
 *
 * Usage:
 *   Bleet::breadcrumb()
 *       ->home('/', 'Accueil')
 *       ->items([
 *           ['label' => 'Projets', 'url' => '/projets'],
 *           ['label' => 'Project Nero'],
 *       ])
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Breadcrumb extends AbstractWidget
{
    use BleetAttributesTrait;
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private ?string $homeUrl = null;
    private string $homeLabel = 'Accueil';
    private string $homeIcon = 'home';
    private array $items = [];
    private bool $showBorder = true;

    /**
     * Sets the home element
     */
    public function home(?string $url = null, string $label = 'Accueil', string $icon = 'home'): self
    {
        $new = clone $this;
        $new->homeUrl = $url;
        $new->homeLabel = $label;
        $new->homeIcon = $icon;
        return $new;
    }

    /**
     * Sets les items du breadcrumb
     * @param array<array{label: string, url?: string}> $items
     */
    public function items(array $items): self
    {
        $new = clone $this;
        $new->items = $items;
        return $new;
    }

    /**
     * Adds un item au breadcrumb
     */
    public function addItem(string $label, ?string $url = null): self
    {
        $new = clone $this;
        $item = ['label' => $label];
        if ($url !== null) {
            $item['url'] = $url;
        }
        $new->items[] = $item;
        return $new;
    }

    /**
     * Shows the bottom border (with SVG separator)
     */
    public function withBorder(): self
    {
        $new = clone $this;
        $new->showBorder = true;
        return $new;
    }

    /**
     * Hides the bottom border (chevron separator)
     */
    public function withoutBorder(): self
    {
        $new = clone $this;
        $new->showBorder = false;
        return $new;
    }

    public function render(): string
    {
        return $this->renderView('breadcrumb', $this->prepareViewParams());
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    private function prepareViewParams(): array
    {
        // Nav classes
        $navClasses = ['flex', 'bg-white'];
        if ($this->showBorder) {
            $navClasses = [...$navClasses, 'border-b', ...$this->getNavBorderColorClasses()];
        }

        $navAttributes = $this->prepareTagAttributes(['aria-label' => 'Breadcrumb']);
        Html::addCssClass($navAttributes, $navClasses);

        // Separator classes
        $separatorClasses = $this->showBorder
            ? ['h-full', 'w-3', 'shrink-0', ...$this->getSeparatorBorderColorClasses()]
            : ['size-4', 'shrink-0', 'my-3', ...$this->getSeparatorChevronColorClasses()];

        $separatorAttributes = ['class' => implode(' ', $separatorClasses)];
        if ($this->showBorder) {
            $separatorAttributes['preserveAspectRatio'] = 'none';
        }

        return [
            'homeUrl' => $this->homeUrl,
            'homeLabel' => $this->homeLabel,
            'homeIcon' => $this->homeIcon,
            'items' => $this->items,
            'showBorder' => $this->showBorder,
            'navAttributes' => $navAttributes,
            'olClasses' => ['mx-auto', 'flex', 'w-full', 'max-w-screen-xl', 'space-x-4', 'px-4', 'sm:px-6', 'lg:px-8'],
            'homeItemClasses' => ['flex'],
            'homeWrapperClasses' => ['flex', 'items-center'],
            'homeLinkClasses' => $this->homeUrl !== null ? $this->getHomeLinkColorClasses() : null,
            'homeSpanClasses' => $this->homeUrl === null ? ['text-secondary-700'] : null,
            'homeIconClasses' => ['size-5', 'shrink-0'],
            'itemClasses' => ['flex'],
            'itemWrapperClasses' => ['flex', 'items-center'],
            'separatorAttributes' => $separatorAttributes,
            'itemLinkClasses' => ['ml-4', 'text-sm', 'font-medium', ...$this->getItemLinkColorClasses()],
            'lastItemClasses' => ['ml-4', 'text-sm', 'font-medium', ...$this->getLastItemColorClasses()],
            'disabledItemClasses' => ['ml-4', 'text-sm', 'font-medium', 'text-secondary-500'],
        ];
    }

    /**
     * @return string[]
     */
    private function getNavBorderColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-200'],
            Bleet::COLOR_SECONDARY => ['border-secondary-200'],
            Bleet::COLOR_SUCCESS => ['border-success-200'],
            Bleet::COLOR_DANGER => ['border-danger-200'],
            Bleet::COLOR_WARNING => ['border-warning-200'],
            Bleet::COLOR_INFO => ['border-info-200'],
            Bleet::COLOR_ACCENT => ['border-accent-200'],
        };
    }

    /**
     * @return string[]
     */
    private function getHomeLinkColorClasses(): array
    {
        $baseClasses = ['text-secondary-700'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['hover:text-primary-700'],
            Bleet::COLOR_SECONDARY => ['hover:text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['hover:text-success-700'],
            Bleet::COLOR_DANGER => ['hover:text-danger-700'],
            Bleet::COLOR_WARNING => ['hover:text-warning-700'],
            Bleet::COLOR_INFO => ['hover:text-info-700'],
            Bleet::COLOR_ACCENT => ['hover:text-accent-700'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getSeparatorBorderColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-200'],
            Bleet::COLOR_SECONDARY => ['text-secondary-200'],
            Bleet::COLOR_SUCCESS => ['text-success-200'],
            Bleet::COLOR_DANGER => ['text-danger-200'],
            Bleet::COLOR_WARNING => ['text-warning-200'],
            Bleet::COLOR_INFO => ['text-info-200'],
            Bleet::COLOR_ACCENT => ['text-accent-200'],
        };
    }

    /**
     * @return string[]
     */
    private function getSeparatorChevronColorClasses(): array
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
    private function getItemLinkColorClasses(): array
    {
        $baseClasses = ['text-secondary-700'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['hover:text-primary-700'],
            Bleet::COLOR_SECONDARY => ['hover:text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['hover:text-success-700'],
            Bleet::COLOR_DANGER => ['hover:text-danger-700'],
            Bleet::COLOR_WARNING => ['hover:text-warning-700'],
            Bleet::COLOR_INFO => ['hover:text-info-700'],
            Bleet::COLOR_ACCENT => ['hover:text-accent-700'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getLastItemColorClasses(): array
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
}
