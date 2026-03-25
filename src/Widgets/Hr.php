<?php

declare(strict_types=1);

/**
 * Hr.php
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
 * Hr widget (horizontal rule / separator)
 *
 * Usage:
 *   Bleet::hr()->render();
 *   Bleet::hr('OU')->render();
 *   Bleet::hr('SECTION')->secondary()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Hr extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private ?string $text = null;

    public function __construct(?string $text = null)
    {
        $this->text = $text;
    }

    /**
     * Sets the separator center text
     */
    public function text(string $text): self
    {
        $new = clone $this;
        $new->text = $text;
        return $new;
    }

    public function render(): string
    {
        if ($this->text !== null && $this->text !== '') {
            return $this->renderWithText();
        }

        return $this->renderSimple();
    }

    private function renderSimple(): string
    {
        $classes = implode(' ', $this->getSimpleClasses());

        return Html::tag('hr')
            ->class($classes)
            ->render();
    }

    private function renderWithText(): string
    {
        [$spanClasses, $hrClasses] = $this->getTextColorClasses();

        $hr1 = Html::tag('hr')->class(...$hrClasses);
        $span = Html::span($this->text)->class(...$spanClasses);
        $hr2 = Html::tag('hr')->class(...$hrClasses);

        return Html::div($hr1->render() . $span->render() . $hr2->render())
            ->encode(false)
            ->class('flex', 'items-center', 'gap-4', 'my-6')
            ->render();
    }

    /**
     * @return string[]
     */
    private function getSimpleClasses(): array
    {
        $baseClasses = ['my-4'];
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
     * @return array{string[], string[]}
     */
    private function getTextColorClasses(): array
    {
        $baseSpanClasses = ['font-medium'];
        $baseHrClasses = ['flex-1', 'my-4'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'border-primary-200'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'border-secondary-200'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'border-success-200'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'border-danger-200'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'border-warning-200'],
            Bleet::COLOR_INFO => ['text-info-700', 'border-info-200'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'border-accent-200'],
        };
        return [
            [...$baseSpanClasses, $colorClasses[0]],
            [...$baseHrClasses, $colorClasses[1]],
        ];
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }
}
