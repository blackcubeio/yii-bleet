<?php

declare(strict_types=1);

/**
 * Dropdown.php
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
use Blackcube\Bleet\Traits\BleetFieldDataTrait;
use Yiisoft\Html\Html;

/**
 * Dropdown widget - Advanced dropdown with search, multiple selection and tags
 *
 * Usage:
 *   // Simple
 *   Bleet::dropdown()
 *       ->name('assignee')
 *       ->label('Assign to')
 *       ->options(['wade' => 'Wade Cooper', 'tom' => 'Tom Cook'])
 *       ->primary()
 *
 *   // Avec recherche
 *   Bleet::dropdown()
 *       ->name('contact')
 *       ->label('Contact')
 *       ->searchable()
 *       ->options([...])
 *
 *   // Multiple
 *   Bleet::dropdown()
 *       ->name('categories')
 *       ->label('Categories')
 *       ->multiple()
 *       ->selected(['tech', 'design'])
 *       ->options([...])
 *
 *   // Multiple avec tags (style Gmail)
 *   Bleet::dropdown()
 *       ->name('tags')
 *       ->label('Tags')
 *       ->multiple()
 *       ->withTags()
 *       ->searchable()
 *       ->options([...])
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Dropdown extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetFieldDataTrait;
    use RenderViewTrait;
    use \Blackcube\Bleet\Traits\BleetModelAwareTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private string $name = '';
    private string $label = '';
    private string $placeholder = '';
    private string $searchPlaceholder = 'Rechercher...';
    private string $emptyText = 'No results';
    /** @var array<string, string|array<string, string>> */
    private array $options = [];
    /** @var string|array<string>|null */
    private string|array|null $selected = null;
    private ?string $labelledBy = null;
    private ?string $describedBy = null;
    private bool $searchable = false;
    private bool $multiple = false;
    private bool $withTags = false;

    /**
     * Sets le nom du champ
     */
    public function name(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Sets the label
     */
    public function label(string $label): self
    {
        $new = clone $this;
        $new->label = $label;
        return $new;
    }

    /**
     * Sets le placeholder
     */
    public function placeholder(string $placeholder): self
    {
        $new = clone $this;
        $new->placeholder = $placeholder;
        return $new;
    }

    /**
     * Sets le placeholder du champ de recherche
     */
    public function searchPlaceholder(string $placeholder): self
    {
        $new = clone $this;
        $new->searchPlaceholder = $placeholder;
        return $new;
    }

    /**
     * Sets the text when no results
     */
    public function emptyText(string $text): self
    {
        $new = clone $this;
        $new->emptyText = $text;
        return $new;
    }

    /**
     * Sets les options
     * @param array<string, string|array<string, string>> $options Key => Label or Group => [Key => Label] for optgroups
     */
    public function options(array $options): self
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    /**
     * Sets the selected value(s)
     * @param string|array<string>|null $value
     */
    public function selected(string|array|null $value): self
    {
        $new = clone $this;
        $new->selected = $value;
        return $new;
    }

    /**
     * Enables la recherche
     */
    public function searchable(bool $searchable = true): self
    {
        $new = clone $this;
        $new->searchable = $searchable;
        return $new;
    }

    /**
     * Enables multiple selection
     */
    public function multiple(bool $multiple = true): self
    {
        $new = clone $this;
        $new->multiple = $multiple;
        return $new;
    }

    /**
     * Enables tag display (requires multiple)
     */
    public function withTags(bool $withTags = true): self
    {
        $new = clone $this;
        $new->withTags = $withTags;
        return $new;
    }

    /**
     * Sets the id of the label externe (aria-labelledby)
     */
    public function labelledBy(string $id): self
    {
        $new = clone $this;
        $new->labelledBy = $id;
        return $new;
    }

    /**
     * Sets the id de the description externe (aria-describedby)
     */
    public function describedBy(string $id): self
    {
        $new = clone $this;
        $new->describedBy = $id;
        return $new;
    }

    public function render(): string
    {
        return $this->renderView('dropdown', $this->prepareViewParams());
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
        // Value resolution : explicit > model > empty
        $name = $this->name !== '' ? $this->name : ($this->getInputName() ?? '');
        $tagAttributes = $this->getTagAttributes();
        $id = $tagAttributes['id'] ?? $this->getInputId();
        $labelContent = $this->label !== '' ? $this->label : ($this->getLabel() ?? '');
        $selected = $this->selected ?? $this->getValue();

        // Create label widget with active() if model-bound
        $labelHtml = '';
        if ($labelContent !== '' && $labelContent !== null) {
            $labelWidget = Bleet::label($labelContent)->color($this->color);
            if ($this->hasModel()) {
                $labelWidget = $labelWidget->active($this->getModel(), $this->getProperty());
            }
            $labelHtml = $labelWidget->render();
        }

        $containerAttributes = $this->prepareTagAttributes();
        $containerAttributes['bleet-dropdown'] = '';
        Html::addCssClass($containerAttributes, ['max-w-md', 'relative']);

        $selectName = $this->multiple ? $name . '[]' : $name;

        // Normalize selected to array for multiple
        $selectedArray = [];
        if ($selected !== null) {
            $selectedArray = is_array($selected) ? $selected : [$selected];
        }

        return [
            'name' => $selectName,
            'id' => $id,
            'label' => $labelHtml,
            'placeholder' => $this->placeholder,
            'searchPlaceholder' => $this->searchPlaceholder,
            'emptyText' => $this->emptyText,
            'options' => $this->options,
            'selected' => $selectedArray,
            'labelledBy' => $this->labelledBy,
            'describedBy' => $this->describedBy,
            'searchable' => $this->searchable,
            'multiple' => $this->multiple,
            'withTags' => $this->withTags,
            'containerAttributes' => $containerAttributes,
            'fieldData' => $this->getFieldDataAttributes(),
            'buttonClasses' => [
                'grid', 'w-full', 'cursor-pointer', 'grid-cols-1', 'rounded-md', 'bg-white',
                'py-2', 'pr-2', 'pl-3', 'text-left', 'sm:text-sm', 'border',
                'focus:ring-2', 'focus:ring-offset-2',
                ...$this->getButtonColorClasses(),
            ],
            'panelClasses' => $this->getPanelClasses(),
            'searchClasses' => $this->getSearchClasses(),
            'itemBaseClasses' => [
                'relative', 'w-full', 'cursor-pointer', 'py-2', 'pr-4', 'pl-8',
                'text-left', 'focus:outline-none',
            ],
            'itemInactiveClasses' => $this->getItemInactiveClasses(),
            'itemActiveClasses' => $this->getItemActiveClasses(),
            'chevronClasses' => ['col-start-1', 'row-start-1', 'size-5', 'self-center', 'justify-self-end', ...$this->getChevronColorClasses()],
            'checkBaseClasses' => ['absolute', 'inset-y-0', 'left-0', 'flex', 'items-center', 'pl-1.5'],
            'checkInactiveClasses' => ['hidden', ...$this->getCheckColorClasses()],
            'checkActiveClasses' => ['text-white'],
            'tagClasses' => $this->getTagClasses(),
            'tagRemoveButtonClasses' => $this->getTagRemoveButtonClasses(),
            'tagRemoveSvgClasses' => $this->getTagRemoveSvgClasses(),
        ];
    }

    /**
     * @return string[]
     */
    private function getButtonColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'border-primary-300', 'focus:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'border-secondary-300', 'focus:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'border-success-300', 'focus:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'border-danger-300', 'focus:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'border-warning-300', 'focus:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-700', 'border-info-300', 'focus:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'border-accent-300', 'focus:ring-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getPanelClasses(): array
    {
        $baseClasses = [
            'absolute', 'z-10', 'mt-1', 'max-h-60', 'w-full', 'overflow-auto',
            'rounded-md', 'bg-white', 'py-1', 'text-base', 'shadow-lg', 'hidden', 'border',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-300'],
            Bleet::COLOR_SECONDARY => ['border-secondary-300'],
            Bleet::COLOR_SUCCESS => ['border-success-300'],
            Bleet::COLOR_DANGER => ['border-danger-300'],
            Bleet::COLOR_WARNING => ['border-warning-300'],
            Bleet::COLOR_INFO => ['border-info-300'],
            Bleet::COLOR_ACCENT => ['border-accent-300'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getSearchClasses(): array
    {
        $baseClasses = [
            'block', 'w-full', 'rounded-md', 'py-1.5', 'px-3', 'text-sm',
            'placeholder:text-secondary-400', 'focus:ring-2',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-50', 'text-primary-700', 'focus:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50', 'text-secondary-700', 'focus:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-50', 'text-success-700', 'focus:ring-success-600'],
            Bleet::COLOR_DANGER => ['bg-danger-50', 'text-danger-700', 'focus:ring-danger-600'],
            Bleet::COLOR_WARNING => ['bg-warning-50', 'text-warning-700', 'focus:ring-warning-600'],
            Bleet::COLOR_INFO => ['bg-info-50', 'text-info-700', 'focus:ring-info-600'],
            Bleet::COLOR_ACCENT => ['bg-accent-50', 'text-accent-700', 'focus:ring-accent-600'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getItemInactiveClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:bg-primary-100', 'hover:text-primary-600', 'focus:bg-primary-100', 'focus:text-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:bg-secondary-100', 'hover:text-secondary-600', 'focus:bg-secondary-100', 'focus:text-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:bg-success-100', 'hover:text-success-600', 'focus:bg-success-100', 'focus:text-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'hover:bg-danger-100', 'hover:text-danger-600', 'focus:bg-danger-100', 'focus:text-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'hover:bg-warning-100', 'hover:text-warning-600', 'focus:bg-warning-100', 'focus:text-warning-600'],
            Bleet::COLOR_INFO => ['text-info-700', 'hover:bg-info-100', 'hover:text-info-600', 'focus:bg-info-100', 'focus:text-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:bg-accent-100', 'hover:text-accent-600', 'focus:bg-accent-100', 'focus:text-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getItemActiveClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-white', 'bg-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-white', 'bg-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-white', 'bg-success-600'],
            Bleet::COLOR_DANGER => ['text-white', 'bg-danger-600'],
            Bleet::COLOR_WARNING => ['text-white', 'bg-warning-600'],
            Bleet::COLOR_INFO => ['text-white', 'bg-info-600'],
            Bleet::COLOR_ACCENT => ['text-white', 'bg-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getChevronColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-500'],
            Bleet::COLOR_SECONDARY => ['text-secondary-500'],
            Bleet::COLOR_SUCCESS => ['text-success-500'],
            Bleet::COLOR_DANGER => ['text-danger-500'],
            Bleet::COLOR_WARNING => ['text-warning-500'],
            Bleet::COLOR_INFO => ['text-info-500'],
            Bleet::COLOR_ACCENT => ['text-accent-500'],
        };
    }

    /**
     * @return string[]
     */
    private function getCheckColorClasses(): array
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
    private function getTagClasses(): array
    {
        // Aligned with Bleet::badge()->removable()
        $baseClasses = [
            'inline-flex', 'items-center', 'gap-x-0.5', 'rounded-md', 'px-2', 'py-1', 'text-xs', 'font-medium',
            'ring-1', 'ring-inset',
        ];

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
    private function getTagRemoveButtonClasses(): array
    {
        // Button with padding to enlarge focus area
        // -mt-1 -mr-1 -mb-1 to stick to top/right/bottom edges
        // pl-1 to balance with space on the right
        $baseClasses = [
            'group', 'relative', 'cursor-pointer', 'rounded-r-md',
            '-mt-1', '-mr-2', '-mb-1', 'pl-1', 'pr-2', 'pt-1', 'pb-1',
            'focus:outline-none', 'focus:ring-2', 'focus:ring-inset',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['hover:bg-primary-500/20', 'focus:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['hover:bg-secondary-500/20', 'focus:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['hover:bg-success-500/20', 'focus:ring-success-600'],
            Bleet::COLOR_DANGER => ['hover:bg-danger-500/20', 'focus:ring-danger-600'],
            Bleet::COLOR_WARNING => ['hover:bg-warning-500/20', 'focus:ring-warning-600'],
            Bleet::COLOR_INFO => ['hover:bg-info-500/20', 'focus:ring-info-600'],
            Bleet::COLOR_ACCENT => ['hover:bg-accent-500/20', 'focus:ring-accent-600'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getTagRemoveSvgClasses(): array
    {
        // Aligned with Badge::getRemoveSvgClasses()
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
