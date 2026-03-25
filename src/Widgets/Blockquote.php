<?php

declare(strict_types=1);

/**
 * Blockquote.php
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
 * Blockquote widget (citation)
 *
 * Usage:
 *   Bleet::blockquote('Famous quote')->render();
 *   Bleet::blockquote('Simplicity is the ultimate sophistication.')
 *       ->cite('Leonardo da Vinci')
 *       ->render();
 *   Bleet::blockquote('Citation')
 *       ->cite('Auteur')
 *       ->source('Livre, 2025')
 *       ->primary()
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Blockquote extends AbstractWidget implements WidgetInterface
{
    use BleetAttributesTrait;
    use SlotCaptureTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private WidgetInterface|Closure|string $content = '';
    private ?string $cite = null;
    private ?string $source = null;
    private bool $encode = true;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Sets the content of the quote
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
     * Sets the author of the quote
     */
    public function cite(string $cite): self
    {
        $new = clone $this;
        $new->cite = $cite;
        return $new;
    }

    /**
     * Sets the source (book, URL, etc.)
     */
    public function source(string $source): self
    {
        $new = clone $this;
        $new->source = $source;
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

        $innerContent = $this->renderContent();

        return Html::tag('blockquote', $innerContent, $attributes)
            ->encode(false)
            ->render();
    }

    /**
     * Generates the content interne of the blockkquote
     */
    private function renderContent(): string
    {
        $resolvedContent = $this->resolveSlot($this->content, $this->encode);

        $parts = [
            Html::tag('p', $resolvedContent)->encode(false)->render(),
        ];

        if ($this->cite !== null || $this->source !== null) {
            $parts[] = $this->renderFooter();
        }

        return implode("\n", $parts);
    }

    /**
     * Generates the footer avec auteur et source
     */
    private function renderFooter(): string
    {
        $footerClasses = ['mt-2', 'text-sm', 'not-italic', ...$this->getFooterColorClasses()];
        $footerContent = '— ';

        if ($this->cite !== null) {
            $citeContent = $this->encode
                ? htmlspecialchars($this->cite, ENT_QUOTES | ENT_HTML5)
                : $this->cite;
            $footerContent .= Html::tag('cite', $citeContent)->encode(false)->render();
        }

        if ($this->source !== null) {
            $sourceContent = $this->encode
                ? htmlspecialchars($this->source, ENT_QUOTES | ENT_HTML5)
                : $this->source;
            if ($this->cite !== null) {
                $footerContent .= ', ';
            }
            $footerContent .= $sourceContent;
        }

        return Html::tag('footer', $footerContent, ['class' => $footerClasses])
            ->encode(false)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = [
            'border-l-4',
            'pl-4',
            'py-2',
            'italic',
        ];

        return [...$baseClasses, ...$this->getBorderColorClasses(), ...$this->getTextColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getBorderColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-300'],
            Bleet::COLOR_SECONDARY => ['border-secondary-300'],
            Bleet::COLOR_SUCCESS => ['border-success-300'],
            Bleet::COLOR_DANGER => ['border-danger-300'],
            Bleet::COLOR_WARNING => ['border-warning-300'],
            Bleet::COLOR_INFO => ['border-info-300'],
            Bleet::COLOR_ACCENT => ['border-accent-300'],
        };
    }

    /**
     * @return string[]
     */
    private function getTextColorClasses(): array
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

    /**
     * @return string[]
     */
    private function getFooterColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-600'],
            Bleet::COLOR_INFO => ['text-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-600'],
        };
    }
}
