<?php

declare(strict_types=1);

/**
 * SlotCaptureTrait.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

use Blackcube\Bleet\Interfaces\WidgetInterface;
use Closure;

/**
 * Trait for capturing slot content with begin/end pattern
 *
 * Supports 4 modes for slot content:
 * - String: Direct HTML string
 * - Widget: Any WidgetInterface implementation
 * - Closure: Function that returns or echoes content
 * - Capture: begin/end pattern with output buffering
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait SlotCaptureTrait
{
    private array $captureStack = [];
    private array $slots = [];

    /**
     * Starts slot capture
     */
    protected function beginSlot(string $name): static
    {
        $new = clone $this;
        $new->captureStack[] = $name;
        ob_start();
        return $new;
    }

    /**
     * Termine la capture et stocke dans le slot
     */
    protected function endSlot(): static
    {
        if (empty($this->captureStack)) {
            throw new \LogicException('No slot capture in progress');
        }

        $name = array_pop($this->captureStack);
        $content = ob_get_clean();

        $new = clone $this;
        $new->slots[$name] = $content;
        $new->captureStack = $this->captureStack;
        return $new;
    }

    /**
     * Gets slot content
     */
    protected function getSlot(string $name): ?string
    {
        return $this->slots[$name] ?? null;
    }

    /**
     * Resolves a slot to an HTML string
     */
    protected function resolveSlot(
        string|WidgetInterface|Closure|null $slot,
        bool $encode = false
    ): string {
        if ($slot === null) {
            return '';
        }

        if ($slot instanceof Closure) {
            ob_start();
            $result = $slot();
            $output = ob_get_clean();
            // Return takes priority over echo
            return $result !== null ? (string) $result : $output;
        }

        if ($slot instanceof WidgetInterface) {
            return $slot->render();
        }

        // String
        return $encode ? htmlspecialchars($slot, ENT_QUOTES, 'UTF-8') : $slot;
    }
}
