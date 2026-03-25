<?php

declare(strict_types=1);

/**
 * H3.php
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
 * H3 widget - Section header with bottom border
 *
 * Usage:
 *   Bleet::h3('Mon titre')->render();
 *   Bleet::h3('Mon titre')->subtitle('Description')->render();
 *   Bleet::h3('Mon titre')->secondary()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class H3 extends AbstractWidget
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

        if (!empty($this->title)) {
            $html .= Html::tag('h3', Html::encode($this->title), ['class' => $this->getTitleClasses()]);
        }

        if (!empty($this->subtitle)) {
            $html .= Html::tag('p', Html::encode($this->subtitle), ['class' => $this->getSubtitleClasses()]);
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
    private function getContainerClasses(): array
    {
        $baseClasses = ['border-b', 'pb-5', 'mb-5'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-200'],
            Bleet::COLOR_SECONDARY => ['border-secondary-200'],
            Bleet::COLOR_SUCCESS => ['border-success-200'],
            Bleet::COLOR_DANGER => ['border-danger-200'],
            Bleet::COLOR_WARNING => ['border-warning-200'],
            Bleet::COLOR_INFO => ['border-info-200'],
            Bleet::COLOR_ACCENT => ['border-accent-200'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getTitleClasses(): array
    {
        $baseClasses = ['text-base', 'font-semibold'];
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

    /**
     * @return string[]
     */
    private function getSubtitleClasses(): array
    {
        $baseClasses = ['mt-2', 'max-w-4xl', 'text-sm'];
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
