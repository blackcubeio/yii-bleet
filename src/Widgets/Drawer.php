<?php

declare(strict_types=1);

/**
 * Drawer.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Blackcube\Bleet\Traits\BleetExportableTrait;
use Yiisoft\Html\Html;

/**
 * Drawer widget - Side panel (AJAX version)
 *
 * Usage:
 *   // Trigger with URL (uses default id 'drawer')
 *   Bleet::button('Details')->attributes(Bleet::drawer()->trigger('/api/user/1'))->render()
 *
 *   // Shell (once in layout)
 *   Bleet::drawer()->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Drawer extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetExportableTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Export for EA, AJAX
     * @return array<string, mixed>
     */
    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'color' => $this->color,
        ];
    }

    /**
     * Attributs pour declencher l'ouverture du drawer
     * @param string|null $url URL AJAX a charger (optionnel)
     * @return array<string, string>
     */
    public function trigger(?string $url = null): array
    {
        $options = ['id' => $this->id, 'color' => $this->color];
        if ($url !== null) {
            $options['url'] = $url;
        }
        return ['bleet-drawer-trigger' => Aurelia::attributesCustomAttribute($options)];
    }

    /**
     * Renders the custom element (coquille vide)
     */
    public function render(): string
    {
        return Html::tag('bleet-drawer', '', $this->prepareTagAttributes(['id' => $this->id]))->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }
}
