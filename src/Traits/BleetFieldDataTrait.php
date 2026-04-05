<?php

declare(strict_types=1);

/**
 * BleetFieldDataTrait.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

/**
 * Trait for widgets that have a field element (input, select, textarea, etc.)
 * Provides a way to add data-* attributes directly on the field element
 */
trait BleetFieldDataTrait
{
    /** @var array<string, string> */
    private array $fieldData = [];

    /**
     * Sets data-* attributes on the field element
     * @param array<string, string> $data Key => value pairs (without 'data-' prefix)
     */
    public function fieldData(array $data): self
    {
        $new = clone $this;
        $new->fieldData = array_merge($new->fieldData, $data);
        return $new;
    }

    /**
     * Gets the field data attributes with 'data-' prefix
     * @return array<string, string>
     */
    protected function getFieldDataAttributes(): array
    {
        $attributes = [];
        foreach ($this->fieldData as $key => $value) {
            $attributes['data-' . $key] = $value;
        }
        return $attributes;
    }
}
