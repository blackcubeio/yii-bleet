<?php

declare(strict_types=1);

/**
 * BleetAureliaTrait.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

use Blackcube\Bleet\Aurelia;
use RuntimeException;
use Yiisoft\Html\Html;

/**
 * Trait pour les helpers Aurelia haut niveau (overlay, tabs, pager, burger, menu)
 */
trait BleetAureliaTrait
{
    private static bool $overlayRendered = false;

    /**
     * Generates <bleet-overlay> (backdrop semi-transparent pour modals/drawers)
     *
     * @param array<string, mixed> $options HTML options
     * @return string
     * @throws RuntimeException If called more than once
     */
    public static function overlay(array $options = []): string
    {
        if (self::$overlayRendered) {
            throw new RuntimeException('overlay() can only be called once per page.');
        }
        self::$overlayRendered = true;

        $existingClasses = $options['class'] ?? [];
        if (is_string($existingClasses)) {
            $existingClasses = explode(' ', $existingClasses);
        }
        $options['class'] = implode(' ', array_merge(
            $existingClasses,
            ['fixed', 'inset-0', 'bg-black/70', 'z-40', 'lg:z-60', 'hidden', 'opacity-0', 'transition-opacity', 'duration-300']
        ));

        return Html::tag('bleet-overlay', '', Aurelia::attributesCustomElement($options))->render();
    }

    /**
     * Reset overlay rendered flag (useful for testing)
     */
    public static function resetOverlay(): void
    {
        self::$overlayRendered = false;
    }

    /**
     * Attribute for tabs handling
     *
     * @param array<string, mixed>|string $options
     * @return array<string, string>
     */
    public static function tabs(array|string $options = ''): array
    {
        return ['bleet-tabs' => is_string($options) ? $options : Aurelia::attributesCustomAttribute($options)];
    }

    /**
     * Attribute for pager handling
     *
     * @param array<string, mixed>|string $options
     * @return array<string, string>
     */
    public static function pager(array|string $options = ''): array
    {
        return ['bleet-pager' => is_string($options) ? $options : Aurelia::attributesCustomAttribute($options)];
    }

    /**
     * Attribute for burger menu handling
     *
     * @param array<string, mixed>|string $options
     * @return array<string, string>
     */
    public static function burger(array|string $options = ''): array
    {
        return ['bleet-burger' => is_string($options) ? $options : Aurelia::attributesCustomAttribute($options)];
    }

    /**
     * Attribute for menu handling
     *
     * @param array<string, mixed>|string $options
     * @return array<string, string>
     */
    public static function menu(array|string $options = ''): array
    {
        return ['bleet-menu' => is_string($options) ? $options : Aurelia::attributesCustomAttribute($options)];
    }
}
