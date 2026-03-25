<?php

declare(strict_types=1);

/**
 * Toaster.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetAttributesTrait;

/**
 * Toaster widget - Container for toast notifications
 *
 * Generates the fixed container (top-right) that holds all dynamically created toasts.
 * Toasts are added via bleet-toaster-trigger elements/attributes that publish events.
 *
 * Usage (once in layout):
 *   <?= Bleet::toaster()->render() ?>
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Toaster extends AbstractWidget
{
    use BleetAttributesTrait;
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    public function render(): string
    {
        return $this->renderView('toaster', $this->prepareViewParams());
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
        return [
            'containerAttributes' => $this->prepareTagAttributes(),
        ];
    }
}
