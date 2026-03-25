<?php

declare(strict_types=1);

/**
 * Profile.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;

/**
 * Profile widget - User profile dropdown for header
 *
 * Displays a user profile button with:
 * - Avatar (initials or image)
 * - Optional user name (desktop only)
 * - Dropdown menu with links/buttons
 *
 * Usage:
 *   Bleet::profile()
 *       ->avatar('JD')
 *       ->name('Jean Dupont')
 *       ->items([
 *           Bleet::a('/profile')->content('Votre profil'),
 *           Bleet::a('/settings')->content('Settings'),
 *           Bleet::button('Logout')->attribute('bleet-logout', ''),
 *       ])
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Profile extends AbstractWidget
{
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private ?string $initials = null;
    private ?Img $avatarImg = null;
    private ?string $name = null;
    /** @var array<Anchor|Button> */
    private array $items = [];
    private array $containerAttributes = [];

    /**
     * Sets l'avatar
     * - 2 chars or less → initials
     * - More than 2 chars → extracts initials (first letter of each word)
     * - Img → image
     */
    public function avatar(string|Img $avatar): self
    {
        $new = clone $this;

        if ($avatar instanceof Img) {
            $new->avatarImg = $avatar;
            $new->initials = null;
        } else {
            $new->avatarImg = null;
            if (strlen($avatar) <= 2) {
                $new->initials = strtoupper($avatar);
            } else {
                // Extract initials from name (e.g., "Jean Dupont" → "JD")
                $words = preg_split('/\s+/', trim($avatar));
                $initials = '';
                foreach ($words as $word) {
                    if ($word !== '') {
                        $initials .= mb_strtoupper(mb_substr($word, 0, 1));
                    }
                }
                $new->initials = mb_substr($initials, 0, 2);
            }
        }

        return $new;
    }

    /**
     * Sets the displayed name (desktop only)
     */
    public function name(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Sets les items du menu dropdown (Anchor ou Button)
     * @param array<Anchor|Button> $items
     */
    public function items(array $items): self
    {
        $new = clone $this;
        $new->items = $items;
        return $new;
    }

    /**
     * Adds un item au menu
     */
    public function addItem(Anchor|Button $item): self
    {
        $new = clone $this;
        $new->items[] = $item;
        return $new;
    }

    /**
     * Sets the id attribute
     */
    public function id(string $id): self
    {
        $new = clone $this;
        $new->containerAttributes['id'] = $id;
        return $new;
    }

    /**
     * Adds a CSS class
     */
    public function addClass(string ...$classes): self
    {
        $new = clone $this;
        $existing = $new->containerAttributes['class'] ?? '';
        $new->containerAttributes['class'] = trim($existing . ' ' . implode(' ', $classes));
        return $new;
    }

    public function render(): string
    {
        return $this->renderView('profile', $this->prepareViewParams());
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    private function prepareViewParams(): array
    {
        return [
            'initials' => $this->initials,
            'avatarImg' => $this->avatarImg,
            'name' => $this->name,
            'items' => $this->items,
            'containerAttributes' => $this->containerAttributes,
            'color' => $this->color,
            // Button classes
            'buttonClasses' => $this->getButtonClasses(),
            // Avatar classes
            'avatarInitialsClasses' => $this->getAvatarInitialsClasses(),
            'avatarImageClasses' => $this->getAvatarImageClasses(),
            'initialsTextClasses' => ['text-sm', 'font-medium', 'leading-none', 'text-white'],
            // Name classes
            'nameContainerClasses' => ['hidden', 'lg:flex', 'lg:items-center'],
            'nameClasses' => $this->getNameClasses(),
            'chevronClasses' => $this->getChevronClasses(),
            // Dropdown classes
            'dropdownClasses' => $this->getDropdownClasses(),
            // Item classes (for menu items)
            'itemClasses' => $this->getItemClasses(),
        ];
    }

    /**
     * @return string[]
     */
    private function getButtonClasses(): array
    {
        $baseClasses = ['relative', 'flex', 'items-center', 'cursor-pointer', 'focus-visible:ring-2', 'focus-visible:ring-offset-2'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['focus-visible:ring-accent-600'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getAvatarInitialsClasses(): array
    {
        $baseClasses = ['inline-flex', 'size-8', 'items-center', 'justify-center', 'rounded-full'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-600'],
            Bleet::COLOR_DANGER => ['bg-danger-600'],
            Bleet::COLOR_WARNING => ['bg-warning-600'],
            Bleet::COLOR_INFO => ['bg-info-600'],
            Bleet::COLOR_ACCENT => ['bg-accent-600'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getAvatarImageClasses(): array
    {
        $baseClasses = ['size-8', 'rounded-full'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-50'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50'],
            Bleet::COLOR_SUCCESS => ['bg-success-50'],
            Bleet::COLOR_DANGER => ['bg-danger-50'],
            Bleet::COLOR_WARNING => ['bg-warning-50'],
            Bleet::COLOR_INFO => ['bg-info-50'],
            Bleet::COLOR_ACCENT => ['bg-accent-50'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getNameClasses(): array
    {
        $baseClasses = ['ml-4', 'text-sm/6', 'font-semibold'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900'],
            Bleet::COLOR_SECONDARY => ['text-secondary-900'],
            Bleet::COLOR_SUCCESS => ['text-success-900'],
            Bleet::COLOR_DANGER => ['text-danger-900'],
            Bleet::COLOR_WARNING => ['text-warning-900'],
            Bleet::COLOR_INFO => ['text-info-900'],
            Bleet::COLOR_ACCENT => ['text-accent-900'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getChevronClasses(): array
    {
        $baseClasses = ['ml-2', 'size-5'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-500'],
            Bleet::COLOR_SECONDARY => ['text-secondary-500'],
            Bleet::COLOR_SUCCESS => ['text-success-500'],
            Bleet::COLOR_DANGER => ['text-danger-500'],
            Bleet::COLOR_WARNING => ['text-warning-500'],
            Bleet::COLOR_INFO => ['text-info-500'],
            Bleet::COLOR_ACCENT => ['text-accent-500'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getDropdownClasses(): array
    {
        // Closed state: hidden opacity-0 scale-95 pointer-events-none
        // Open state (JS): remove hidden + opacity-0 scale-95 pointer-events-none, add opacity-100 scale-100
        $baseClasses = [
            'hidden', 'absolute', 'right-0', 'z-10', 'mt-2.5', 'min-w-full', 'origin-top-right',
            'rounded-md', 'bg-white', 'py-2', 'shadow-lg', 'ring-1', 'focus:outline-hidden',
            'transition', 'transform', 'opacity-0', 'scale-95', 'pointer-events-none',
        ];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['ring-primary-900/5'],
            Bleet::COLOR_SECONDARY => ['ring-secondary-900/5'],
            Bleet::COLOR_SUCCESS => ['ring-success-900/5'],
            Bleet::COLOR_DANGER => ['ring-danger-900/5'],
            Bleet::COLOR_WARNING => ['ring-warning-900/5'],
            Bleet::COLOR_INFO => ['ring-info-900/5'],
            Bleet::COLOR_ACCENT => ['ring-accent-900/5'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getItemClasses(): array
    {
        $baseClasses = ['block', 'w-full', 'text-left', 'px-3', 'py-1', 'text-sm/6', 'cursor-pointer'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900', 'hover:bg-primary-50'],
            Bleet::COLOR_SECONDARY => ['text-secondary-900', 'hover:bg-secondary-50'],
            Bleet::COLOR_SUCCESS => ['text-success-900', 'hover:bg-success-50'],
            Bleet::COLOR_DANGER => ['text-danger-900', 'hover:bg-danger-50'],
            Bleet::COLOR_WARNING => ['text-warning-900', 'hover:bg-warning-50'],
            Bleet::COLOR_INFO => ['text-info-900', 'hover:bg-info-50'],
            Bleet::COLOR_ACCENT => ['text-accent-900', 'hover:bg-accent-50'],
        };
        return [...$baseClasses, ...$colorClasses];
    }
}
