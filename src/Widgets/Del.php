<?php

declare(strict_types=1);

/**
 * Del.php
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
 * Del widget (deleted text)
 *
 * Usage:
 *   Bleet::del('deleted text')->render();
 *   Bleet::del('ancien prix')->datetime('2025-01-15')->render();
 *   Bleet::del('contenu')->cite('https://example.com/changelog')->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Del extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_DANGER;

    private string $content = '';
    private ?string $datetime = null;
    private ?string $cite = null;
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
     * Sets la date/heure de la suppression (format ISO 8601)
     */
    public function datetime(string $datetime): self
    {
        $new = clone $this;
        $new->datetime = $datetime;
        return $new;
    }

    /**
     * Sets the URL expliquant la suppression
     */
    public function cite(string $cite): self
    {
        $new = clone $this;
        $new->cite = $cite;
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
        $defaults = [];
        if ($this->datetime !== null) {
            $defaults['datetime'] = $this->datetime;
        }
        if ($this->cite !== null) {
            $defaults['cite'] = $this->cite;
        }

        $attributes = $this->prepareTagAttributes($defaults);
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::tag('del', $this->content, $attributes)
            ->encode($this->encode)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [...$this->getColorClasses()];
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
