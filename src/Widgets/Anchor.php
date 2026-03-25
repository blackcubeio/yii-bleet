<?php

declare(strict_types=1);

/**
 * Anchor.php
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
 * Anchor widget (link)
 *
 * Usage:
 *   Bleet::a('Cliquez ici', '/page')->render();
 *   Bleet::a('Lien externe', 'https://example.com')->external()->render();
 *   Bleet::a('Action')->danger()->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Anchor extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private ?string $iconName = null;
    private string $iconType = 'outline';
    private ?int $badge = null;
    private ?string $originalContent = null;
    private bool $encode = true;
    private bool $outline = false;
    private bool $ghost = false;
    private bool $button = false;

    public function __construct(string $content = '', ?string $url = null)
    {
        if ($content !== '') {
            $this->originalContent = $content;
        }
        if ($url !== null) {
            $this->tagAttributes['href'] = $url;
        }
    }

    /**
     * Sets the content du lien
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->originalContent = $content;
        return $new;
    }

    /**
     * Controls HTML encoding of content
     */
    public function encode(bool $encode = true): self
    {
        $new = clone $this;
        $new->encode = $encode;
        return $new;
    }

    /**
     * Sets the icon (outline by default)
     */
    public function icon(string $name, string $type = 'outline'): self
    {
        $new = clone $this;
        $new->iconName = $name;
        $new->iconType = $type;
        return $new;
    }

    /**
     * Sets le badge (compteur)
     */
    public function badge(int $count): self
    {
        $new = clone $this;
        $new->badge = $count;
        return $new;
    }

    /**
     * Sets outline mode (no background, colored text, bg on hover)
     */
    public function outline(bool $outline = true): self
    {
        $new = clone $this;
        $new->outline = $outline;
        return $new;
    }

    /**
     * Sets ghost mode (border, no background, bg on hover)
     */
    public function ghost(bool $ghost = true): self
    {
        $new = clone $this;
        $new->ghost = $ghost;
        return $new;
    }

    /**
     * Sets button mode (filled background like Button widget)
     */
    public function button(bool $button = true): self
    {
        $new = clone $this;
        $new->button = $button;
        return $new;
    }

    /**
     * @return bool
     */
    public function hasIcon(): bool
    {
        return $this->iconName !== null;
    }

    /**
     * @return bool
     */
    public function hasBadge(): bool
    {
        return $this->badge !== null;
    }

    /**
     * Sets the URL du lien
     */
    public function url(?string $url): self
    {
        return $this->attribute('href', $url);
    }

    /**
     * Sets the href du lien
     */
    public function href(?string $href): self
    {
        return $this->attribute('href', $href);
    }

    /**
     * Sets the target attribute
     */
    public function target(?string $contextName): self
    {
        return $this->attribute('target', $contextName);
    }

    /**
     * Sets the rel attribute
     */
    public function rel(?string $rel): self
    {
        return $this->attribute('rel', $rel);
    }

    /**
     * Ouvre le lien dans un nouvel onglet (target="_blank" + rel="noopener noreferrer")
     */
    public function external(): self
    {
        $new = clone $this;
        $new->tagAttributes['target'] = '_blank';
        $new->tagAttributes['rel'] = 'noopener noreferrer';
        return $new;
    }

    public function render(): string
    {
        // Build content with icon and badge if present
        $content = $this->buildContent();

        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses());

        $hasHtmlContent = $this->iconName !== null || $this->badge !== null;
        $encode = $hasHtmlContent ? false : $this->encode;

        return Html::tag('a', $content ?? '', $attributes)
            ->encode($encode)
            ->render();
    }

    /**
     * Render as menu item (without link styling)
     * @param string ...$classes Additional classes to apply
     */
    public function renderAsMenuItem(string ...$classes): string
    {
        $attributes = $this->getTagAttributes();
        Html::addCssClass($attributes, $classes);

        return Html::tag('a', $this->originalContent ?? '', $attributes)->render();
    }

    /**
     * Build content with icon and/or badge
     */
    private function buildContent(): ?string
    {
        $parts = [];

        // Icon
        if ($this->iconName !== null) {
            $iconSize = $this->outline ? 'size-4' : 'size-6';
            $svg = $this->iconType === 'solid'
                ? Bleet::svg()->solid($this->iconName)->addClass($iconSize)
                : Bleet::svg()->outline($this->iconName)->addClass($iconSize);
            $parts[] = $svg->render();
        }

        // Original content
        if ($this->originalContent !== null) {
            $parts[] = Html::encode($this->originalContent);
        }

        // Badge
        if ($this->badge !== null) {
            $parts[] = Html::span((string) $this->badge)
                ->addClass('absolute', '-top-1', '-right-1', 'size-4', 'text-xs', 'font-bold', 'text-white', 'bg-danger-600', 'rounded-full', 'flex', 'items-center', 'justify-center')
                ->render();
        }

        if (empty($parts)) {
            return null;
        }

        return implode('', $parts);
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        // Add flex classes when icon + content or button mode
        $hasIconAndContent = $this->iconName !== null && $this->originalContent !== null;
        $flexClasses = ($hasIconAndContent || $this->button) ? ['inline-flex', 'items-center', 'gap-2'] : [];
        if ($this->button) {
            $flexClasses[] = 'justify-center';
        }

        if ($this->outline) {
            $baseClasses = [
                'cursor-pointer',
            ];
            return [...$flexClasses, ...$baseClasses, ...$this->getSizeClasses(), ...$this->getColorClasses()];
        }

        if ($this->ghost) {
            $baseClasses = [
                'rounded-md',
                'font-semibold',
                'border',
                'cursor-pointer',
                'focus-visible:ring-2',
                'focus-visible:ring-offset-2',
            ];
            return [...$flexClasses, ...$baseClasses, ...$this->getSizeClasses(), ...$this->getColorClasses()];
        }

        if ($this->button) {
            $baseClasses = [
                'rounded-md',
                'font-semibold',
                'shadow-xs',
                'text-white',
                'cursor-pointer',
                'focus-visible:ring-2',
                'focus-visible:ring-offset-2',
            ];
            return [...$flexClasses, ...$baseClasses, ...$this->getSizeClasses(), ...$this->getColorClasses()];
        }

        // If icon only (no text content), no underline
        $isIconOnly = $this->iconName !== null && $this->originalContent === null;

        $baseClasses = $isIconOnly
            ? ['relative', 'focus-visible:ring-2', 'focus-visible:ring-offset-2']
            : ['underline', 'focus-visible:ring-2', 'focus-visible:ring-offset-2'];

        return [...$flexClasses, ...$baseClasses, ...$this->getSizeClasses(), ...$this->getColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getSizeClasses(): array
    {
        if ($this->outline) {
            return match ($this->size) {
                Bleet::SIZE_XS => ['p-1'],
                Bleet::SIZE_SM => ['p-1.5'],
                Bleet::SIZE_MD => ['p-2'],
                Bleet::SIZE_LG => ['p-2.5'],
                Bleet::SIZE_XL => ['p-3'],
            };
        }

        if ($this->ghost || $this->button) {
            return match ($this->size) {
                Bleet::SIZE_XS => ['px-2', 'py-1', 'text-xs'],
                Bleet::SIZE_SM => ['px-3', 'py-2', 'text-sm'],
                Bleet::SIZE_MD => ['px-4', 'py-2.5', 'text-sm'],
                Bleet::SIZE_LG => ['px-5', 'py-3', 'text-base'],
                Bleet::SIZE_XL => ['px-6', 'py-4', 'text-lg'],
            };
        }

        return match ($this->size) {
            Bleet::SIZE_XS => ['text-xs'],
            Bleet::SIZE_SM => ['text-sm'],
            Bleet::SIZE_MD => ['text-base'],
            Bleet::SIZE_LG => ['text-lg'],
            Bleet::SIZE_XL => ['text-xl'],
        };
    }

    /**
     * @return string[]
     */
    private function getColorClasses(): array
    {
        if ($this->outline) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:bg-primary-700', 'hover:text-white'],
                Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:bg-secondary-700', 'hover:text-white'],
                Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:bg-success-700', 'hover:text-white'],
                Bleet::COLOR_DANGER => ['text-danger-700', 'hover:bg-danger-700', 'hover:text-white'],
                Bleet::COLOR_WARNING => ['text-warning-700', 'hover:bg-warning-700', 'hover:text-white'],
                Bleet::COLOR_INFO => ['text-info-700', 'hover:bg-info-700', 'hover:text-white'],
                Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:bg-accent-700', 'hover:text-white'],
            };
        }

        if ($this->ghost) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['text-primary-700', 'border-primary-300', 'hover:bg-primary-600', 'hover:text-white', 'hover:border-primary-600', 'focus-visible:ring-primary-600'],
                Bleet::COLOR_SECONDARY => ['text-secondary-700', 'border-secondary-300', 'hover:bg-secondary-600', 'hover:text-white', 'hover:border-secondary-600', 'focus-visible:ring-secondary-600'],
                Bleet::COLOR_SUCCESS => ['text-success-700', 'border-success-300', 'hover:bg-success-600', 'hover:text-white', 'hover:border-success-600', 'focus-visible:ring-success-600'],
                Bleet::COLOR_DANGER => ['text-danger-700', 'border-danger-300', 'hover:bg-danger-600', 'hover:text-white', 'hover:border-danger-600', 'focus-visible:ring-danger-600'],
                Bleet::COLOR_WARNING => ['text-warning-700', 'border-warning-300', 'hover:bg-warning-600', 'hover:text-white', 'hover:border-warning-600', 'focus-visible:ring-warning-600'],
                Bleet::COLOR_INFO => ['text-info-700', 'border-info-300', 'hover:bg-info-600', 'hover:text-white', 'hover:border-info-600', 'focus-visible:ring-info-600'],
                Bleet::COLOR_ACCENT => ['text-accent-700', 'border-accent-300', 'hover:bg-accent-600', 'hover:text-white', 'hover:border-accent-600', 'focus-visible:ring-accent-600'],
            };
        }

        if ($this->button) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['bg-primary-600', 'hover:bg-primary-700', 'focus-visible:ring-primary-600'],
                Bleet::COLOR_SECONDARY => ['bg-secondary-600', 'hover:bg-secondary-700', 'focus-visible:ring-secondary-600'],
                Bleet::COLOR_SUCCESS => ['bg-success-600', 'hover:bg-success-700', 'focus-visible:ring-success-600'],
                Bleet::COLOR_DANGER => ['bg-danger-600', 'hover:bg-danger-700', 'focus-visible:ring-danger-600'],
                Bleet::COLOR_WARNING => ['bg-warning-600', 'hover:bg-warning-700', 'focus-visible:ring-warning-600'],
                Bleet::COLOR_INFO => ['bg-info-600', 'hover:bg-info-700', 'focus-visible:ring-info-600'],
                Bleet::COLOR_ACCENT => ['bg-accent-600', 'hover:bg-accent-700', 'focus-visible:ring-accent-600'],
            };
        }

        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:text-primary-600', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:text-secondary-600', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:text-success-600', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'hover:text-danger-600', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'hover:text-warning-600', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-700', 'hover:text-info-600', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:text-accent-600', 'focus-visible:ring-accent-600'],
        };
    }
}
