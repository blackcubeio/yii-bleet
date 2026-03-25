<?php

declare(strict_types=1);

/**
 * Pre.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Interfaces\WidgetInterface;
use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Blackcube\Bleet\Traits\SlotCaptureTrait;
use Closure;
use Yiisoft\Html\Html;

/**
 * Pre widget (code block)
 *
 * Usage:
 *   Bleet::pre('code here')->render();
 *   Bleet::pre('code')->title('file.php')->render();
 *   Bleet::pre('SELECT * FROM users')->secondary()->title('query.sql')->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Pre extends AbstractWidget implements WidgetInterface
{
    use BleetAttributesTrait;
    use SlotCaptureTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private WidgetInterface|Closure|string $content = '';
    private ?string $title = null;
    private bool $encode = true;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Sets the content of the block code
     */
    public function content(WidgetInterface|Closure|string $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    public function beginContent(): static
    {
        return $this->beginSlot('content');
    }

    public function endContent(): static
    {
        $new = $this->endSlot();
        $new->content = $new->getSlot('content') ?? '';
        return $new;
    }

    /**
     * Sets the title of the block (nom of the file par exemple)
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
        $resolvedContent = $this->resolveSlot($this->content, $this->encode);
        $codeTag = Html::tag('code', $resolvedContent)
            ->encode(false)
            ->class('font-mono');

        if ($this->title !== null) {
            return $this->renderWithTitle($codeTag);
        }

        return $this->renderSimple($codeTag);
    }

    private function renderSimple(mixed $codeTag): string
    {
        $preClasses = [
            'bg-primary-900',
            'text-white',
            'p-4',
            'rounded-lg',
            'overflow-x-auto',
        ];

        return Html::tag('pre', $codeTag->render())
            ->encode(false)
            ->class(implode(' ', $preClasses))
            ->render();
    }

    private function renderWithTitle(mixed $codeTag): string
    {
        [$titleClasses, $headerClasses, $containerClasses] = $this->getTitleColorClasses();

        // Header with title
        $titleTag = Html::p($this->title)
            ->class(...$titleClasses);

        $headerTag = Html::div($titleTag)
            ->class(...$headerClasses);

        // Pre with code
        $preTag = Html::tag('pre', $codeTag->render())
            ->encode(false)
            ->class('bg-primary-900', 'text-white', 'p-4', 'rounded-b-lg', 'overflow-x-auto');

        return Html::div($headerTag->render() . $preTag->render())
            ->encode(false)
            ->class(...$containerClasses)
            ->render();
    }

    /**
     * @return array{string[], string[], string[]}
     */
    private function getTitleColorClasses(): array
    {
        $baseTitleClasses = ['text-sm', 'font-medium'];
        $baseHeaderClasses = ['px-4', 'py-2', 'border-b'];
        $baseContainerClasses = ['border', 'rounded-lg', 'overflow-hidden'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'bg-primary-50', 'border-primary-200', 'bg-primary-50'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'bg-secondary-50', 'border-secondary-200', 'bg-secondary-50'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'bg-success-50', 'border-success-200', 'bg-success-50'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'bg-danger-50', 'border-danger-200', 'bg-danger-50'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'bg-warning-50', 'border-warning-200', 'bg-warning-50'],
            Bleet::COLOR_INFO => ['text-info-700', 'bg-info-50', 'border-info-200', 'bg-info-50'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'bg-accent-50', 'border-accent-200', 'bg-accent-50'],
        };
        return [
            [...$baseTitleClasses, $colorClasses[0]],
            [...$baseHeaderClasses, $colorClasses[1], $colorClasses[2]],
            [...$baseContainerClasses, $colorClasses[3], $colorClasses[2]],
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
