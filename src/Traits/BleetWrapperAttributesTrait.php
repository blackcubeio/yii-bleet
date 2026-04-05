<?php

declare(strict_types=1);

/**
 * BleetWrapperAttributesTrait.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

use BackedEnum;
use Yiisoft\Html\Html;

/**
 * Provides wrapper element attribute management for Bleet widgets.
 *
 * Same API as BleetAttributesTrait but prefixed with "wrapper".
 * Targets the **wrapper HTML element** (e.g., the grid div around an input with icons).
 *
 * Only used by widgets that have a wrapper element (form widgets in icon/floating modes).
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait BleetWrapperAttributesTrait
{
    private array $wrapperAttributes = [];

    /**
     * Set a wrapper attribute value.
     */
    public function wrapperAttribute(string $name, mixed $value): static
    {
        $new = clone $this;
        $new->wrapperAttributes[$name] = $value;
        return $new;
    }

    /**
     * Replace all wrapper attributes with a new set.
     */
    public function wrapperAttributes(array $attributes): static
    {
        $new = clone $this;
        $new->wrapperAttributes = $attributes;
        return $new;
    }

    /**
     * Merge attributes into existing wrapper ones (new values win on conflict).
     */
    public function wrapperAddAttributes(array $attributes): static
    {
        $new = clone $this;
        $new->wrapperAttributes = array_merge($new->wrapperAttributes, $attributes);
        return $new;
    }

    /**
     * Union attributes with existing wrapper ones (existing values win on conflict).
     */
    public function wrapperUnionAttributes(array $attributes): static
    {
        $new = clone $this;
        $new->wrapperAttributes += $attributes;
        return $new;
    }

    /**
     * Set the wrapper ID.
     */
    public function wrapperId(?string $id): static
    {
        $new = clone $this;
        $new->wrapperAttributes['id'] = $id;
        return $new;
    }

    /**
     * Add one or more CSS classes to the wrapper.
     */
    public function wrapperAddClass(BackedEnum|string|null ...$class): static
    {
        $new = clone $this;
        Html::addCssClass($new->wrapperAttributes, $class);
        return $new;
    }

    /**
     * Replace wrapper CSS classes with a new set.
     */
    public function wrapperClass(BackedEnum|string|null ...$class): static
    {
        $new = clone $this;
        unset($new->wrapperAttributes['class']);
        Html::addCssClass($new->wrapperAttributes, $class);
        return $new;
    }

    /**
     * Add CSS inline styles to the wrapper.
     */
    public function wrapperAddStyle(array|string $style, bool $overwrite = true): static
    {
        $new = clone $this;
        Html::addCssStyle($new->wrapperAttributes, $style, $overwrite);
        return $new;
    }

    /**
     * Remove CSS inline styles from the wrapper.
     */
    public function wrapperRemoveStyle(string|array $properties): static
    {
        $new = clone $this;
        Html::removeCssStyle($new->wrapperAttributes, $properties);
        return $new;
    }

    /**
     * @return array Raw user-defined wrapper attributes
     */
    protected function getWrapperAttributes(): array
    {
        return $this->wrapperAttributes;
    }

    /**
     * Merge user wrapper attributes on top of widget defaults.
     * - Classes accumulate (both default and user classes are kept)
     * - Everything else: user values override defaults
     *
     * @param array $defaults Widget-specific default wrapper attributes
     * @return array Merged attributes ready for rendering
     */
    protected function prepareWrapperAttributes(array $defaults = []): array
    {
        $wrapperAttributes = $this->wrapperAttributes;

        // Extract user classes (handled separately to accumulate instead of replace)
        $userClass = $wrapperAttributes['class'] ?? null;
        unset($wrapperAttributes['class']);

        // Merge: user attributes override defaults
        $attributes = array_merge($defaults, $wrapperAttributes);

        // Accumulate user classes
        if ($userClass !== null) {
            Html::addCssClass($attributes, $userClass);
        }

        return $attributes;
    }
}
