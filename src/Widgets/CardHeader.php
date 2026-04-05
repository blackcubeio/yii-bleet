<?php

declare(strict_types=1);

/**
 * CardHeader.php
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
use Stringable;
use Yiisoft\Html\Html;

/**
 * CardHeader widget - Colored header with icon, title, badges and optional button
 *
 * Usage:
 *   Bleet::cardHeader()->title('Rubriques')->icon('document-text')->primary()->render()
 *   Bleet::cardHeader()
 *       ->icon('document-text')
 *       ->title('Contenus')
 *       ->badges([
 *           Bleet::badge('45')->dot()->success(),
 *           Bleet::badge('0')->dot()->danger(),
 *       ])
 *       ->primary()
 *       ->render()
 *   Bleet::cardHeader()
 *       ->icon('document-text')
 *       ->title('Rubriques')
 *       ->button(Bleet::button()->icon('plus')->accent())
 *       ->primary()
 *       ->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class CardHeader extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $title = '';
    private ?string $icon = null;
    private Stringable|string|null $left = null;
    /** @var Stringable[] */
    private array $badges = [];
    private Stringable|string|null $button = null;

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
     * Sets the icon (nom heroicon outline)
     */
    public function icon(string $icon): self
    {
        $new = clone $this;
        $new->icon = $icon;
        return $new;
    }

    /**
     * Sets the left content (widget ou string)
     */
    public function left(Stringable|string $left): self
    {
        $new = clone $this;
        $new->left = $left;
        return $new;
    }

    /**
     * Sets les badges
     * @param Stringable[] $badges
     */
    public function badges(array $badges): self
    {
        $new = clone $this;
        $new->badges = $badges;
        return $new;
    }

    /**
     * Sets the button d'action
     */
    public function button(Stringable|string $button): self
    {
        $new = clone $this;
        $new->button = $button;
        return $new;
    }

    public function render(): string
    {
        $html = '';

        // Left part: left widget + icon + title + badges
        $leftHtml = '';

        // Left widget (ex: Bleet::a()->icon('chevron-left'))
        if ($this->left !== null) {
            $leftHtml .= (string) $this->left;
        }

        // Icon circle
        if ($this->icon !== null) {
            $iconSvg = Bleet::svg()->outline($this->icon)->addClass('size-5', 'text-white');
            $leftHtml .= Html::div($iconSvg, ['class' => $this->getIconWrapperClasses()])
                ->encode(false)
                ->render();
        }

        // Title + badges container
        $titleContent = '';

        // Title
        if (!empty($this->title)) {
            $titleContent .= Html::tag('h3', Html::encode($this->title), ['class' => ['text-lg', 'font-semibold', 'text-white']])
                ->encode(false)
                ->render();
        }

        // Badges
        if (!empty($this->badges)) {
            $badgesHtml = '';
            foreach ($this->badges as $badge) {
                $badgesHtml .= (string) $badge;
            }
            $titleContent .= Html::div($badgesHtml, ['class' => ['flex', 'items-center', 'gap-2', 'mt-1']])
                ->encode(false)
                ->render();
        }

        if (!empty($titleContent)) {
            $leftHtml .= Html::div($titleContent)->encode(false)->render();
        }

        $html .= Html::div($leftHtml, ['class' => ['flex', 'items-center', 'gap-4']])
            ->encode(false)
            ->render();

        // Right part: button
        if ($this->button !== null) {
            $html .= (string) $this->button;
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
        $baseClasses = ['flex', 'items-center', 'justify-between', 'gap-4', 'p-4', 'rounded-t-lg'];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-700'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-700'],
            Bleet::COLOR_ACCENT => ['bg-accent-700'],
            Bleet::COLOR_SUCCESS => ['bg-success-700'],
            Bleet::COLOR_DANGER => ['bg-danger-700'],
            Bleet::COLOR_WARNING => ['bg-warning-700'],
            Bleet::COLOR_INFO => ['bg-info-700'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getIconWrapperClasses(): array
    {
        return ['flex', 'items-center', 'justify-center', 'w-10', 'h-10', 'bg-white/20', 'rounded-lg'];
    }
}
