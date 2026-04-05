<?php

declare(strict_types=1);

/**
 * Sidebar.php
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

/**
 * Sidebar widget - Responsive side navigation
 *
 * Usage:
 *   Bleet::sidebar()
 *       ->logo('BLERP')
 *       ->items([
 *           Bleet::sidebarItem('Dashboard')->url('/')->outline('squares-2x2')->active(),
 *           Bleet::sidebarItem('Contacts')->url('/contacts')->outline('users'),
 *           Bleet::sidebarItem('Settings')->url('/settings')->outline('cog-6-tooth'),
 *       ])
 *       ->render();
 *
 * Usage avec logo HTML (SVG inline) :
 *   $logoHtml = '<div class="flex items-center gap-2">' .
 *       '<div class="bg-black rounded-md text-white">' .
 *           '<svg ...>...</svg>' .
 *       '</div>' .
 *       '<span class="font-semibold text-primary-800">BLAMS</span>' .
 *   '</div>';
 *   Bleet::sidebar()
 *       ->logo($logoHtml)
 *       ->encode(false)  // Disables HTML encoding of logo
 *       ->items([...])
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Sidebar extends AbstractWidget
{
    use BleetAttributesTrait;
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string|Img|null $logo = null;
    private bool $encodeLogo = true;
    private bool $showSeparator = false;
    /** @var array<SidebarItem|array> */
    private array $items = [];

    /**
     * Sets le logo (string ou Bleet::img())
     */
    public function logo(string|Img $logo): self
    {
        $new = clone $this;
        $new->logo = $logo;
        return $new;
    }

    /**
     * Sets if logo should be encoded (true by default)
     * Utiliser encode(false) pour passer du HTML brut (SVG inline, etc.)
     */
    public function encode(bool $encode = true): self
    {
        $new = clone $this;
        $new->encodeLogo = $encode;
        return $new;
    }

    /**
     * Shows/hides a separator line between the logo and navigation
     */
    public function separator(bool $showSeparator = true): self
    {
        $new = clone $this;
        $new->showSeparator = $showSeparator;
        return $new;
    }

    /**
     * Sets les items de navigation
     * @param array<SidebarItem|array> $items
     */
    public function items(array $items): self
    {
        $new = clone $this;
        $new->items = $items;
        return $new;
    }

    /**
     * Adds un item
     */
    public function addItem(SidebarItem|array $item): self
    {
        $new = clone $this;
        $new->items[] = $item;
        return $new;
    }

    public function render(): string
    {
        return $this->renderView('sidebar', $this->prepareViewParams());
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
        // Normaliser les items
        $normalizedItems = [];
        foreach ($this->items as $item) {
            if ($item instanceof SidebarItem) {
                $normalizedItems[] = $item;
            } elseif (is_array($item)) {
                // Support format array: ['label' => '', 'url' => '', 'icon' => '', 'active' => false]
                $sidebarItem = new SidebarItem($item['label'] ?? '');
                if (isset($item['url'])) {
                    $sidebarItem = $sidebarItem->url($item['url']);
                }
                if (isset($item['icon'])) {
                    $sidebarItem = $sidebarItem->outline($item['icon']);
                }
                if (isset($item['solid'])) {
                    $sidebarItem = $sidebarItem->solid($item['solid']);
                }
                if (!empty($item['active'])) {
                    $sidebarItem = $sidebarItem->active();
                }
                $normalizedItems[] = $sidebarItem;
            }
        }

        // Add bleet-menu attribute to container for menu.ts
        $containerAttributes = $this->prepareTagAttributes();
        $containerAttributes['bleet-menu'] = '';

        return [
            'logo' => $this->logo,
            'encodeLogo' => $this->encodeLogo,
            'items' => $normalizedItems,
            'containerAttributes' => $containerAttributes,
            'color' => $this->color,
            // Aside classes
            'asideClasses' => $this->getAsideClasses(),
            // Header mobile
            'headerMobileClasses' => ['flex', 'items-center', 'justify-between', 'p-4', 'lg:hidden', ...($this->showSeparator ? ['border-b', ...$this->getBorderColorClasses()] : [])],
            'closeButtonClasses' => ['-m-2.5', 'p-2.5', 'cursor-pointer', ...$this->getCloseButtonColorClasses()],
            // Header desktop
            'headerDesktopClasses' => ['hidden', 'lg:flex', 'items-center', 'p-4', ...($this->showSeparator ? ['border-b', ...$this->getBorderColorClasses()] : [])],
            // Logo
            'logoClasses' => $this->getLogoClasses(),
            // Nav
            'navClasses' => ['p-4'],
            'ulClasses' => ['space-y-1'],
            // Item
            'itemBaseClasses' => ['flex', 'items-center', 'gap-3', 'px-3', 'py-2', 'text-sm', 'font-medium', 'rounded-md'],
            'itemActiveClasses' => $this->getItemActiveClasses(),
            'itemInactiveClasses' => $this->getItemInactiveClasses(),
            'iconClasses' => ['size-5'],
            // Toggle button (sous-menu)
            'toggleButtonClasses' => $this->getToggleButtonClasses(),
            'toggleIconClasses' => ['size-4', 'transition-transform', 'duration-200'],
            // Sublist
            'sublistClasses' => ['hidden', 'mt-1', 'ml-4', 'space-y-1'],
            'subItemClasses' => $this->getSubItemClasses(),
        ];
    }

    /**
     * @return string[]
     */
    private function getAsideClasses(): array
    {
        $baseClasses = [
            'fixed', 'inset-y-0', 'left-0', 'z-50', 'w-64', 'bg-white',
            'border-r', 'overflow-y-auto', 'transform', '-translate-x-full',
            'lg:translate-x-0', 'transition-transform', 'duration-300', 'ease-in-out',
        ];
        return [...$baseClasses, ...$this->getBorderColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getBorderColorClasses(): array
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
    private function getCloseButtonColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:text-primary-900', 'focus-visible:ring-2', 'focus-visible:ring-offset-2', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:text-secondary-900', 'focus-visible:ring-2', 'focus-visible:ring-offset-2', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:text-success-900', 'focus-visible:ring-2', 'focus-visible:ring-offset-2', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'hover:text-danger-900', 'focus-visible:ring-2', 'focus-visible:ring-offset-2', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'hover:text-warning-900', 'focus-visible:ring-2', 'focus-visible:ring-offset-2', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-700', 'hover:text-info-900', 'focus-visible:ring-2', 'focus-visible:ring-offset-2', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:text-accent-900', 'focus-visible:ring-2', 'focus-visible:ring-offset-2', 'focus-visible:ring-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getLogoClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600', 'text-white', 'px-4', 'py-2', 'rounded-lg', 'text-xl', 'font-bold'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-600', 'text-white', 'px-4', 'py-2', 'rounded-lg', 'text-xl', 'font-bold'],
            Bleet::COLOR_SUCCESS => ['bg-success-600', 'text-white', 'px-4', 'py-2', 'rounded-lg', 'text-xl', 'font-bold'],
            Bleet::COLOR_DANGER => ['bg-danger-600', 'text-white', 'px-4', 'py-2', 'rounded-lg', 'text-xl', 'font-bold'],
            Bleet::COLOR_WARNING => ['bg-warning-600', 'text-white', 'px-4', 'py-2', 'rounded-lg', 'text-xl', 'font-bold'],
            Bleet::COLOR_INFO => ['bg-info-600', 'text-white', 'px-4', 'py-2', 'rounded-lg', 'text-xl', 'font-bold'],
            Bleet::COLOR_ACCENT => ['bg-accent-600', 'text-white', 'px-4', 'py-2', 'rounded-lg', 'text-xl', 'font-bold'],
        };
    }

    /**
     * @return string[]
     */
    private function getItemActiveClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900', 'bg-primary-100', 'border-l-4', 'border-primary-600', 'hover:bg-primary-50'],
            Bleet::COLOR_SECONDARY => ['text-secondary-900', 'bg-secondary-100', 'border-l-4', 'border-secondary-600', 'hover:bg-secondary-50'],
            Bleet::COLOR_SUCCESS => ['text-success-900', 'bg-success-100', 'border-l-4', 'border-success-600', 'hover:bg-success-50'],
            Bleet::COLOR_DANGER => ['text-danger-900', 'bg-danger-100', 'border-l-4', 'border-danger-600', 'hover:bg-danger-50'],
            Bleet::COLOR_WARNING => ['text-warning-900', 'bg-warning-100', 'border-l-4', 'border-warning-600', 'hover:bg-warning-50'],
            Bleet::COLOR_INFO => ['text-info-900', 'bg-info-100', 'border-l-4', 'border-info-600', 'hover:bg-info-50'],
            Bleet::COLOR_ACCENT => ['text-accent-900', 'bg-accent-100', 'border-l-4', 'border-accent-600', 'hover:bg-accent-50'],
        };
    }

    /**
     * @return string[]
     */
    private function getItemInactiveClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-secondary-700', 'hover:bg-primary-50', 'hover:text-primary-900'],
            Bleet::COLOR_SECONDARY => ['text-primary-700', 'hover:bg-secondary-50', 'hover:text-secondary-900'],
            Bleet::COLOR_SUCCESS => ['text-secondary-700', 'hover:bg-success-50', 'hover:text-success-900'],
            Bleet::COLOR_DANGER => ['text-secondary-700', 'hover:bg-danger-50', 'hover:text-danger-900'],
            Bleet::COLOR_WARNING => ['text-secondary-700', 'hover:bg-warning-50', 'hover:text-warning-900'],
            Bleet::COLOR_INFO => ['text-secondary-700', 'hover:bg-info-50', 'hover:text-info-900'],
            Bleet::COLOR_ACCENT => ['text-secondary-700', 'hover:bg-accent-50', 'hover:text-accent-900'],
        };
    }

    /**
     * @return string[]
     */
    private function getToggleButtonClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['w-full', 'flex', 'items-center', 'justify-between', 'gap-3', 'px-3', 'py-2', 'text-sm', 'font-medium', 'text-secondary-700', 'rounded-md', 'hover:bg-primary-50', 'hover:text-primary-900', 'cursor-pointer'],
            Bleet::COLOR_SECONDARY => ['w-full', 'flex', 'items-center', 'justify-between', 'gap-3', 'px-3', 'py-2', 'text-sm', 'font-medium', 'text-primary-700', 'rounded-md', 'hover:bg-secondary-50', 'hover:text-secondary-900', 'cursor-pointer'],
            Bleet::COLOR_SUCCESS => ['w-full', 'flex', 'items-center', 'justify-between', 'gap-3', 'px-3', 'py-2', 'text-sm', 'font-medium', 'text-secondary-700', 'rounded-md', 'hover:bg-success-50', 'hover:text-success-900', 'cursor-pointer'],
            Bleet::COLOR_DANGER => ['w-full', 'flex', 'items-center', 'justify-between', 'gap-3', 'px-3', 'py-2', 'text-sm', 'font-medium', 'text-secondary-700', 'rounded-md', 'hover:bg-danger-50', 'hover:text-danger-900', 'cursor-pointer'],
            Bleet::COLOR_WARNING => ['w-full', 'flex', 'items-center', 'justify-between', 'gap-3', 'px-3', 'py-2', 'text-sm', 'font-medium', 'text-secondary-700', 'rounded-md', 'hover:bg-warning-50', 'hover:text-warning-900', 'cursor-pointer'],
            Bleet::COLOR_INFO => ['w-full', 'flex', 'items-center', 'justify-between', 'gap-3', 'px-3', 'py-2', 'text-sm', 'font-medium', 'text-secondary-700', 'rounded-md', 'hover:bg-info-50', 'hover:text-info-900', 'cursor-pointer'],
            Bleet::COLOR_ACCENT => ['w-full', 'flex', 'items-center', 'justify-between', 'gap-3', 'px-3', 'py-2', 'text-sm', 'font-medium', 'text-secondary-700', 'rounded-md', 'hover:bg-accent-50', 'hover:text-accent-900', 'cursor-pointer'],
        };
    }

    /**
     * @return string[]
     */
    private function getSubItemClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['block', 'px-4', 'py-2', 'text-sm', 'rounded-lg', 'hover:bg-primary-50', 'text-secondary-600', 'hover:text-primary-700'],
            Bleet::COLOR_SECONDARY => ['block', 'px-4', 'py-2', 'text-sm', 'rounded-lg', 'hover:bg-secondary-50', 'text-primary-600', 'hover:text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['block', 'px-4', 'py-2', 'text-sm', 'rounded-lg', 'hover:bg-success-50', 'text-secondary-600', 'hover:text-success-700'],
            Bleet::COLOR_DANGER => ['block', 'px-4', 'py-2', 'text-sm', 'rounded-lg', 'hover:bg-danger-50', 'text-secondary-600', 'hover:text-danger-700'],
            Bleet::COLOR_WARNING => ['block', 'px-4', 'py-2', 'text-sm', 'rounded-lg', 'hover:bg-warning-50', 'text-secondary-600', 'hover:text-warning-700'],
            Bleet::COLOR_INFO => ['block', 'px-4', 'py-2', 'text-sm', 'rounded-lg', 'hover:bg-info-50', 'text-secondary-600', 'hover:text-info-700'],
            Bleet::COLOR_ACCENT => ['block', 'px-4', 'py-2', 'text-sm', 'rounded-lg', 'hover:bg-accent-50', 'text-secondary-600', 'hover:text-accent-700'],
        };
    }
}
