<?php

declare(strict_types=1);

/**
 * Select.php
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
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldDataTrait;
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Blackcube\Bleet\Traits\BleetWrapperAttributesTrait;

/**
 * Select widget - Select simple avec affichage custom
 *
 * Usage:
 *   Bleet::select()
 *       ->name('assignee')
 *       ->label('Assign to')
 *       ->placeholder('-- Select --')
 *       ->options([
 *           'wade' => 'Wade Cooper',
 *           'arlene' => 'Arlene Mccoy',
 *       ])
 *       ->selected('wade')
 *       ->primary()
 *       ->render();
 *   Bleet::select()->active($model, 'status')->options([...])->render() // model-bound
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Select extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetColorTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;
    use BleetWrapperAttributesTrait;
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private string $name = '';
    private string $label = '';
    private string $placeholder = '';
    /** @var array<string, string|array<string, string>> */
    private array $options = [];
    /** @var string|array<string>|null */
    protected string|array|null $selected = null;
    private bool $disabled = false;
    private ?string $labelledBy = null;
    private ?string $describedBy = null;
    private bool $withLabel = false;
    // Advanced rendering (dropdown view) — triggered when any flag below is true
    private bool $searchable = false;
    private bool $multiple = false;
    private bool $withTags = false;
    private string $searchPlaceholder = 'Rechercher...';
    private string $emptyText = 'No results';

    /**
     * Sets le nom du champ
     */
    public function name(string $name): static
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Sets the label or controls its visibility.
     * @param string|bool $label String for custom label, true to show (default), false to hide
     */
    public function label(string|bool $label): static
    {
        $new = clone $this;
        if (is_bool($label)) {
            $new->withLabel = $label;
        } else {
            $new->label = $label;
        }
        return $new;
    }

    /**
     * Sets le placeholder (option vide)
     */
    public function placeholder(string $placeholder): static
    {
        $new = clone $this;
        $new->placeholder = $placeholder;
        return $new;
    }

    /**
     * Sets les options
     * @param array<string, string|array<string, string>> $options Key => Label or Group => [Key => Label] for optgroups
     */
    public function options(array $options): static
    {
        $new = clone $this;
        $new->options = $options;
        return $new;
    }

    /**
     * Sets the selected value(s). Accepts array when multiple() is enabled.
     * @param string|array<string>|null $value
     */
    public function selected(string|array|null $value): static
    {
        $new = clone $this;
        $new->selected = $value;
        return $new;
    }

    /**
     * Marks the field as disabled
     */
    public function disabled(bool $disabled = true): static
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    /**
     * Sets the id of the label externe (aria-labelledby)
     */
    public function labelledBy(string $id): static
    {
        $new = clone $this;
        $new->labelledBy = $id;
        return $new;
    }

    /**
     * Sets the id de the description externe (aria-describedby)
     */
    public function describedBy(string $id): static
    {
        $new = clone $this;
        $new->describedBy = $id;
        return $new;
    }

    /**
     * Enables the search field (switches to advanced rendering).
     */
    public function searchable(bool $searchable = true): static
    {
        $new = clone $this;
        $new->searchable = $searchable;
        return $new;
    }

    /**
     * Enables multiple selection (switches to advanced rendering).
     */
    public function multiple(bool $multiple = true): static
    {
        $new = clone $this;
        $new->multiple = $multiple;
        return $new;
    }

    /**
     * Enables tag display — requires multiple (switches to advanced rendering).
     */
    public function withTags(bool $withTags = true): static
    {
        $new = clone $this;
        $new->withTags = $withTags;
        return $new;
    }

    /**
     * Sets the search field placeholder (advanced rendering only).
     */
    public function searchPlaceholder(string $placeholder): static
    {
        $new = clone $this;
        $new->searchPlaceholder = $placeholder;
        return $new;
    }

    /**
     * Sets the text displayed when no results match the search (advanced rendering only).
     */
    public function emptyText(string $text): static
    {
        $new = clone $this;
        $new->emptyText = $text;
        return $new;
    }

    public function render(): string
    {
        $view = $this->isAdvanced() ? 'dropdown' : 'select';
        return $this->renderView($view, $this->prepareViewParams());
    }

    /**
     * Advanced rendering (dropdown view) is used as soon as any of
     * searchable / multiple / withTags is enabled.
     */
    private function isAdvanced(): bool
    {
        return $this->searchable || $this->multiple || $this->withTags;
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    protected function prepareViewParams(): array
    {
        // Value resolution : explicit > model > empty
        $name = $this->name !== '' ? $this->name : ($this->getInputName() ?? '');
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $this->getInputId();
        $labelContent = $this->label !== '' ? $this->label : ($this->getLabel() ?? '');
        $selected = $this->selected ?? $this->getValue();

        // Create label widget with active() if model-bound
        $labelHtml = '';
        if ($this->withLabel && $labelContent !== '' && $labelContent !== null) {
            $labelWidget = Bleet::label($labelContent)->color($this->color);
            if ($this->hasModel()) {
                $labelWidget = $labelWidget->active($this->getModel(), $this->getProperty());
            }
            $labelHtml = $labelWidget->render();
        }

        $advanced = $this->isAdvanced();

        $containerAttributes = $this->prepareWrapperAttributes();
        $containerAttributes[$advanced ? 'bleet-dropdown' : 'bleet-select'] = '';
        \Yiisoft\Html\Html::addCssClass($containerAttributes, ['relative']);

        // Advanced mode : append [] to name when multiple, normalize selected to array
        if ($advanced) {
            if ($this->multiple) {
                $name = $name . '[]';
            }
            $selectedArray = [];
            if ($selected !== null) {
                $selectedArray = is_array($selected) ? $selected : [$selected];
            }
            $selected = $selectedArray;
        }

        $params = [
            'name' => $name,
            'id' => $id,
            'label' => $labelHtml,
            'placeholder' => $this->placeholder,
            'options' => $this->options,
            'selected' => $selected,
            'disabled' => $this->disabled,
            'labelledBy' => $this->labelledBy,
            'describedBy' => $this->describedBy,
            'containerAttributes' => $containerAttributes,
            'fieldData' => $this->getFieldDataAttributes(),
            'buttonClasses' => [
                'grid', 'w-full', 'cursor-pointer', 'grid-cols-1', 'rounded-md', 'bg-white',
                'py-1.5', 'pr-2', 'pl-3', 'text-left', 'text-base', 'sm:text-sm/6',
                'outline-1', '-outline-offset-1', 'focus:outline-2', 'focus:-outline-offset-2',
                ...$this->inputColorClasses(),
            ],
            'panelClasses' => [
                'absolute', 'z-10', 'mt-1', 'max-h-60', 'w-full', 'overflow-auto',
                'rounded-md', 'bg-white', 'py-1', 'text-base', 'shadow-lg', 'hidden',
                ...$this->getPanelColorClasses(),
            ],
            'itemBaseClasses' => [
                'relative', 'w-full', 'cursor-pointer', 'py-2', 'pr-4', 'pl-8',
                'text-left', 'focus:outline-none',
            ],
            'itemInactiveClasses' => $this->getItemColorClasses(),
            'itemActiveClasses' => $this->getSelectedItemColorClasses(),
            'groupHeaderClasses' => [
                'relative', 'w-full', 'cursor-default', 'py-2', 'pr-4', 'pl-8',
                'text-left', 'font-bold', 'text-secondary-500', 'bg-secondary-50',
            ],
            'chevronClasses' => ['col-start-1', 'row-start-1', 'size-5', 'self-center', 'justify-self-end', ...$this->getChevronColorClasses()],
            'checkBaseClasses' => ['absolute', 'inset-y-0', 'left-0', 'flex', 'items-center', 'pl-1.5'],
            'checkInactiveClasses' => ['hidden', ...$this->getCheckColorClasses()],
            'checkActiveClasses' => ['text-white'],
        ];

        if ($advanced) {
            $params['searchPlaceholder'] = $this->searchPlaceholder;
            $params['emptyText'] = $this->emptyText;
            $params['searchable'] = $this->searchable;
            $params['multiple'] = $this->multiple;
            $params['withTags'] = $this->withTags;
            $params['searchClasses'] = $this->getSearchClasses();
            $params['tagClasses'] = $this->getTagClasses();
            $params['tagRemoveButtonClasses'] = $this->getTagRemoveButtonClasses();
            $params['tagRemoveSvgClasses'] = $this->getTagRemoveSvgClasses();
        }

        return $params;
    }

    /**
     * @return string[]
     */
    private function getPanelColorClasses(): array
    {
        $baseClasses = ['border'];
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
    private function getItemColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'hover:bg-primary-100', 'hover:text-primary-800', 'focus:bg-primary-100', 'focus:text-primary-800'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'hover:bg-secondary-100', 'hover:text-secondary-800', 'focus:bg-secondary-100', 'focus:text-secondary-800'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'hover:bg-success-100', 'hover:text-success-800', 'focus:bg-success-100', 'focus:text-success-800'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'hover:bg-danger-100', 'hover:text-danger-800', 'focus:bg-danger-100', 'focus:text-danger-800'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'hover:bg-warning-100', 'hover:text-warning-800', 'focus:bg-warning-100', 'focus:text-warning-800'],
            Bleet::COLOR_INFO => ['text-info-700', 'hover:bg-info-100', 'hover:text-info-800', 'focus:bg-info-100', 'focus:text-info-800'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'hover:bg-accent-100', 'hover:text-accent-800', 'focus:bg-accent-100', 'focus:text-accent-800'],
        };
    }

    /**
     * @return string[]
     */
    private function getSelectedItemColorClasses(): array
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
