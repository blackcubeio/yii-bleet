<?php

declare(strict_types=1);

/**
 * Mark.php
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
use Yiisoft\Html\Html;

/**
 * Mark widget (highlighted text)
 *
 * Usage:
 *   Bleet::mark('highlighted text')->render();
 *   Bleet::mark('important')->warning()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Mark extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_WARNING;

    private string $content = '';
    private bool $encode = true;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Sets the content
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Disables HTML encoding
     */
    public function encode(bool $encode = true): self
    {
        $new = clone $this;
        $new->encode = $encode;
        return $new;
    }

    public function render(): string
    {
        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::tag('mark', $this->content, $attributes)
            ->encode($this->encode)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = [
            'px-1',
            'rounded',
        ];

        return [...$baseClasses, ...$this->getColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-200'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-200'],
            Bleet::COLOR_SUCCESS => ['bg-success-200'],
            Bleet::COLOR_DANGER => ['bg-danger-200'],
            Bleet::COLOR_WARNING => ['bg-warning-200'],
            Bleet::COLOR_INFO => ['bg-info-200'],
            Bleet::COLOR_ACCENT => ['bg-accent-200'],
        };
    }
}
