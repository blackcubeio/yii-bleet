<?php

declare(strict_types=1);

/**
 * Img.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Yiisoft\Html\Html;

/**
 * Img widget (styled image tag)
 *
 * Usage:
 *   Bleet::img('/path/to/image.jpg', 'Description')->render();
 *   Bleet::img('/path/to/image.jpg', 'Description')->rounded()->render();
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Img extends AbstractWidget
{
    use BleetAttributesTrait;

    private string $src = '';
    private string $alt = '';
    private bool $rounded = false;

    public function __construct(string $src = '', string $alt = '')
    {
        $this->src = $src;
        $this->alt = $alt;
    }

    public function src(string $src): self
    {
        $new = clone $this;
        $new->src = $src;
        return $new;
    }

    public function alt(string $alt): self
    {
        $new = clone $this;
        $new->alt = $alt;
        return $new;
    }

    public function rounded(): self
    {
        $new = clone $this;
        $new->rounded = true;
        return $new;
    }

    public function render(): string
    {
        if ($this->src === '') {
            return '';
        }

        $attributes = $this->prepareTagAttributes([
            'src' => $this->src,
            'alt' => $this->alt,
        ]);
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::img()
            ->attributes($attributes)
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = ['h-auto'];

        if ($this->rounded) {
            $baseClasses[] = 'rounded-lg';
        }

        return $baseClasses;
    }
}
