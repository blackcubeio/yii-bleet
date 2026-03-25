<?php

declare(strict_types=1);

/**
 * BleetColorTrait.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

use Blackcube\Bleet\Bleet;

/**
 * Trait for normalized color CSS classes
 *
 * Shades :
 * - Texte principal : 700 (hover 800)
 * - Texte muted : 500 (hover 600)
 * - Bordure : 300 (hover 400)
 * - Focus : ring-2 + ring-600
 * - Light bg: 50 (hover 100)
 * - Bg actif : 600 (hover 700)
 */
trait BleetColorTrait
{
    /**
     * Returns effective color, switching to danger if model has errors.
     */
    protected function getEffectiveColor(): string
    {
        if (method_exists($this, 'hasErrors') && $this->hasErrors()) {
            return Bleet::COLOR_DANGER;
        }
        return $this->color;
    }

    // ========== TEXT ==========

    protected function textColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'text-primary-700',
            Bleet::COLOR_SECONDARY => 'text-secondary-700',
            Bleet::COLOR_SUCCESS => 'text-success-700',
            Bleet::COLOR_DANGER => 'text-danger-700',
            Bleet::COLOR_WARNING => 'text-warning-700',
            Bleet::COLOR_INFO => 'text-info-700',
            Bleet::COLOR_ACCENT => 'text-accent-700',
        };
    }

    protected function textHoverColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'hover:text-primary-800',
            Bleet::COLOR_SECONDARY => 'hover:text-secondary-800',
            Bleet::COLOR_SUCCESS => 'hover:text-success-800',
            Bleet::COLOR_DANGER => 'hover:text-danger-800',
            Bleet::COLOR_WARNING => 'hover:text-warning-800',
            Bleet::COLOR_INFO => 'hover:text-info-800',
            Bleet::COLOR_ACCENT => 'hover:text-accent-800',
        };
    }

    protected function textMutedColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'text-primary-500',
            Bleet::COLOR_SECONDARY => 'text-secondary-500',
            Bleet::COLOR_SUCCESS => 'text-success-500',
            Bleet::COLOR_DANGER => 'text-danger-500',
            Bleet::COLOR_WARNING => 'text-warning-500',
            Bleet::COLOR_INFO => 'text-info-500',
            Bleet::COLOR_ACCENT => 'text-accent-500',
        };
    }

    protected function textMutedHoverColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'hover:text-primary-600',
            Bleet::COLOR_SECONDARY => 'hover:text-secondary-600',
            Bleet::COLOR_SUCCESS => 'hover:text-success-600',
            Bleet::COLOR_DANGER => 'hover:text-danger-600',
            Bleet::COLOR_WARNING => 'hover:text-warning-600',
            Bleet::COLOR_INFO => 'hover:text-info-600',
            Bleet::COLOR_ACCENT => 'hover:text-accent-600',
        };
    }

    // ========== BORDER ==========

    protected function borderColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'border-primary-300',
            Bleet::COLOR_SECONDARY => 'border-secondary-300',
            Bleet::COLOR_SUCCESS => 'border-success-300',
            Bleet::COLOR_DANGER => 'border-danger-300',
            Bleet::COLOR_WARNING => 'border-warning-300',
            Bleet::COLOR_INFO => 'border-info-300',
            Bleet::COLOR_ACCENT => 'border-accent-300',
        };
    }

    protected function borderHoverColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'hover:border-primary-400',
            Bleet::COLOR_SECONDARY => 'hover:border-secondary-400',
            Bleet::COLOR_SUCCESS => 'hover:border-success-400',
            Bleet::COLOR_DANGER => 'hover:border-danger-400',
            Bleet::COLOR_WARNING => 'hover:border-warning-400',
            Bleet::COLOR_INFO => 'hover:border-info-400',
            Bleet::COLOR_ACCENT => 'hover:border-accent-400',
        };
    }

    // ========== FOCUS (RING) ==========

    /**
     * @return string[]
     */
    protected function focusRingClasses(): array
    {
        return [
            'focus:ring-2',
            match ($this->color) {
                Bleet::COLOR_PRIMARY => 'focus:ring-primary-600',
                Bleet::COLOR_SECONDARY => 'focus:ring-secondary-600',
                Bleet::COLOR_SUCCESS => 'focus:ring-success-600',
                Bleet::COLOR_DANGER => 'focus:ring-danger-600',
                Bleet::COLOR_WARNING => 'focus:ring-warning-600',
                Bleet::COLOR_INFO => 'focus:ring-info-600',
                Bleet::COLOR_ACCENT => 'focus:ring-accent-600',
            },
        ];
    }

    /**
     * @return string[]
     */
    protected function focusWithinRingClasses(): array
    {
        return [
            'focus-within:ring-2',
            match ($this->color) {
                Bleet::COLOR_PRIMARY => 'focus-within:ring-primary-600',
                Bleet::COLOR_SECONDARY => 'focus-within:ring-secondary-600',
                Bleet::COLOR_SUCCESS => 'focus-within:ring-success-600',
                Bleet::COLOR_DANGER => 'focus-within:ring-danger-600',
                Bleet::COLOR_WARNING => 'focus-within:ring-warning-600',
                Bleet::COLOR_INFO => 'focus-within:ring-info-600',
                Bleet::COLOR_ACCENT => 'focus-within:ring-accent-600',
            },
        ];
    }

    /**
     * @return string[]
     */
    protected function focusVisibleRingClasses(): array
    {
        return [
            'focus-visible:ring-2',
            match ($this->color) {
                Bleet::COLOR_PRIMARY => 'focus-visible:ring-primary-600',
                Bleet::COLOR_SECONDARY => 'focus-visible:ring-secondary-600',
                Bleet::COLOR_SUCCESS => 'focus-visible:ring-success-600',
                Bleet::COLOR_DANGER => 'focus-visible:ring-danger-600',
                Bleet::COLOR_WARNING => 'focus-visible:ring-warning-600',
                Bleet::COLOR_INFO => 'focus-visible:ring-info-600',
                Bleet::COLOR_ACCENT => 'focus-visible:ring-accent-600',
            },
        ];
    }

    // ========== BACKGROUND ==========

    protected function bgLightColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'bg-primary-50',
            Bleet::COLOR_SECONDARY => 'bg-secondary-50',
            Bleet::COLOR_SUCCESS => 'bg-success-50',
            Bleet::COLOR_DANGER => 'bg-danger-50',
            Bleet::COLOR_WARNING => 'bg-warning-50',
            Bleet::COLOR_INFO => 'bg-info-50',
            Bleet::COLOR_ACCENT => 'bg-accent-50',
        };
    }

    protected function bgLightHoverColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'hover:bg-primary-100',
            Bleet::COLOR_SECONDARY => 'hover:bg-secondary-100',
            Bleet::COLOR_SUCCESS => 'hover:bg-success-100',
            Bleet::COLOR_DANGER => 'hover:bg-danger-100',
            Bleet::COLOR_WARNING => 'hover:bg-warning-100',
            Bleet::COLOR_INFO => 'hover:bg-info-100',
            Bleet::COLOR_ACCENT => 'hover:bg-accent-100',
        };
    }

    protected function bgColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'bg-primary-600',
            Bleet::COLOR_SECONDARY => 'bg-secondary-600',
            Bleet::COLOR_SUCCESS => 'bg-success-600',
            Bleet::COLOR_DANGER => 'bg-danger-600',
            Bleet::COLOR_WARNING => 'bg-warning-600',
            Bleet::COLOR_INFO => 'bg-info-600',
            Bleet::COLOR_ACCENT => 'bg-accent-600',
        };
    }

    protected function bgHoverColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'hover:bg-primary-700',
            Bleet::COLOR_SECONDARY => 'hover:bg-secondary-700',
            Bleet::COLOR_SUCCESS => 'hover:bg-success-700',
            Bleet::COLOR_DANGER => 'hover:bg-danger-700',
            Bleet::COLOR_WARNING => 'hover:bg-warning-700',
            Bleet::COLOR_INFO => 'hover:bg-info-700',
            Bleet::COLOR_ACCENT => 'hover:bg-accent-700',
        };
    }

    // ========== COMBINED (common helpers) ==========

    /**
     * Classes for un input standard
     * @return string[]
     */
    protected function inputColorClasses(): array
    {
        return [
            $this->textColorClass(),
            $this->borderColorClass(),
            $this->placeholderColorClass(),
            ...$this->focusRingClasses(),
        ];
    }

    protected function placeholderColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'placeholder:text-primary-500',
            Bleet::COLOR_SECONDARY => 'placeholder:text-secondary-500',
            Bleet::COLOR_SUCCESS => 'placeholder:text-success-500',
            Bleet::COLOR_DANGER => 'placeholder:text-danger-500',
            Bleet::COLOR_WARNING => 'placeholder:text-warning-500',
            Bleet::COLOR_INFO => 'placeholder:text-info-500',
            Bleet::COLOR_ACCENT => 'placeholder:text-accent-500',
        };
    }

    /**
     * Classes for un bouton standard
     * @return string[]
     */
    protected function buttonColorClasses(): array
    {
        return [
            'text-white',
            $this->bgColorClass(),
            $this->bgHoverColorClass(),
            ...$this->focusVisibleRingClasses(),
        ];
    }

    /**
     * Classes for un lien
     * @return string[]
     */
    protected function linkColorClasses(): array
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => [
                'text-primary-700',
                'hover:text-primary-800',
            ],
            Bleet::COLOR_SECONDARY => [
                'text-secondary-700',
                'hover:text-secondary-800',
            ],
            Bleet::COLOR_SUCCESS => [
                'text-success-700',
                'hover:text-success-800',
            ],
            Bleet::COLOR_DANGER => [
                'text-danger-700',
                'hover:text-danger-800',
            ],
            Bleet::COLOR_WARNING => [
                'text-warning-700',
                'hover:text-warning-800',
            ],
            Bleet::COLOR_INFO => [
                'text-info-700',
                'hover:text-info-800',
            ],
            Bleet::COLOR_ACCENT => [
                'text-accent-700',
                'hover:text-accent-800',
            ],
        };
    }

    // ========== ICON CONTAINER ==========

    /**
     * Background pour conteneur d'icone (shade 100)
     */
    protected function bgIconContainerColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'bg-primary-100',
            Bleet::COLOR_SECONDARY => 'bg-secondary-100',
            Bleet::COLOR_SUCCESS => 'bg-success-100',
            Bleet::COLOR_DANGER => 'bg-danger-100',
            Bleet::COLOR_WARNING => 'bg-warning-100',
            Bleet::COLOR_INFO => 'bg-info-100',
            Bleet::COLOR_ACCENT => 'bg-accent-100',
        };
    }

    /**
     * Couleur texte pour icone (shade 600)
     */
    protected function textIconColorClass(): string
    {
        return match ($this->getEffectiveColor()) {
            Bleet::COLOR_PRIMARY => 'text-primary-600',
            Bleet::COLOR_SECONDARY => 'text-secondary-600',
            Bleet::COLOR_SUCCESS => 'text-success-600',
            Bleet::COLOR_DANGER => 'text-danger-600',
            Bleet::COLOR_WARNING => 'text-warning-600',
            Bleet::COLOR_INFO => 'text-info-600',
            Bleet::COLOR_ACCENT => 'text-accent-600',
        };
    }
}
