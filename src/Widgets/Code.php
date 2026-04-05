<?php

declare(strict_types=1);

/**
 * Code.php
 *
 * PHP Version 8.1
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
 * Code widget (inline code)
 *
 * Usage:
 *   Bleet::code('$variable')->render();
 *   Bleet::code('SELECT *')->secondary()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Code extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

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

        return Html::tag('code', $this->content, $attributes)
            ->encode($this->encode)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = [
            'px-2',
            'py-1',
            'rounded',
            'font-mono',
        ];

        return [...$baseClasses, ...$this->getSizeClasses(), ...$this->getColorClasses()];
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
            Bleet::COLOR_PRIMARY => ['bg-primary-50', 'text-primary-700'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50', 'text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['bg-success-50', 'text-success-700'],
            Bleet::COLOR_DANGER => ['bg-danger-50', 'text-danger-700'],
            Bleet::COLOR_WARNING => ['bg-warning-50', 'text-warning-700'],
            Bleet::COLOR_INFO => ['bg-info-50', 'text-info-700'],
            Bleet::COLOR_ACCENT => ['bg-accent-50', 'text-accent-700'],
        };
    }
}
