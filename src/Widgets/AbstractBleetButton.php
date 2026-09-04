<?php

declare(strict_types=1);

/**
 * AbstractBleetButton.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Yiisoft\Html\Html;
use Blackcube\Icons\Svg;
use Blackcube\Form\Field\AbstractButton;
use InvalidArgumentException;

/**
 * The buttons styled with the Bleet colors.
 *
 * The mechanics live in blackcube/yii-form; what follows is only the styling:
 * the shade, the size, the classes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractBleetButton extends AbstractButton
{
    use BleetColorTrait;

    protected string $color = Bleet::COLOR_PRIMARY;
    protected string $size = Bleet::SIZE_MD;

    public function getColor(): string
    {
        return $this->color;
    }

    public function color(string $color): static
    {
        $new = clone $this;
        $new->color = $color;

        return $new;
    }

    public function size(string $size): static
    {
        $new = clone $this;
        $new->size = $size;

        return $new;
    }

    public function primary(): static
    {
        return $this->color(Bleet::COLOR_PRIMARY);
    }

    public function secondary(): static
    {
        return $this->color(Bleet::COLOR_SECONDARY);
    }

    public function success(): static
    {
        return $this->color(Bleet::COLOR_SUCCESS);
    }

    public function danger(): static
    {
        return $this->color(Bleet::COLOR_DANGER);
    }

    public function warning(): static
    {
        return $this->color(Bleet::COLOR_WARNING);
    }

    public function info(): static
    {
        return $this->color(Bleet::COLOR_INFO);
    }

    public function accent(): static
    {
        return $this->color(Bleet::COLOR_ACCENT);
    }

    public function xs(): static
    {
        return $this->size(Bleet::SIZE_XS);
    }

    public function sm(): static
    {
        return $this->size(Bleet::SIZE_SM);
    }

    public function md(): static
    {
        return $this->size(Bleet::SIZE_MD);
    }

    public function lg(): static
    {
        return $this->size(Bleet::SIZE_LG);
    }

    public function xl(): static
    {
        return $this->size(Bleet::SIZE_XL);
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $hasIconAndContent = $this->iconName !== null && $this->text !== null;
        $flexClasses = [];
        if ($hasIconAndContent === true) {
            $flexClasses = [
                'inline-flex',
                'items-center',
                'gap-2',
            ];
        }

        if ($this->outline === true) {
            $baseClasses = [
                $this->roundedClass(),
                'cursor-pointer',
                'disabled:cursor-not-allowed',
                'disabled:opacity-50',
            ];
        } elseif ($this->ghost === true) {
            $baseClasses = [
                $this->roundedClass(),
                'font-semibold',
                'border',
                'cursor-pointer',
                'disabled:cursor-not-allowed',
                'disabled:opacity-50',
                'focus-visible:outline-2',
                'focus-visible:outline-offset-2',
            ];
        } elseif ($this->inverse === true) {
            $baseClasses = [
                $this->roundedClass(),
                'font-semibold',
                'shadow-xs',
                'cursor-pointer',
                'disabled:cursor-not-allowed',
                'disabled:opacity-50',
                'focus-visible:outline-2',
                'focus-visible:outline-offset-2',
            ];
        } else {
            $baseClasses = [
                $this->roundedClass(),
                'font-semibold',
                'text-white',
                'shadow-xs',
                'cursor-pointer',
                'disabled:cursor-not-allowed',
                'disabled:opacity-50',
                'focus-visible:outline-2',
                'focus-visible:outline-offset-2',
            ];
        }

        return [
            ...$flexClasses,
            ...$baseClasses,
            ...$this->getColorClasses(),
            ...$this->getSizeClasses(),
        ];
    }

    /**
     * @return string[]
     */
    protected function getColorClasses(): array
    {
        if ($this->outline === true) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:bg-primary-500', 'hover:text-white'],
                Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:bg-secondary-500', 'hover:text-white'],
                Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:bg-success-500', 'hover:text-white'],
                Bleet::COLOR_DANGER => ['text-danger-700', 'hover:bg-danger-500', 'hover:text-white'],
                Bleet::COLOR_WARNING => ['text-warning-700', 'hover:bg-warning-500', 'hover:text-white'],
                Bleet::COLOR_INFO => ['text-info-700', 'hover:bg-info-500', 'hover:text-white'],
                Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:bg-accent-500', 'hover:text-white'],
            };
        }

        if ($this->ghost === true) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['text-primary-700', 'border-primary-300', 'hover:bg-primary-600', 'hover:text-white', 'hover:border-primary-600', 'focus-visible:outline-primary-600'],
                Bleet::COLOR_SECONDARY => ['text-secondary-700', 'border-secondary-300', 'hover:bg-secondary-600', 'hover:text-white', 'hover:border-secondary-600', 'focus-visible:outline-secondary-600'],
                Bleet::COLOR_SUCCESS => ['text-success-700', 'border-success-300', 'hover:bg-success-600', 'hover:text-white', 'hover:border-success-600', 'focus-visible:outline-success-600'],
                Bleet::COLOR_DANGER => ['text-danger-700', 'border-danger-300', 'hover:bg-danger-600', 'hover:text-white', 'hover:border-danger-600', 'focus-visible:outline-danger-600'],
                Bleet::COLOR_WARNING => ['text-warning-700', 'border-warning-300', 'hover:bg-warning-600', 'hover:text-white', 'hover:border-warning-600', 'focus-visible:outline-warning-600'],
                Bleet::COLOR_INFO => ['text-info-700', 'border-info-300', 'hover:bg-info-600', 'hover:text-white', 'hover:border-info-600', 'focus-visible:outline-info-600'],
                Bleet::COLOR_ACCENT => ['text-accent-700', 'border-accent-300', 'hover:bg-accent-600', 'hover:text-white', 'hover:border-accent-600', 'focus-visible:outline-accent-600'],
            };
        }

        if ($this->inverse === true) {
            return match ($this->color) {
                Bleet::COLOR_PRIMARY => ['bg-white/90', 'text-primary-700', 'hover:bg-white', 'hover:text-primary-800', 'focus-visible:outline-primary-600'],
                Bleet::COLOR_SECONDARY => ['bg-white/90', 'text-secondary-700', 'hover:bg-white', 'hover:text-secondary-800', 'focus-visible:outline-secondary-600'],
                Bleet::COLOR_SUCCESS => ['bg-white/90', 'text-success-700', 'hover:bg-white', 'hover:text-success-800', 'focus-visible:outline-success-600'],
                Bleet::COLOR_DANGER => ['bg-white/90', 'text-danger-700', 'hover:bg-white', 'hover:text-danger-800', 'focus-visible:outline-danger-600'],
                Bleet::COLOR_WARNING => ['bg-white/90', 'text-warning-700', 'hover:bg-white', 'hover:text-warning-800', 'focus-visible:outline-warning-600'],
                Bleet::COLOR_INFO => ['bg-white/90', 'text-info-700', 'hover:bg-white', 'hover:text-info-800', 'focus-visible:outline-info-600'],
                Bleet::COLOR_ACCENT => ['bg-white/90', 'text-accent-700', 'hover:bg-white', 'hover:text-accent-800', 'focus-visible:outline-accent-600'],
            };
        }

        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600', 'hover:bg-primary-500', 'focus-visible:outline-primary-600'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-600', 'hover:bg-secondary-500', 'focus-visible:outline-secondary-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-600', 'hover:bg-success-500', 'focus-visible:outline-success-600'],
            Bleet::COLOR_DANGER => ['bg-danger-600', 'hover:bg-danger-500', 'focus-visible:outline-danger-600'],
            Bleet::COLOR_WARNING => ['bg-warning-600', 'hover:bg-warning-500', 'focus-visible:outline-warning-600'],
            Bleet::COLOR_INFO => ['bg-info-600', 'hover:bg-info-500', 'focus-visible:outline-info-600'],
            Bleet::COLOR_ACCENT => ['bg-accent-600', 'hover:bg-accent-500', 'focus-visible:outline-accent-600'],
        };
    }

    protected function getSizeClasses(): array
    {
        if ($this->outline === true) {
            return match ($this->size) {
                Bleet::SIZE_XS => ['p-1'],
                Bleet::SIZE_SM => ['p-1.5'],
                Bleet::SIZE_MD => ['p-2'],
                Bleet::SIZE_LG => ['p-2.5'],
                Bleet::SIZE_XL => ['p-3'],
            };
        }

        return match ($this->size) {
            Bleet::SIZE_XS => ['px-2', 'py-1', 'text-xs'],
            Bleet::SIZE_SM => ['px-2', 'py-1', 'text-sm'],
            Bleet::SIZE_MD => ['px-2.5', 'py-1.5', 'text-sm'],
            Bleet::SIZE_LG => ['px-3', 'py-2', 'text-sm'],
            Bleet::SIZE_XL => ['px-3.5', 'py-2.5', 'text-sm'],
        };
    }

    /**
     * @return string[]
     */
    /**
     * Rounding by size: the two smallest ones are less rounded.
     */
    protected function roundedClass(): string
    {
        return match ($this->size) {
            Bleet::SIZE_XS, Bleet::SIZE_SM => 'rounded-sm',
            default => 'rounded-md',
        };
    }

    /**
     * @return string[]
     */
    protected function iconClasses(): array
    {
        return [$this->outline === true ? 'size-4' : 'size-6'];
    }

    /**
     * @return string[]
     */
    protected function badgeClasses(): array
    {
        return [
            'absolute',
            '-top-1',
            '-right-1',
            'size-4',
            'text-xs',
            'font-bold',
            'text-white',
            'bg-danger-600',
            'rounded-full',
            'flex',
            'items-center',
            'justify-center',
        ];
    }

    /**
     * The button icon, set before the text.
     */
    protected function renderBeforeText(): string
    {
        $html = '';

        if ($this->iconName !== null) {
            if ($this->iconType === 'solid') {
                $svg = Svg::heroicon()->solid($this->iconName);
            } else {
                $svg = Svg::heroicon()->outline($this->iconName);
            }

            $html = $svg->addClass(...$this->iconClasses())->render();
        }

        return $html;
    }

    /**
     * The button badge, set after the text.
     */
    protected function renderAfterText(): string
    {
        $html = '';

        if ($this->badge !== null) {
            $html = Html::span((string) $this->badge)
                ->addClass(...$this->badgeClasses())
                ->render();
        }

        return $html;
    }
}
