<?php

declare(strict_types=1);

/**
 * TabPanel.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use RuntimeException;

/**
 * TabPanel widget - Panneau de contenu pour Tab
 *
 * Registers with parent Tab and captures its content.
 * Rendering is handled by Tab.
 *
 * Usage:
 *   <?php TabPanel::begin('Mon onglet', active: true) ?>
 *       Contenu du panneau
 *   <?php TabPanel::end() ?>
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class TabPanel
{
    private static ?TabPanel $currentInstance = null;

    private string $label;
    private bool $active;
    private ?string $badge;
    private string $content = '';

    private function __construct(string $label, bool $active = false, ?string $badge = null)
    {
        $this->label = $label;
        $this->active = $active;
        $this->badge = $badge;
    }

    /**
     * Starts a panel
     */
    public static function begin(string $label, bool $active = false, ?string $badge = null): void
    {
        if (self::$currentInstance !== null) {
            throw new RuntimeException('Cannot nest TabPanel instances');
        }

        $tabWidget = Tab::getCurrentInstance();
        if ($tabWidget === null) {
            throw new RuntimeException('TabPanel must be used inside Tab::begin() / Tab::end()');
        }

        $instance = new self($label, $active, $badge);
        self::$currentInstance = $instance;

        $tabWidget->addPanel($instance);

        ob_start();
    }

    /**
     * Termine le panneau et capture the content
     */
    public static function end(): void
    {
        if (self::$currentInstance === null) {
            throw new RuntimeException('TabPanel::end() called without matching TabPanel::begin()');
        }

        self::$currentInstance->content = ob_get_clean();
        self::$currentInstance = null;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getBadge(): ?string
    {
        return $this->badge;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
