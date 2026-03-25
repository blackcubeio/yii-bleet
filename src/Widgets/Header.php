<?php

declare(strict_types=1);

/**
 * Header.php
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
 * Header widget - Sticky header with optional burger menu
 *
 * Displays a sticky header at the top of the page with:
 * - Optional burger menu button (for mobile navigation)
 * - Configurable page title
 * - Customizable color scheme
 *
 * Usage:
 *   Bleet::header()
 *       ->title('My Application')
 *       ->withBurgerMenu()
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Header extends AbstractWidget
{
    use BleetAttributesTrait;
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private string $title = 'Bleet';
    private bool $showBurgerMenu = true;
    private ?string $searchAction = null;
    private string $searchPlaceholder = 'Rechercher';
    /** @var array<Anchor|Button> */
    private array $actions = [];
    /** @var array<string|null> rendered HTML widgets (null = separator) */
    private array $widgets = [];
    private ?Profile $profile = null;

    /**
     * Sets the title displayed in the header
     */
    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    /**
     * Affiche the button burger menu
     */
    public function withBurgerMenu(): self
    {
        $new = clone $this;
        $new->showBurgerMenu = true;
        return $new;
    }

    /**
     * Masque the button burger menu
     */
    public function withoutBurgerMenu(): self
    {
        $new = clone $this;
        $new->showBurgerMenu = false;
        return $new;
    }

    /**
     * Adds un formulaire de recherche
     */
    public function search(?string $action = null, string $placeholder = 'Rechercher'): self
    {
        $new = clone $this;
        $new->searchAction = $action ?? '#';
        $new->searchPlaceholder = $placeholder;
        return $new;
    }

    /**
     * Sets les actions (boutons/liens) of the header
     * @param array<Anchor|Button> $actions
     */
    public function actions(array $actions): self
    {
        $new = clone $this;
        $new->actions = $actions;
        return $new;
    }

    /**
     * Adds une action
     */
    public function addAction(Anchor|Button $action): self
    {
        $new = clone $this;
        $new->actions[] = $action;
        return $new;
    }

    /**
     * Adds arbitrary rendered HTML in the header actions area
     */
    public function addWidget(string $html): self
    {
        $new = clone $this;
        $new->widgets[] = $html;
        return $new;
    }

    /**
     * Adds a visual separator in the widgets area
     */
    public function addSeparator(): self
    {
        $new = clone $this;
        $new->widgets[] = null;
        return $new;
    }

    /**
     * Sets le widget Profile
     */
    public function profile(Profile $profile): self
    {
        $new = clone $this;
        $new->profile = $profile;
        return $new;
    }

    public function render(): string
    {
        return $this->renderView('header', $this->prepareViewParams());
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
        // Container classes - different layout if search/actions/profile present
        $hasExtras = $this->searchAction !== null || !empty($this->actions) || !empty($this->widgets) || $this->profile !== null;

        $containerClasses = $hasExtras
            ? ['sticky', 'top-0', 'z-40', 'flex', 'h-16', 'shrink-0', 'items-center', 'gap-x-4', 'border-b', 'bg-white', 'px-4', 'shadow-xs', 'sm:gap-x-6', 'sm:px-6', 'lg:px-8', ...$this->getContainerBorderColorClasses()]
            : ['bg-white', 'border-b', 'sticky', 'top-0', 'z-40', ...$this->getContainerBorderColorClasses()];

        $containerAttributes = $this->prepareTagAttributes();
        Html::addCssClass($containerAttributes, $containerClasses);

        return [
            'title' => $this->title,
            'showBurgerMenu' => $this->showBurgerMenu,
            'hasExtras' => $hasExtras,
            'searchAction' => $this->searchAction,
            'searchPlaceholder' => $this->searchPlaceholder,
            'actions' => $this->actions,
            'widgets' => $this->widgets,
            'profile' => $this->profile,
            'containerAttributes' => $containerAttributes,
            'color' => $this->color,
            // Simple layout classes
            'innerClasses' => ['flex', 'items-center', 'justify-between', 'px-4', 'sm:px-6', 'lg:px-8', 'py-4'],
            'leftSectionClasses' => ['flex', 'items-center', 'gap-4'],
            // Burger
            'burgerButtonClasses' => $this->getBurgerButtonClasses(),
            'burgerButtonAttributes' => Bleet::burger(),
            'burgerIconClasses' => ['size-6', ...$this->getBurgerIconColorClasses()],
            // Title
            'titleClasses' => ['text-xl', 'font-semibold', ...$this->getTitleColorClasses()],
            // Separator
            'separatorClasses' => $this->getSeparatorClasses(),
            // Search
            'searchInputClasses' => $this->getSearchInputClasses(),
            'searchIconClasses' => $this->getSearchIconClasses(),
            // Actions
            'actionButtonClasses' => $this->getActionButtonClasses(),
        ];
    }

    /**
     * @return string[]
     */
    private function getContainerBorderColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-200'],
            Bleet::COLOR_SECONDARY => ['border-secondary-200'],
            Bleet::COLOR_SUCCESS => ['border-success-200'],
            Bleet::COLOR_DANGER => ['border-danger-200'],
            Bleet::COLOR_WARNING => ['border-warning-200'],
            Bleet::COLOR_INFO => ['border-info-200'],
            Bleet::COLOR_ACCENT => ['border-accent-200'],
        };
    }

    /**
     * @return string[]
     */
    private function getBurgerButtonClasses(): array
    {
        $baseClasses = ['lg:hidden', 'p-2', 'rounded-lg', 'cursor-pointer', 'focus:ring-2', 'focus:ring-offset-2'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['hover:bg-primary-50', 'focus:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['hover:bg-secondary-50', 'focus:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['hover:bg-success-50', 'focus:ring-success-600'],
            Bleet::COLOR_DANGER => ['hover:bg-danger-50', 'focus:ring-danger-600'],
            Bleet::COLOR_WARNING => ['hover:bg-warning-50', 'focus:ring-warning-600'],
            Bleet::COLOR_INFO => ['hover:bg-info-50', 'focus:ring-info-600'],
            Bleet::COLOR_ACCENT => ['hover:bg-accent-50', 'focus:ring-accent-600'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getBurgerIconColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };
    }

    /**
     * @return string[]
     */
    private function getTitleColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };
    }

    /**
     * @return string[]
     */
    private function getSeparatorClasses(): array
    {
        $baseClasses = ['h-6', 'w-px'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-200'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-200'],
            Bleet::COLOR_SUCCESS => ['bg-success-200'],
            Bleet::COLOR_DANGER => ['bg-danger-200'],
            Bleet::COLOR_WARNING => ['bg-warning-200'],
            Bleet::COLOR_INFO => ['bg-info-200'],
            Bleet::COLOR_ACCENT => ['bg-accent-200'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getSearchInputClasses(): array
    {
        $baseClasses = ['col-start-1', 'row-start-1', 'block', 'size-full', 'bg-white', 'pl-8', 'text-base', 'outline-hidden', 'sm:text-sm/6', 'focus-visible:ring-2', 'focus-visible:ring-offset-2'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900', 'placeholder:text-primary-500', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-900', 'placeholder:text-secondary-500', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-900', 'placeholder:text-success-500', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-900', 'placeholder:text-danger-500', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-900', 'placeholder:text-warning-500', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-900', 'placeholder:text-info-500', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-900', 'placeholder:text-accent-500', 'focus-visible:ring-accent-600'],
        };
        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getSearchIconClasses(): array
    {
        $baseClasses = ['pointer-events-none', 'col-start-1', 'row-start-1', 'size-5', 'self-center'];
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
    private function getActionButtonClasses(): array
    {
        $baseClasses = ['-m-2.5', 'p-2.5', 'focus-visible:ring-2', 'focus-visible:ring-offset-2'];
        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-500', 'hover:text-primary-600', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-500', 'hover:text-secondary-600', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-500', 'hover:text-success-600', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-500', 'hover:text-danger-600', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-500', 'hover:text-warning-600', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-500', 'hover:text-info-600', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-500', 'hover:text-accent-600', 'focus-visible:ring-accent-600'],
        };
        return [...$baseClasses, ...$colorClasses];
    }
}
