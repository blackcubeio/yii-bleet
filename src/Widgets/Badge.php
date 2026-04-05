<?php

declare(strict_types=1);

/**
 * Badge.php
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
 * Badge widget - Inline label/tag component
 *
 * Displays a small badge with various styles:
 * - Colors (primary, secondary, success, danger, warning, info)
 * - Pill style (fully rounded)
 * - Dot indicator (status indicator)
 * - Remove button (for tags)
 *
 * Usage:
 *   Bleet::badge('Label')->render()
 *   Bleet::badge('Status')->dot()->success()->render()
 *   Bleet::badge('Tag')->removable()->render()
 *   Bleet::badge('Pill')->pill()->danger()->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Badge extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private string $content = '';
    private bool $pill = false;
    private bool $dot = false;
    private bool $removable = false;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Sets the content du badge
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Enables le style pill (arrondi complet)
     */
    public function pill(bool $pill = true): self
    {
        $new = clone $this;
        $new->pill = $pill;
        return $new;
    }

    /**
     * Enables l'indicateur dot (pastille de statut)
     */
    public function dot(bool $dot = true): self
    {
        $new = clone $this;
        $new->dot = $dot;
        return $new;
    }

    /**
     * Enables the button de suppression (pour les tags)
     */
    public function removable(bool $removable = true): self
    {
        $new = clone $this;
        $new->removable = $removable;
        return $new;
    }

    public function render(): string
    {
        // Remove takes priority over dot
        $showDot = $this->dot && !$this->removable;

        $innerHtml = '';

        // Dot SVG
        if ($showDot) {
            $innerHtml .= Bleet::icon()->ui('dot')->addClass(...$this->getDotClasses());
        }

        // Content
        $innerHtml .= Html::encode($this->content);

        // Remove button
        if ($this->removable) {
            $innerHtml .= $this->renderRemoveButton();
        }

        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses());

        // Adds Aurelia attribute if removable
        if ($this->removable) {
            $attributes = array_merge($attributes, ['bleet-badge' => $attributes['id'] ?? '']);
        }

        return Html::tag('span', $innerHtml, $attributes)->encode(false)->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        // Remove takes priority over dot
        $showDot = $this->dot && !$this->removable;

        $baseClasses = ['inline-flex', 'items-center', 'text-center', 'px-2', 'py-1', 'text-xs', 'font-medium'];

        // Rounded
        $roundedClasses = $this->pill ? ['rounded-full'] : ['rounded-md'];

        // Gap
        $gapClasses = [];
        if ($showDot) {
            $gapClasses = ['gap-x-1.5'];
        } elseif ($this->removable) {
            $gapClasses = ['gap-x-0.5'];
        }

        return [...$baseClasses, ...$roundedClasses, ...$gapClasses, ...$this->getColorClasses($showDot)];
    }

    /**
     * @return string[]
     */
    private function getColorClasses(bool $showDot): array
    {
        // Badge with dot without explicit color: neutral style
        if ($showDot) {
            return ['bg-white', 'text-primary-700', 'ring-1', 'ring-inset', 'ring-primary-200'];
        }

        $baseClasses = ['ring-1', 'ring-inset'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-50', 'text-primary-700', 'ring-primary-500/10'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50', 'text-secondary-700', 'ring-secondary-500/10'],
            Bleet::COLOR_SUCCESS => ['bg-success-50', 'text-success-700', 'ring-success-500/10'],
            Bleet::COLOR_DANGER => ['bg-danger-50', 'text-danger-700', 'ring-danger-500/10'],
            Bleet::COLOR_WARNING => ['bg-warning-50', 'text-warning-700', 'ring-warning-500/10'],
            Bleet::COLOR_INFO => ['bg-info-50', 'text-info-700', 'ring-info-500/10'],
            Bleet::COLOR_ACCENT => ['bg-accent-50', 'text-accent-700', 'ring-accent-500/10'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getDotClasses(): array
    {
        $baseClasses = ['size-1.5'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['fill-primary-500'],
            Bleet::COLOR_SECONDARY => ['fill-secondary-500'],
            Bleet::COLOR_SUCCESS => ['fill-success-500'],
            Bleet::COLOR_DANGER => ['fill-danger-500'],
            Bleet::COLOR_WARNING => ['fill-warning-500'],
            Bleet::COLOR_INFO => ['fill-info-500'],
            Bleet::COLOR_ACCENT => ['fill-accent-500'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    private function renderRemoveButton(): string
    {
        $buttonClasses = $this->getRemoveButtonClasses();
        $svgClasses = $this->getRemoveSvgClasses();

        $buttonHtml = Html::openTag('button', ['type' => 'button', 'class' => $buttonClasses, 'data-badge' => 'remove']);
        $buttonHtml .= Html::tag('span', 'Remove', ['class' => 'sr-only']);
        $buttonHtml .= Bleet::svg()->solid('x-mark')->addClass(...$svgClasses);
        $buttonHtml .= Html::tag('span', '', ['class' => 'absolute -inset-1']);
        $buttonHtml .= Html::closeTag('button');

        return $buttonHtml;
    }

    /**
     * @return string[]
     */
    private function getRemoveButtonClasses(): array
    {
        $baseClasses = ['group', 'relative', '-mr-1', 'size-3.5', 'rounded-sm', 'cursor-pointer'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['hover:bg-primary-500/20'],
            Bleet::COLOR_SECONDARY => ['hover:bg-secondary-500/20'],
            Bleet::COLOR_SUCCESS => ['hover:bg-success-500/20'],
            Bleet::COLOR_DANGER => ['hover:bg-danger-500/20'],
            Bleet::COLOR_WARNING => ['hover:bg-warning-500/20'],
            Bleet::COLOR_INFO => ['hover:bg-info-500/20'],
            Bleet::COLOR_ACCENT => ['hover:bg-accent-500/20'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getRemoveSvgClasses(): array
    {
        $baseClasses = ['size-3.5'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700/50', 'group-hover:text-primary-700/75'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700/50', 'group-hover:text-secondary-700/75'],
            Bleet::COLOR_SUCCESS => ['text-success-700/50', 'group-hover:text-success-700/75'],
            Bleet::COLOR_DANGER => ['text-danger-700/50', 'group-hover:text-danger-700/75'],
            Bleet::COLOR_WARNING => ['text-warning-700/50', 'group-hover:text-warning-700/75'],
            Bleet::COLOR_INFO => ['text-info-700/50', 'group-hover:text-info-700/75'],
            Bleet::COLOR_ACCENT => ['text-accent-700/50', 'group-hover:text-accent-700/75'],
        };
        return [...$baseClasses, ...$colorClasses];
    }
}
