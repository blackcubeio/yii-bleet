<?php

declare(strict_types=1);

/**
 * Figure.php
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
 * Figure widget (image with optional caption)
 *
 * Usage:
 *   Bleet::figure('/path/to/image.jpg', 'Alt text')->render();
 *   Bleet::figure('/path/to/image.jpg', 'Alt text')->caption('Caption')->render();
 *   Bleet::figure('/path/to/image.jpg', 'Alt text')->caption('Titre')->description('Details')->primary()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Figure extends AbstractWidget implements WidgetInterface
{
    use BleetAttributesTrait;
    use SlotCaptureTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $src = '';
    private string $alt = '';
    private WidgetInterface|Closure|string|null $caption = null;
    private ?string $description = null;
    private ?string $prefix = null;
    private bool $center = false;
    private bool $rounded = false;

    public function __construct(string $src = '', string $alt = '')
    {
        $this->src = $src;
        $this->alt = $alt;
    }

    /**
     * Sets the source de the image
     */
    public function src(string $src): self
    {
        $new = clone $this;
        $new->src = $src;
        return $new;
    }

    /**
     * Sets le texte alternatif
     */
    public function alt(string $alt): self
    {
        $new = clone $this;
        $new->alt = $alt;
        return $new;
    }

    /**
     * Sets the caption (figcaption)
     */
    public function caption(WidgetInterface|Closure|string $caption): self
    {
        $new = clone $this;
        $new->caption = $caption;
        return $new;
    }

    public function beginCaption(): static
    {
        return $this->beginSlot('caption');
    }

    public function endCaption(): static
    {
        $new = $this->endSlot();
        $new->caption = $new->getSlot('caption');
        return $new;
    }

    /**
     * Sets the description (enables detailed mode with colored background)
     */
    public function description(string $description): self
    {
        $new = clone $this;
        $new->description = $description;
        return $new;
    }

    /**
     * Sets the prefix (e.g., "Figure 1.")
     */
    public function prefix(string $prefix): self
    {
        $new = clone $this;
        $new->prefix = $prefix;
        return $new;
    }

    /**
     * Centre le caption
     */
    public function center(): self
    {
        $new = clone $this;
        $new->center = true;
        return $new;
    }

    /**
     * Enables rounded corners sur the image
     */
    public function rounded(): self
    {
        $new = clone $this;
        $new->rounded = true;
        return $new;
    }

    public function render(): string
    {
        $figureAttributes = $this->prepareTagAttributes();
        Html::addCssClass($figureAttributes, $this->prepareClasses());

        // Build image
        $img = Bleet::img($this->src, $this->alt);
        if ($this->rounded) {
            $img = $img->rounded();
        }
        if ($this->description !== null) {
            $img = $img->addClass('w-full', 'shadow-lg');
        } else {
            $img = $img->addClass('w-full');
        }

        $content = $img->render();

        // Build figcaption if needed
        $resolvedCaption = $this->resolveSlot($this->caption);
        if ($resolvedCaption !== '' || $this->description !== null) {
            $content .= $this->renderFigcaption();
        }

        return Html::tag('figure', $content, $figureAttributes)->encode(false)->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        // Detailed mode (with description): colored background
        if ($this->description !== null) {
            return [
                'p-4',
                'rounded-lg',
                ...$this->getBackgroundClasses(),
            ];
        }

        return [];
    }

    private function renderFigcaption(): string
    {
        $captionClasses = $this->getFigcaptionClasses();
        $captionContent = '';

        // Prefix
        if ($this->prefix !== null) {
            $prefixClasses = $this->getPrefixClasses();
            $captionContent .= Html::tag('span', $this->prefix . ' ', ['class' => $prefixClasses])->encode(false)->render();
        }

        // Caption
        $resolvedCaption = $this->resolveSlot($this->caption);
        if ($resolvedCaption !== '') {
            $captionContent .= $resolvedCaption;
        }

        // Description
        if ($this->description !== null) {
            if ($resolvedCaption !== '') {
                $captionContent .= ' ';
            }
            $captionContent .= $this->description;
        }

        return Html::tag('figcaption', $captionContent, ['class' => $captionClasses])->encode(false)->render();
    }

    /**
     * @return string[]
     */
    private function getBackgroundClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-50'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50'],
            Bleet::COLOR_SUCCESS => ['bg-success-50'],
            Bleet::COLOR_DANGER => ['bg-danger-50'],
            Bleet::COLOR_WARNING => ['bg-warning-50'],
            Bleet::COLOR_INFO => ['bg-info-50'],
            Bleet::COLOR_ACCENT => ['bg-accent-50'],
        };
    }

    /**
     * @return string[]
     */
    private function getFigcaptionClasses(): array
    {
        $classes = ['mt-3', 'text-sm', 'text-secondary-600'];

        if ($this->center) {
            $classes[] = 'text-center';
        }

        if ($this->description !== null) {
            $classes = ['mt-4', 'text-sm', 'text-secondary-600'];
            if ($this->center) {
                $classes[] = 'text-center';
            }
        }

        return $classes;
    }

    /**
     * @return string[]
     */
    private function getPrefixClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['font-semibold', 'text-primary-700'],
            Bleet::COLOR_SECONDARY => ['font-semibold', 'text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['font-semibold', 'text-success-700'],
            Bleet::COLOR_DANGER => ['font-semibold', 'text-danger-700'],
            Bleet::COLOR_WARNING => ['font-semibold', 'text-warning-700'],
            Bleet::COLOR_INFO => ['font-semibold', 'text-info-700'],
            Bleet::COLOR_ACCENT => ['font-semibold', 'text-accent-700'],
        };
    }
}
