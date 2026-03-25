<?php

declare(strict_types=1);

/**
 * Strong.php
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
 * Strong widget (bold text)
 *
 * Usage:
 *   Bleet::strong('texte important')->render();
 *   Bleet::strong('attention')->danger()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Strong extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

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

        return Html::tag('strong', $this->content, $attributes)
            ->encode($this->encode)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = [
            'font-semibold',
        ];

        return [...$baseClasses, ...$this->getColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };
    }
}
