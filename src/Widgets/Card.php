<?php

declare(strict_types=1);

/**
 * Card.php
 *
 * PHP Version 8.1
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
use Stringable;
use Yiisoft\Html\Html;

/**
 * Card widget (container with optional header/footer)
 *
 * Usage:
 *   Bleet::card('Contenu')->render();
 *   Bleet::card('Contenu')->title('Titre')->render();
 *   Bleet::card('Contenu')->title('Titre')->description('Desc')->headerButton('Action', '/url')->render();
 *   Bleet::card('Contenu')->footer('Texte footer')->footerButton('Voir', '/url')->render();
 *   Bleet::card()->header(Bleet::cardHeader()->title('Titre')->primary())->content($widget)->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Card extends AbstractWidget implements WidgetInterface
{
    use BleetAttributesTrait;
    use SlotCaptureTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    protected Stringable|WidgetInterface|Closure|string $content = '';
    protected Stringable|WidgetInterface|Closure|string|null $header = null;
    protected ?string $title = null;
    protected ?string $description = null;
    protected ?string $headerButtonLabel = null;
    protected ?string $headerButtonUrl = null;
    protected Stringable|WidgetInterface|Closure|string|null $footer = null;
    protected ?string $footerButtonLabel = null;
    protected ?string $footerButtonUrl = null;
    protected bool $encode = true;

    public function __construct(Stringable|WidgetInterface|Closure|string $content = '')
    {
        $this->content = $content;
    }

    // ========== CONTENT ==========

    /**
     * Sets the content of the card (accepts a widget, closure or string)
     */
    public function content(Stringable|WidgetInterface|Closure|string $content): self
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

    // ========== HEADER ==========

    /**
     * Sets the header slot (accepts a CardHeader, widget, closure or string)
     * Note: If set, takes priority over title/description/headerButton
     */
    public function header(Stringable|WidgetInterface|Closure|string $header): self
    {
        $new = clone $this;
        $new->header = $header;
        return $new;
    }

    public function beginHeader(): static
    {
        return $this->beginSlot('header');
    }

    public function endHeader(): static
    {
        $new = $this->endSlot();
        $new->header = $new->getSlot('header');
        return $new;
    }

    /**
     * Sets the title (header)
     */
    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    /**
     * Sets the description (sous the title)
     */
    public function description(string $description): self
    {
        $new = clone $this;
        $new->description = $description;
        return $new;
    }

    /**
     * Sets the button of the header
     */
    public function headerButton(string $label, string $url): self
    {
        $new = clone $this;
        $new->headerButtonLabel = $label;
        $new->headerButtonUrl = $url;
        return $new;
    }

    // ========== FOOTER ==========

    /**
     * Sets the footer (accepts a widget, closure or string)
     */
    public function footer(Stringable|WidgetInterface|Closure|string $footer): self
    {
        $new = clone $this;
        $new->footer = $footer;
        return $new;
    }

    public function beginFooter(): static
    {
        return $this->beginSlot('footer');
    }

    public function endFooter(): static
    {
        $new = $this->endSlot();
        $new->footer = $new->getSlot('footer');
        return $new;
    }

    /**
     * Sets the button of the footer
     */
    public function footerButton(string $label, string $url): self
    {
        $new = clone $this;
        $new->footerButtonLabel = $label;
        $new->footerButtonUrl = $url;
        return $new;
    }

    /**
     * Disables HTML encoding of the content
     */
    public function encode(bool $encode = true): self
    {
        $new = clone $this;
        $new->encode = $encode;
        return $new;
    }

    public function render(): string
    {
        $html = '';

        // (Header slot (takes priority over title)
        if ($this->header !== null) {
            $html .= $this->resolveSlot($this->header, $this->encode);
        } elseif ($this->title !== null) {
            $html .= $this->renderHeaderBlock();
        }

        // Content
        $html .= $this->renderContentBlock();

        // Footer
        if ($this->footer !== null || $this->footerButtonLabel !== null) {
            $html .= $this->renderFooterBlock();
        }

        // Container
        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::div($html)
            ->attributes($attributes)
            ->encode(false)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = [
            'overflow-hidden',
            'bg-white',
            'rounded-lg',
            'shadow-lg',
            'border',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-200'],
            Bleet::COLOR_SECONDARY => ['border-secondary-200'],
            Bleet::COLOR_ACCENT => ['border-accent-200'],
            Bleet::COLOR_SUCCESS => ['border-success-200'],
            Bleet::COLOR_DANGER => ['border-danger-200'],
            Bleet::COLOR_WARNING => ['border-warning-200'],
            Bleet::COLOR_INFO => ['border-info-200'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    private function renderHeaderBlock(): string
    {
        $headerContent = '';

        // Title + description
        $titleHtml = Html::tag('h3', $this->title)
            ->class('text-base', 'font-semibold', 'text-primary-900')
            ->render();

        if ($this->description !== null) {
            $titleHtml .= Html::tag('p', $this->description)
                ->class('mt-1', 'text-sm', 'text-primary-600')
                ->render();
        }

        $headerContent .= Html::div($titleHtml)
            ->encode(false)
            ->class('mt-4', 'ml-4')
            ->render();

        // Header button
        if ($this->headerButtonLabel !== null) {
            $buttonClasses = $this->getButtonClasses();
            $buttonHtml = Html::a($this->headerButtonLabel, $this->headerButtonUrl)
                ->class(...$buttonClasses)
                ->render();

            $headerContent .= Html::div($buttonHtml)
                ->encode(false)
                ->class('mt-4', 'ml-4', 'shrink-0')
                ->render();
        }

        // Flex container
        $flexHtml = Html::div($headerContent)
            ->encode(false)
            ->class('-mt-4', '-ml-4', 'flex', 'flex-wrap', 'items-center', 'justify-between', 'sm:flex-nowrap')
            ->render();

        // Header wrapper
        $borderClasses = $this->getBorderClasses();

        return Html::div($flexHtml)
            ->encode(false)
            ->class('px-4', 'py-5', 'sm:px-6', 'border-b', ...$borderClasses)
            ->render();
    }

    private function renderContentBlock(): string
    {
        $content = $this->resolveSlot($this->content, $this->encode);

        return Html::div($content)
            ->encode(false)
            ->class('px-4', 'py-5', 'sm:px-6')
            ->render();
    }

    private function renderFooterBlock(): string
    {
        $footerContent = '';

        // Footer slot or simple text
        if ($this->footer !== null) {
            $resolved = $this->resolveSlot($this->footer, $this->encode);
            // If complex slot (widget/closure), use it directly
            if ($this->footer instanceof WidgetInterface || $this->footer instanceof Closure) {
                $footerContent .= $resolved;
            } else {
                // Otherwise, wrap in a <p>
                $footerContent .= Html::tag('p', $resolved)
                    ->encode(false)
                    ->class('text-sm', 'text-secondary-600')
                    ->render();
            }
        }

        // Footer button
        if ($this->footerButtonLabel !== null) {
            $buttonClasses = $this->getButtonClasses();
            $footerContent .= Html::a($this->footerButtonLabel, $this->footerButtonUrl)
                ->class(...$buttonClasses)
                ->render();
        }

        // Footer wrapper
        $borderClasses = $this->getBorderClasses();

        $footerClasses = ['px-4', 'py-4', 'sm:px-6', 'border-t', ...$borderClasses];

        // If text AND button, flex justify-between
        if ($this->footer !== null && $this->footerButtonLabel !== null) {
            $footerClasses = [...$footerClasses, 'flex', 'items-center', 'justify-between'];
        }

        return Html::div($footerContent)
            ->encode(false)
            ->class(...$footerClasses)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function getBorderClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-200'],
            Bleet::COLOR_SECONDARY => ['border-secondary-200'],
            Bleet::COLOR_ACCENT => ['border-accent-200'],
            Bleet::COLOR_SUCCESS => ['border-success-200'],
            Bleet::COLOR_DANGER => ['border-danger-200'],
            Bleet::COLOR_WARNING => ['border-warning-200'],
            Bleet::COLOR_INFO => ['border-info-200'],
        };
    }

    /**
     * @return string[]
     */
    protected function getButtonClasses(): array
    {
        $baseClasses = [
            'relative',
            'inline-flex',
            'items-center',
            'rounded-md',
            'px-3',
            'py-2',
            'text-sm',
            'font-semibold',
            'text-white',
            'shadow-xs',
            'focus-visible:ring-2',
            'focus-visible:ring-offset-2',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600', 'hover:bg-primary-700', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-600', 'hover:bg-secondary-700', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_ACCENT => ['bg-accent-600', 'hover:bg-accent-700', 'focus-visible:ring-accent-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-600', 'hover:bg-success-700', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['bg-danger-600', 'hover:bg-danger-700', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['bg-warning-600', 'hover:bg-warning-700', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['bg-info-600', 'hover:bg-info-700', 'focus-visible:ring-info-600'],
        };

        return [...$baseClasses, ...$colorClasses];
    }
}
