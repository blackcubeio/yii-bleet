<?php

declare(strict_types=1);

/**
 * ButtonsBar.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

/**
 * ButtonsBar widget - Container for action buttons
 *
 * Usage:
 *   Bleet::buttonsBar()
 *       ->addButton(Bleet::button()->icon('pencil')->info()->outline()->xs())
 *       ->addButton(Bleet::a()->url('/edit')->icon('pencil')->info()->outline()->xs())
 *       ->addButton(Bleet::button()->icon('trash')->danger()->outline()->xs())
 *       ->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ButtonsBar extends AbstractWidget
{
    /** @var array<Button|Anchor> */
    private array $buttons = [];

    /**
     * Adds a button or anchor
     */
    public function addButton(Button|Anchor $button): self
    {
        $new = clone $this;
        $new->buttons[] = $button;
        return $new;
    }

    /**
     * Sets all buttons
     * @param array<Button|Anchor> $buttons
     */
    public function buttons(array $buttons): self
    {
        $new = clone $this;
        $new->buttons = $buttons;
        return $new;
    }

    public function render(): string
    {
        if (empty($this->buttons)) {
            return '';
        }

        $html = '';
        foreach ($this->buttons as $button) {
            $html .= $button->render();
        }

        return Html::div($html, ['class' => $this->prepareClasses()])
            ->encode(false)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return ['inline-flex', 'items-center', 'border', 'border-secondary-300', 'rounded', 'overflow-hidden'];
    }
}
