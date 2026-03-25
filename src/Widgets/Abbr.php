<?php

declare(strict_types=1);

/**
 * Abbr.php
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
 * Abbr widget (abbreviation)
 *
 * Usage:
 *   Bleet::abbr('HTML', 'HyperText Markup Language')->render();
 *   Bleet::abbr('CSS', 'Cascading Style Sheets')->primary()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Abbr extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $content = '';
    private string $title;
    private bool $encode = true;

    public function __construct(string $content, string $title)
    {
        $this->content = $content;
        $this->title = $title;
    }

    /**
     * Sets the content (the abbreviation)
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Sets the title (the full meaning)
     */
    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
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
        $attributes = $this->prepareTagAttributes(['title' => $this->title]);
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::tag('abbr', $this->content, $attributes)
            ->encode($this->encode)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = [
            'cursor-help',
            'border-b',
            'border-dotted',
        ];

        return [...$baseClasses, ...$this->getColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-400'],
            Bleet::COLOR_SECONDARY => ['border-secondary-400'],
            Bleet::COLOR_SUCCESS => ['border-success-400'],
            Bleet::COLOR_DANGER => ['border-danger-400'],
            Bleet::COLOR_WARNING => ['border-warning-400'],
            Bleet::COLOR_INFO => ['border-info-400'],
            Bleet::COLOR_ACCENT => ['border-accent-400'],
        };
    }
}
