<?php

declare(strict_types=1);

/**
 * SidebarItem.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;

/**
 * SidebarItem - Item de navigation pour Sidebar
 *
 * Usage:
 *   Bleet::sidebarItem('Dashboard')->url('/')->outline('squares-2x2')->active()
 *   Bleet::sidebarItem('Contacts')->url('/contacts')->outline('users')
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class SidebarItem
{
    private string $label;
    private ?string $url = null;
    private ?string $iconName = null;
    private string $iconType = 'outline';
    private bool $active = false;
    private ?string $toggleId = null;
    /** @var SidebarItem[] */
    private array $children = [];

    public function __construct(string $label = '')
    {
        $this->label = $label;
    }

    /**
     * Sets the label
     */
    public function label(string $label): self
    {
        $new = clone $this;
        $new->label = $label;
        return $new;
    }

    /**
     * Sets the URL
     */
    public function url(string $url): self
    {
        $new = clone $this;
        $new->url = $url;
        return $new;
    }

    /**
     * Outline icon (default)
     */
    public function outline(string $name): self
    {
        $new = clone $this;
        $new->iconName = $name;
        $new->iconType = 'outline';
        return $new;
    }

    /**
     * Solid icon
     */
    public function solid(string $name): self
    {
        $new = clone $this;
        $new->iconName = $name;
        $new->iconType = 'solid';
        return $new;
    }

    /**
     * Marks comme actif
     */
    public function active(bool $active = true): self
    {
        $new = clone $this;
        $new->active = $active;
        return $new;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getIconName(): ?string
    {
        return $this->iconName;
    }

    public function getIconType(): string
    {
        return $this->iconType;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * Sets l'ID pour toggle (sous-menu)
     */
    public function toggleId(string $id): self
    {
        $new = clone $this;
        $new->toggleId = $id;
        return $new;
    }

    /**
     * Sets les sous-items
     * @param SidebarItem[] $children
     */
    public function children(array $children): self
    {
        $new = clone $this;
        $new->children = $children;
        return $new;
    }

    /**
     * Adds un sous-item
     */
    public function addChild(SidebarItem $child): self
    {
        $new = clone $this;
        $new->children[] = $child;
        return $new;
    }

    public function getToggleId(): ?string
    {
        return $this->toggleId;
    }

    /**
     * @return SidebarItem[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return !empty($this->children);
    }
}
