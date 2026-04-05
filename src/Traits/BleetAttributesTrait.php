<?php

declare(strict_types=1);

/**
 * BleetAttributesTrait.php
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
 * Provides homogeneous HTML attribute management for Bleet widgets.
 *
 * Mirrors the Yiisoft\Html\Tag\Base\Tag API:
 * - attribute(), attributes() (replace), addAttributes() (merge), unionAttributes() (union)
 * - id(), addClass(), class() (replace), addStyle(), removeStyle()
 *
 * Attributes target the **main HTML element** of the widget.
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait BleetAttributesTrait
{
    private array $tagAttributes = [];

    /**
     * Set an attribute value.
     */
    public function attribute(string $name, mixed $value): static
    {
        $new = clone $this;
        $new->tagAttributes[$name] = $value;
        return $new;
    }

    /**
     * Replace all attributes with a new set.
     */
    public function attributes(array $attributes): static
    {
        $new = clone $this;
        $new->tagAttributes = $attributes;
        return $new;
    }

    /**
     * Merge attributes into existing ones (new values win on conflict).
     */
    public function addAttributes(array $attributes): static
    {
        $new = clone $this;
        $new->tagAttributes = array_merge($new->tagAttributes, $attributes);
        return $new;
    }

    /**
     * Union attributes with existing ones (existing values win on conflict).
     */
    public function unionAttributes(array $attributes): static
    {
        $new = clone $this;
        $new->tagAttributes += $attributes;
        return $new;
    }

    /**
     * Set the tag ID.
     */
    public function id(?string $id): static
    {
        $new = clone $this;
        $new->tagAttributes['id'] = $id;
        return $new;
    }

    /**
     * Add one or more CSS classes.
     */
    public function addClass(BackedEnum|string|null ...$class): static
    {
        $new = clone $this;
        Html::addCssClass($new->tagAttributes, $class);
        return $new;
    }

    /**
     * Replace CSS classes with a new set.
     */
    public function class(BackedEnum|string|null ...$class): static
    {
        $new = clone $this;
        unset($new->tagAttributes['class']);
        Html::addCssClass($new->tagAttributes, $class);
        return $new;
    }

    /**
     * Add CSS inline styles.
     */
    public function addStyle(array|string $style, bool $overwrite = true): static
    {
        $new = clone $this;
        Html::addCssStyle($new->tagAttributes, $style, $overwrite);
        return $new;
    }

    /**
     * Remove CSS inline styles.
     */
    public function removeStyle(string|array $properties): static
    {
        $new = clone $this;
        Html::removeCssStyle($new->tagAttributes, $properties);
        return $new;
    }

    /**
     * @return array Raw user-defined attributes
     */
    protected function getTagAttributes(): array
    {
        return $this->tagAttributes;
    }

    /**
     * Merge user attributes on top of widget defaults.
     * - Classes accumulate (both default and user classes are kept)
     * - Everything else: user values override defaults
     *
     * @param array $defaults Widget-specific default attributes (e.g., ['title' => $this->title])
     * @return array Merged attributes ready for rendering
     */
    protected function prepareTagAttributes(array $defaults = []): array
    {
        $tagAttributes = $this->tagAttributes;

        // Extract user classes (handled separately to accumulate instead of replace)
        $userClass = $tagAttributes['class'] ?? null;
        unset($tagAttributes['class']);

        // Merge: user attributes override defaults
        $attributes = array_merge($defaults, $tagAttributes);

        // Accumulate user classes
        if ($userClass !== null) {
            Html::addCssClass($attributes, $userClass);
        }

        return $attributes;
    }
}
