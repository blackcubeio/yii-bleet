<?php

declare(strict_types=1);

/**
 * Tab.php
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
use RuntimeException;

/**
 * Tab widget - Tab container with automatic menu generation
 *
 * Usage:
 *   <?php Tab::widget()->primary()->begin() ?>
 *       <?php TabPanel::begin('Profil', active: true) ?>
 *           Content of the first tab
 *       <?php TabPanel::end() ?>
 *       <?php TabPanel::begin('Settings') ?>
 *           Content of the second tab
 *       <?php TabPanel::end() ?>
 *   <?php echo Tab::end() ?>
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Tab extends AbstractWidget
{
    use BleetAttributesTrait;
    use RenderViewTrait;

    private static ?Tab $currentInstance = null;

    protected string $color = Bleet::COLOR_PRIMARY;

    /** @var TabPanel[] */
    private array $panels = [];

    public static function getCurrentInstance(): ?Tab
    {
        return self::$currentInstance;
    }

    /**
     * Starts the Tab widget
     */
    public function begin(): ?string
    {
        if (self::$currentInstance !== null) {
            throw new RuntimeException('Cannot nest Tab instances');
        }

        self::$currentInstance = $this;
        parent::begin();
        return null;
    }

    /**
     * Registers a TabPanel
     */
    public function addPanel(TabPanel $panel): void
    {
        $this->panels[] = $panel;
    }

    public function render(): string
    {
        self::$currentInstance = null;

        if (empty($this->panels)) {
            throw new RuntimeException('Tab must contain at least one TabPanel');
        }

        // If no panel is active, activate the first one
        $hasActivePanel = false;
        foreach ($this->panels as $panel) {
            if ($panel->isActive()) {
                $hasActivePanel = true;
                break;
            }
        }

        if (!$hasActivePanel) {
            $this->panels[0]->setActive(true);
        }

        return $this->renderView('tabs', $this->prepareViewParams());
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
        // Container
        $containerAttributes = $this->prepareTagAttributes();
        $containerAttributes['bleet-tabs'] = '';

        return [
            'panels' => $this->panels,
            'containerAttributes' => $containerAttributes,
            // Mobile
            'mobileContainerClasses' => ['lg:hidden'],
            'mobileSelectClasses' => [
                'block', 'w-full', 'rounded-md', 'border-0', 'py-1.5', 'pl-3', 'pr-10',
                'ring-1', 'ring-inset', 'focus:ring-2', 'text-sm', 'leading-6',
                ...$this->getMobileSelectColorClasses(),
            ],
            // Desktop
            'desktopContainerClasses' => ['hidden', 'lg:block'],
            'desktopBorderClasses' => ['border-b', ...$this->getDesktopBorderColorClasses()],
            'desktopNavClasses' => ['-mb-px', 'flex', 'space-x-8'],
            // Tab
            'tabBaseClasses' => ['flex', 'items-center', 'gap-x-3', 'border-b-2', 'px-1', 'py-4', 'text-sm', 'font-medium', 'whitespace-nowrap', 'cursor-pointer'],
            'tabInactiveClasses' => ['border-transparent', 'text-secondary-500', 'hover:text-secondary-700', ...$this->getTabInactiveColorClasses()],
            'tabActiveClasses' => $this->getTabActiveColorClasses(),
            // Badge
            'badgeClasses' => [
                'hidden', 'lg:inline-block', 'rounded-full', 'px-2', 'py-1',
                'text-xs', 'font-medium', 'ring-1', 'ring-inset',
                ...$this->getBadgeColorClasses(),
            ],
            // Panels
            'panelsContainerClasses' => ['mt-4'],
        ];
    }

    /**
     * @return string[]
     */
    private function getMobileSelectColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'ring-primary-300', 'focus:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'ring-secondary-300', 'focus:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'ring-success-300', 'focus:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'ring-danger-300', 'focus:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'ring-warning-300', 'focus:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-700', 'ring-info-300', 'focus:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'ring-accent-300', 'focus:ring-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getDesktopBorderColorClasses(): array
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
    private function getTabInactiveColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['hover:border-primary-300'],
            Bleet::COLOR_SECONDARY => ['hover:border-secondary-300'],
            Bleet::COLOR_SUCCESS => ['hover:border-success-300'],
            Bleet::COLOR_DANGER => ['hover:border-danger-300'],
            Bleet::COLOR_WARNING => ['hover:border-warning-300'],
            Bleet::COLOR_INFO => ['hover:border-info-300'],
            Bleet::COLOR_ACCENT => ['hover:border-accent-300'],
        };
    }

    /**
     * @return string[]
     */
    private function getTabActiveColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-600', 'text-primary-700'],
            Bleet::COLOR_SECONDARY => ['border-secondary-600', 'text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['border-success-600', 'text-success-700'],
            Bleet::COLOR_DANGER => ['border-danger-600', 'text-danger-700'],
            Bleet::COLOR_WARNING => ['border-warning-600', 'text-warning-700'],
            Bleet::COLOR_INFO => ['border-info-600', 'text-info-700'],
            Bleet::COLOR_ACCENT => ['border-accent-600', 'text-accent-700'],
        };
    }

    /**
     * @return string[]
     */
    private function getBadgeColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-50', 'text-primary-700', 'ring-primary-500/10'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50', 'text-secondary-700', 'ring-secondary-500/10'],
            Bleet::COLOR_SUCCESS => ['bg-success-50', 'text-success-700', 'ring-success-500/10'],
            Bleet::COLOR_DANGER => ['bg-danger-50', 'text-danger-700', 'ring-danger-500/10'],
            Bleet::COLOR_WARNING => ['bg-warning-50', 'text-warning-700', 'ring-warning-500/10'],
            Bleet::COLOR_INFO => ['bg-info-50', 'text-info-700', 'ring-info-500/10'],
            Bleet::COLOR_ACCENT => ['bg-accent-50', 'text-accent-700', 'ring-accent-500/10'],
        };
    }
}
