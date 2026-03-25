<?php

declare(strict_types=1);

/**
 * Paragraph.php
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
use Stringable;
use Yiisoft\Html\Html;

/**
 * Paragraph widget
 *
 * Usage:
 *   Bleet::p('Paragraph text')->render();
 *   Bleet::p('Texte d\'introduction')->lg()->render();
 *   Bleet::p('Note')->primary()->sm()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Paragraph extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private string|Stringable $content = '';
    private bool $encode = true;

    /**
     * Sets the content
     */
    public function content(string|Stringable $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Controls HTML encoding of content
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

        return Html::tag('p', (string) $this->content, $attributes)
            ->encode($this->encode)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [...$this->getSizeClasses(), ...$this->getColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getSizeClasses(): array
    {
        return match ($this->size) {
            Bleet::SIZE_XS => ['text-xs'],
            Bleet::SIZE_SM => ['text-sm'],
            Bleet::SIZE_MD => ['text-base'],
            Bleet::SIZE_LG => ['text-lg'],
            Bleet::SIZE_XL => ['text-xl'],
        };
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
