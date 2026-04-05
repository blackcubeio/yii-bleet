<?php

declare(strict_types=1);

/**
 * Alert.php
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
use InvalidArgumentException;
use Yiisoft\Html\Html;

/**
 * Alert widget - Inline alert messages
 *
 * Generates contextual alerts with icon, message and optional close button.
 * Supports 4 semantic types: info, warning, success, danger.
 *
 * Usage:
 *   Bleet::alert()->content('Simpthe message')->render();
 *   Bleet::alert()->title('Attention')->content('Detailed message')->warning()->render();
 *   Bleet::alert()->content('Fermable')->dismissible()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Alert extends AbstractWidget implements WidgetInterface
{
    use BleetAttributesTrait;
    use SlotCaptureTrait;
    private const ALLOWED_COLORS = [
        Bleet::COLOR_INFO,
        Bleet::COLOR_SUCCESS,
        Bleet::COLOR_WARNING,
        Bleet::COLOR_DANGER,
    ];

    private const DEFAULT_ICONS = [
        Bleet::COLOR_INFO => 'information-circle',
        Bleet::COLOR_SUCCESS => 'check-circle',
        Bleet::COLOR_WARNING => 'exclamation-triangle',
        Bleet::COLOR_DANGER => 'x-circle',
    ];

    protected string $color = Bleet::COLOR_INFO;

    private ?string $title = null;
    private WidgetInterface|Closure|string $content = '';
    private ?string $icon = null;
    private bool $dismissible = false;

    /**
     * Override color to restrict to 4 semantic colors
     */
    public function color(string $color): static
    {
        if (!in_array($color, self::ALLOWED_COLORS, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid color "%s" for Alert. Valid: %s', $color, implode(', ', self::ALLOWED_COLORS))
            );
        }

        $new = clone $this;
        $new->color = $color;
        return $new;
    }

    /**
     * Sets the title (h3 tag)
     */
    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    /**
     * Sets the content/message
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
     * Sets the icon manuellement (override auto-selection)
     */
    public function icon(string $icon): self
    {
        $new = clone $this;
        $new->icon = $icon;
        return $new;
    }

    /**
     * Enables the button de fermeture
     */
    public function dismissible(bool $dismissible = true): self
    {
        $new = clone $this;
        $new->dismissible = $dismissible;
        return $new;
    }

    public function render(): string
    {
        $iconName = $this->icon ?? self::DEFAULT_ICONS[$this->color];

        // Icon
        $iconHtml = Svg::heroicon()
            ->solid($iconName)
            ->addClass(...$this->getIconClasses())
            ->render();

        $iconContainer = Html::div($iconHtml)
            ->encode(false)
            ->class('shrink-0')
            ->render();

        // Content
        $contentHtml = $this->renderContent();

        $contentContainerClasses = $this->dismissible ? ['ml-3', 'flex-1'] : ['ml-3'];
        $contentContainer = Html::div($contentHtml)
            ->encode(false)
            ->class(...$contentContainerClasses)
            ->render();

        // Dismiss button
        $dismissHtml = '';
        if ($this->dismissible) {
            $dismissHtml = $this->renderDismissButton();
        }

        // Wrapper flex
        $wrapperContent = $iconContainer . $contentContainer . $dismissHtml;
        $wrapper = Html::div($wrapperContent)
            ->encode(false)
            ->class('flex')
            ->render();

        // Container
        $attributes = $this->prepareTagAttributes(
            $this->dismissible ? ['bleet-alert' => ''] : []
        );
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::div($wrapper)
            ->attributes($attributes)
            ->encode(false)
            ->render();
    }

    private function renderContent(): string
    {
        $html = '';
        $resolvedContent = $this->resolveSlot($this->content);

        if ($this->title !== null) {
            $html .= Html::tag('h3', $this->title)
                ->class(...$this->getTitleClasses())
                ->render();

            if ($resolvedContent !== '') {
                $html .= Html::div($resolvedContent)
                    ->encode(false)
                    ->class(...$this->getContentWrapperClasses())
                    ->render();
            }
        } else {
            if ($resolvedContent !== '') {
                $html .= Html::tag('p', $resolvedContent)
                    ->encode(false)
                    ->class(...$this->getMessageClasses())
                    ->render();
            }
        }

        return $html;
    }

    private function renderDismissButton(): string
    {
        $closeIcon = Svg::heroicon()
            ->solid('x-mark')
            ->addClass('size-5')
            ->render();

        $srText = Html::tag('span', 'Fermer')
            ->class('sr-only')
            ->render();

        $button = Html::button($srText . $closeIcon)
            ->encode(false)
            ->type('button')
            ->attribute('data-alert', 'close')
            ->class(...$this->getDismissButtonClasses())
            ->render();

        $buttonWrapper = Html::div($button)
            ->encode(false)
            ->class('-mx-1.5', '-my-1.5')
            ->render();

        return Html::div($buttonWrapper)
            ->encode(false)
            ->class('ml-auto', 'pl-3')
            ->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $baseClasses = ['border-l-4', 'p-4', 'transition-all', 'duration-300', 'opacity-100'];
        return [...$baseClasses, ...$this->getContainerColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getContainerColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_INFO => ['border-info-400', 'bg-info-50'],
            Bleet::COLOR_SUCCESS => ['border-success-400', 'bg-success-50'],
            Bleet::COLOR_WARNING => ['border-warning-400', 'bg-warning-50'],
            Bleet::COLOR_DANGER => ['border-danger-400', 'bg-danger-50'],
            default => ['border-info-400', 'bg-info-50'],
        };
    }

    /**
     * @return string[]
     */
    private function getIconClasses(): array
    {
        $baseClasses = ['size-5'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_INFO => ['text-info-400'],
            Bleet::COLOR_SUCCESS => ['text-success-400'],
            Bleet::COLOR_WARNING => ['text-warning-400'],
            Bleet::COLOR_DANGER => ['text-danger-400'],
            default => ['text-info-400'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getTitleClasses(): array
    {
        $baseClasses = ['text-sm', 'font-medium'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_INFO => ['text-info-800'],
            Bleet::COLOR_SUCCESS => ['text-success-800'],
            Bleet::COLOR_WARNING => ['text-warning-800'],
            Bleet::COLOR_DANGER => ['text-danger-800'],
            default => ['text-info-800'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getContentWrapperClasses(): array
    {
        $baseClasses = ['mt-2', 'text-sm'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            default => ['text-info-700'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getMessageClasses(): array
    {
        $baseClasses = ['text-sm'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            default => ['text-info-700'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getDismissButtonClasses(): array
    {
        $baseClasses = [
            'inline-flex',
            'rounded-md',
            'p-1.5',
            'cursor-pointer',
            'focus-visible:outline',
            'focus-visible:ring-2',
            'focus-visible:ring-offset-2',
        ];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_INFO => ['bg-info-50', 'text-info-500', 'hover:bg-info-100', 'focus-visible:ring-info-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-50', 'text-success-500', 'hover:bg-success-100', 'focus-visible:ring-success-600'],
            Bleet::COLOR_WARNING => ['bg-warning-50', 'text-warning-500', 'hover:bg-warning-100', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_DANGER => ['bg-danger-50', 'text-danger-500', 'hover:bg-danger-100', 'focus-visible:ring-danger-600'],
            default => ['bg-info-50', 'text-info-500', 'hover:bg-info-100', 'focus-visible:ring-info-600'],
        };
        return [...$baseClasses, ...$colorClasses];
    }
}
