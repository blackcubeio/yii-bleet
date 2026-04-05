<?php

declare(strict_types=1);

/**
 * H2.php
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
 * H2 widget - Section header with colored background
 *
 * Usage:
 *   Bleet::h2('Mon titre')->render();
 *   Bleet::h2('Mon titre')->subtitle('Description')->render();
 *   Bleet::h2('Mon titre')->secondary()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class H2 extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $title = '';
    private ?string $subtitle = null;

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

    /**
     * Sets le sous-titre
     */
    public function subtitle(string $subtitle): self
    {
        $new = clone $this;
        $new->subtitle = $subtitle;
        return $new;
    }

    public function render(): string
    {
        $containerAttributes = $this->prepareTagAttributes();
        Html::addCssClass($containerAttributes, $this->getContainerClasses());

        $html = Html::openTag('div', $containerAttributes);
        $html .= Html::openTag('div', ['class' => ['max-w-4xl']]);

        if (!empty($this->title)) {
            $html .= Html::tag('h2', Html::encode($this->title), ['class' => ['text-2xl', 'font-bold', 'text-white', 'sm:text-3xl']]);
        }

        if (!empty($this->subtitle)) {
            $html .= Html::tag('p', Html::encode($this->subtitle), ['class' => $this->getSubtitleClasses()]);
        }

        $html .= Html::closeTag('div');
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
    private function getContainerClasses(): array
    {
        $baseClasses = ['rounded-lg', 'px-6', 'py-8', 'sm:px-8', 'mb-6'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-800'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-800'],
            Bleet::COLOR_SUCCESS => ['bg-success-800'],
            Bleet::COLOR_DANGER => ['bg-danger-800'],
            Bleet::COLOR_WARNING => ['bg-warning-800'],
            Bleet::COLOR_INFO => ['bg-info-800'],
            Bleet::COLOR_ACCENT => ['bg-accent-800'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getSubtitleClasses(): array
    {
        $baseClasses = ['mt-3', 'text-base', 'sm:text-lg'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-200'],
            Bleet::COLOR_SECONDARY => ['text-secondary-200'],
            Bleet::COLOR_SUCCESS => ['text-success-200'],
            Bleet::COLOR_DANGER => ['text-danger-200'],
            Bleet::COLOR_WARNING => ['text-warning-200'],
            Bleet::COLOR_INFO => ['text-info-200'],
            Bleet::COLOR_ACCENT => ['text-accent-200'],
        };
        return [...$baseClasses, ...$colorClasses];
    }
}
