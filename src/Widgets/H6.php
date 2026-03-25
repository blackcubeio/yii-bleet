<?php

declare(strict_types=1);

/**
 * H6.php
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
 * H6 widget - Smallest section header
 *
 * Usage:
 *   Bleet::h6('Mon titre')->render();
 *   Bleet::h6('Mon titre')->secondary()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class H6 extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $title = '';

    public function __construct(string $title = '')
    {
        $this->title = $title;
    }

    /**
     * Sets the title
     */
    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    public function render(): string
    {
        $containerAttributes = $this->prepareTagAttributes();
        Html::addCssClass($containerAttributes, ['pb-2', 'mb-2']);

        $html = Html::openTag('div', $containerAttributes);

        if (!empty($this->title)) {
            $html .= Html::tag('h6', Html::encode($this->title), ['class' => $this->getTitleClasses()]);
        }

        $html .= Html::closeTag('div');

        return $html;
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    private function getTitleClasses(): array
    {
        $baseClasses = ['text-xs', 'font-medium'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };
        return [...$baseClasses, ...$colorClasses];
    }
}
