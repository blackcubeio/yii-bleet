<?php

declare(strict_types=1);

/**
 * ListItem.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use InvalidArgumentException;

/**
 * ListItem widget for list items with optional icons
 *
 * Usage:
 *   Bleet::listItem('Texte')->solid('check-circle')->success()->render();
 *   Bleet::listItem('Texte')->outline('home')->primary()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ListItem
{
    private string $content;
    private ?string $iconName = null;
    private string $iconType = 'solid';
    private string $iconSize = 'normal';
    private ?string $color = null;
    private bool $encode = true;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Sets the content texte
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Solid icon (24x24)
     */
    public function solid(string $name): self
    {
        $new = clone $this;
        $new->iconName = $name;
        $new->iconType = 'solid';
        $new->iconSize = 'normal';
        return $new;
    }

    /**
     * Outline icon (24x24)
     */
    public function outline(string $name): self
    {
        $new = clone $this;
        $new->iconName = $name;
        $new->iconType = 'outline';
        $new->iconSize = 'normal';
        return $new;
    }

    /**
     * Mini icon (20x20)
     */
    public function mini(string $name): self
    {
        $new = clone $this;
        $new->iconName = $name;
        $new->iconType = 'solid';
        $new->iconSize = 'mini';
        return $new;
    }

    /**
     * Micro icon (16x16)
     */
    public function micro(string $name): self
    {
        $new = clone $this;
        $new->iconName = $name;
        $new->iconType = 'solid';
        $new->iconSize = 'micro';
        return $new;
    }

    /**
     * Sets la couleur de the icon
     */
    public function color(string $color): self
    {
        if (!in_array($color, Bleet::COLORS, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid color "%s". Valid: %s', $color, implode(', ', Bleet::COLORS))
            );
        }

        $new = clone $this;
        $new->color = $color;
        return $new;
    }

    // Raccourcis couleurs
    public function primary(): self
    {
        return $this->color(Bleet::COLOR_PRIMARY);
    }

    public function secondary(): self
    {
        return $this->color(Bleet::COLOR_SECONDARY);
    }

    public function success(): self
    {
        return $this->color(Bleet::COLOR_SUCCESS);
    }

    public function danger(): self
    {
        return $this->color(Bleet::COLOR_DANGER);
    }

    public function warning(): self
    {
        return $this->color(Bleet::COLOR_WARNING);
    }

    public function info(): self
    {
        return $this->color(Bleet::COLOR_INFO);
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
     * Indicates if item has an icon
     */
    public function hasIcon(): bool
    {
        return $this->iconName !== null;
    }

    /**
     * Returns the content texte
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns si l'encodage est actif
     */
    public function isEncoded(): bool
    {
        return $this->encode;
    }

    /**
     * Generates li content (icon + text)
     *
     * @param string $parentColor Color inherited from parent (ul/ol)
     */
    public function renderContent(string $parentColor): string
    {
        $content = $this->encode ? htmlspecialchars($this->content, ENT_QUOTES | ENT_HTML5) : $this->content;

        if ($this->iconName === null) {
            return $content;
        }

        $iconColor = $this->color ?? $parentColor;

        $svg = Bleet::svg();

        $svg = match ($this->iconSize) {
            'mini' => $svg->mini($this->iconName),
            'micro' => $svg->micro($this->iconName),
            default => $this->iconType === 'outline'
                ? $svg->outline($this->iconName)
                : $svg->solid($this->iconName),
        };

        $sizeClass = match ($this->iconSize) {
            'mini' => 'size-5',
            'micro' => 'size-4',
            default => 'size-5',
        };

        $colorClass = match ($iconColor) {
            Bleet::COLOR_PRIMARY => 'text-primary-700',
            Bleet::COLOR_SECONDARY => 'text-secondary-700',
            Bleet::COLOR_SUCCESS => 'text-success-700',
            Bleet::COLOR_DANGER => 'text-danger-700',
            Bleet::COLOR_WARNING => 'text-warning-700',
            Bleet::COLOR_INFO => 'text-info-700',
        };

        $icon = $svg->addClass($sizeClass, 'shrink-0', $colorClass)->render();

        return $icon . '<span>' . $content . '</span>';
    }
}
