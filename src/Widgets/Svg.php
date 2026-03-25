<?php

declare(strict_types=1);

/**
 * Svg.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use InvalidArgumentException;
use Yiisoft\Html\Html;

/**
 * Svg widget for Heroicons and custom icons
 *
 * Usage:
 *   Svg::heroicon()->outline('chevron-right')->render();
 *   Svg::heroicon()->solid('check')->addClass('w-6', 'h-6')->render();
 *   Svg::heroicon()->mini('arrow-left')->render();
 *   Svg::heroicon()->micro('plus')->render();
 *   Svg::icon()->ui('custom-icon')->render();
 *   Svg::icon()->logo('brand')->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Svg
{
    public const SIZE_NORMAL = 'normal';
    public const SIZE_MINI = 'mini';
    public const SIZE_MICRO = 'micro';
    public const SIZE_UI = 'ui';
    public const SIZE_LOGO = 'logo';

    public const TYPE_OUTLINE = 'outline';
    public const TYPE_SOLID = 'solid';

    private const RESOURCES_DIR = __DIR__ . '/../resources';
    private const HEROICONS_DIR = 'heroicons';
    private const CUSTOMICONS_DIR = 'customicons';

    private static array $outerSvgAttributes = [
        'outline/normal' => [
            'xmlns' => 'http://www.w3.org/2000/svg',
            'fill' => 'none',
            'viewBox' => '0 0 24 24',
            'stroke-width' => '1.5',
            'stroke' => 'currentColor',
            'aria-hidden' => 'true',
            'data-slot' => 'icon',
        ],
        'solid/normal' => [
            'xmlns' => 'http://www.w3.org/2000/svg',
            'viewBox' => '0 0 24 24',
            'fill' => 'currentColor',
            'aria-hidden' => 'true',
            'data-slot' => 'icon',
        ],
        'solid/mini' => [
            'xmlns' => 'http://www.w3.org/2000/svg',
            'viewBox' => '0 0 20 20',
            'fill' => 'currentColor',
            'aria-hidden' => 'true',
            'data-slot' => 'icon',
        ],
        'solid/micro' => [
            'xmlns' => 'http://www.w3.org/2000/svg',
            'viewBox' => '0 0 16 16',
            'fill' => 'currentColor',
            'aria-hidden' => 'true',
            'data-slot' => 'icon',
        ],
        'solid/ui' => [
            'xmlns' => 'http://www.w3.org/2000/svg',
            'viewBox' => '0 0 24 24',
            'fill' => 'none',
            'aria-hidden' => 'true',
            'data-slot' => 'icon',
        ],
        'solid/logo' => [
            'xmlns' => 'http://www.w3.org/2000/svg',
            'viewBox' => '0 0 200 200',
            'fill' => 'none',
            'aria-hidden' => 'true',
            'data-slot' => 'icon',
        ],
    ];

    /** @var array<string, array<string, array<string, string|array<string>>>> */
    private static array $loadedIcons = [];

    private ?string $name = null;
    private string $size = self::SIZE_NORMAL;
    private string $type = self::TYPE_OUTLINE;
    private bool $isHeroicon = true;
    private array $attributes = [];

    /**
     * Factory pour Heroicons
     */
    public static function heroicon(): self
    {
        $instance = new self();
        $instance->isHeroicon = true;
        return $instance;
    }

    /**
     * Factory for custom icons
     */
    public static function icon(): self
    {
        $instance = new self();
        $instance->isHeroicon = false;
        return $instance;
    }

    /**
     * Outline icon (normal 24x24) - Heroicons only
     */
    public function outline(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        $new->size = self::SIZE_NORMAL;
        $new->type = self::TYPE_OUTLINE;
        return $new;
    }

    /**
     * Solid icon (normal 24x24) - Heroicons only
     */
    public function solid(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        $new->size = self::SIZE_NORMAL;
        $new->type = self::TYPE_SOLID;
        return $new;
    }

    /**
     * Mini solid icon (20x20) - Heroicons only
     */
    public function mini(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        $new->size = self::SIZE_MINI;
        $new->type = self::TYPE_SOLID;
        return $new;
    }

    /**
     * Micro solid icon (16x16) - Heroicons only
     */
    public function micro(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        $new->size = self::SIZE_MICRO;
        $new->type = self::TYPE_SOLID;
        return $new;
    }

    /**
     * UI custom icon (24x24) - Custom icons only
     */
    public function ui(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        $new->size = self::SIZE_UI;
        $new->type = self::TYPE_SOLID;
        $new->isHeroicon = false;
        return $new;
    }

    /**
     * Custom logo (200x200) - Custom icons only
     */
    public function logo(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        $new->size = self::SIZE_LOGO;
        $new->type = self::TYPE_SOLID;
        $new->isHeroicon = false;
        return $new;
    }

    /**
     * Sets the id attribute
     */
    public function id(string $id): self
    {
        $new = clone $this;
        $new->attributes['id'] = $id;
        return $new;
    }

    /**
     * Adds a CSS class
     */
    public function addClass(string ...$classes): self
    {
        $new = clone $this;
        $existing = $new->attributes['class'] ?? '';
        $new->attributes['class'] = trim($existing . ' ' . implode(' ', $classes));
        return $new;
    }

    /**
     * Sets an HTML attribute
     */
    public function attribute(string $name, mixed $value): self
    {
        $new = clone $this;
        $new->attributes[$name] = $value;
        return $new;
    }

    /**
     * Sets plusieurs attributs HTML
     */
    public function attributes(array $attributes): self
    {
        $new = clone $this;
        $new->attributes = array_merge($new->attributes, $attributes);
        return $new;
    }

    public function __toString(): string
    {
        return $this->render();
    }

    public function render(): string
    {
        if ($this->name === null) {
            throw new InvalidArgumentException('SVG name is required. Use outline(), solid(), mini(), micro(), ui() or logo() first.');
        }

        $this->validateSizeType();

        $iconData = $this->loadIconData();
        $path = $iconData[$this->name] ?? null;

        if ($path === null) {
            throw new InvalidArgumentException("SVG '{$this->name}' not found in {$this->type}/{$this->size}");
        }

        $key = $this->type . '/' . $this->size;
        $baseAttributes = self::$outerSvgAttributes[$key];

        // Handle custom viewBox from icon data
        if (is_array($path) && isset($path['viewBox'])) {
            $viewBox = $path['viewBox'];
            unset($path['viewBox']);
            $baseAttributes['viewBox'] = $viewBox;
        }

        $finalAttributes = array_merge($baseAttributes, $this->attributes);

        // Convert path array to string
        $pathContent = is_array($path) ? implode("\n", $path) : $path;

        return Html::tag('svg', $pathContent, $finalAttributes)
            ->encode(false)
            ->render();
    }

    private function validateSizeType(): void
    {
        $validSizes = [self::SIZE_NORMAL, self::SIZE_MINI, self::SIZE_MICRO, self::SIZE_UI, self::SIZE_LOGO];
        $validTypes = [self::TYPE_OUTLINE, self::TYPE_SOLID];

        if (!in_array($this->size, $validSizes, true) || !in_array($this->type, $validTypes, true)) {
            throw new InvalidArgumentException('Invalid size or type');
        }

        // Outline only available for normal size
        if ($this->size !== self::SIZE_NORMAL && $this->type === self::TYPE_OUTLINE) {
            throw new InvalidArgumentException('Outline type is only available for normal size');
        }
    }

    /**
     * @return array<string, string|array<string>>
     */
    private function loadIconData(): array
    {
        if (isset(self::$loadedIcons[$this->size][$this->type])) {
            return self::$loadedIcons[$this->size][$this->type];
        }

        $directory = $this->isHeroicon ? self::HEROICONS_DIR : self::CUSTOMICONS_DIR;

        $filePath = self::RESOURCES_DIR . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $this->size . DIRECTORY_SEPARATOR . $this->type . '.php';

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("Icons file not found: {$this->type}/{$this->size}");
        }

        self::$loadedIcons[$this->size][$this->type] = require $filePath;

        return self::$loadedIcons[$this->size][$this->type];
    }
}
