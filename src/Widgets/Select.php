<?php

declare(strict_types=1);

/**
 * Select.php
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
use Blackcube\Bleet\Traits\BleetFieldTrait;
use Blackcube\Form\Field\Select as BaseSelect;

/**
 * The Bleet dropdown: the styled native select, and its drawn panel as soon
 * as a search, a multiple choice or
 * etiquettes.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Select extends BaseSelect
{
    use BleetColorTrait;
    use BleetFieldTrait;
    use RenderViewTrait;

    protected bool $searchable = false;
    protected bool $withTags = false;
    protected string $searchPlaceholder = 'Rechercher...';
    protected string $emptyText = 'No results';

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

    protected function generateInput(): string
    {
        $view = $this->isAdvanced() === true ? 'dropdown' : 'select';

        return $this->renderView($view, $this->prepareViewParams());
    }

    /**
     * Advanced rendering (dropdown view) is used as soon as any of
     * searchable / multiple / withTags is enabled.
     */
    protected function isAdvanced(): bool
    {
        return $this->searchable === true || $this->multiple === true || $this->withTags === true;
    }

    protected function prepareViewParams(): array
    {
        $inputData = $this->getInputData();
        $name = $inputData->getName() ?? '';
        $tagAttrs = $this->getTagAttributes();
        $id = $tagAttrs['id'] ?? $inputData->getId();

        $selected = $inputData->getValue();

        $advanced = $this->isAdvanced();

        $containerAttributes = $this->prepareWrapperAttributes();
        $containerAttributes[$advanced ? 'bleet-dropdown' : 'bleet-select'] = '';
        \Yiisoft\Html\Html::addCssClass($containerAttributes, ['relative']);

        if ($advanced) {
            if ($this->multiple === true) {
                $name = $name.'[]';
            }
            $selectedArray = [];
            if ($selected !== null) {
                $selectedArray = is_array($selected) === true ? $selected : [$selected];
            }
            $selected = $selectedArray;
        }

        $params = [
            'name' => $name,
            'id' => $id,
            'prompt' => $this->prompt,
            'options' => $this->options,
            'selected' => $selected,
            'disabled' => $this->disabled,
            'labelledBy' => $this->labelledBy,
            'describedBy' => $this->describedBy,
            'containerAttributes' => $containerAttributes,
            'fieldData' => $this->getFieldDataAttributes(),
            'buttonClasses' => $this->buttonClasses(),
            'panelClasses' => $this->panelClasses(),
            'itemBaseClasses' => $this->itemBaseClasses(),
            'itemInactiveClasses' => $this->getItemColorClasses(),
            'itemActiveClasses' => $this->getSelectedItemColorClasses(),
            'groupHeaderClasses' => $this->groupHeaderClasses(),
            'chevronClasses' => $this->chevronClasses(),
            'checkBaseClasses' => $this->checkBaseClasses(),
            'checkInactiveClasses' => $this->checkInactiveClasses(),
            'checkActiveClasses' => $this->checkActiveClasses(),
            'checkIconClasses' => $this->checkIconClasses(),
            'disabledClasses' => $this->disabledClasses(),
        ];

        if ($advanced) {
            $params['searchPlaceholder'] = $this->searchPlaceholder;
            $params['emptyText'] = $this->emptyText;
            $params['searchable'] = $this->searchable;
            $params['multiple'] = $this->multiple;
            $params['withTags'] = $this->withTags;
            $params['searchClasses'] = $this->getSearchClasses();
            $params['searchIconClasses'] = $this->getSearchIconClasses();
            $params['tagClasses'] = $this->getTagClasses();
            $params['tagRemoveButtonClasses'] = $this->getTagRemoveButtonClasses();
            $params['tagRemoveSvgClasses'] = $this->getTagRemoveSvgClasses();
        }

        return $params;
    }

    /**
     * Panel search field, from Tailwind Plus
     * Application UI > Navigation > Command Palettes > Simple: no border of its
     * own, the separation comes from the container.
     *
     * @return string[]
     */
    protected function getSearchClasses(): array
    {
        $baseClasses = [
            'col-start-1',
            'row-start-1',
            'h-12',
            'w-full',
            'pr-4',
            'pl-11',
            'text-base',
            'outline-hidden',
            'sm:text-sm',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900', 'placeholder:text-primary-400'],
            Bleet::COLOR_SECONDARY => ['text-secondary-900', 'placeholder:text-secondary-400'],
            Bleet::COLOR_SUCCESS => ['text-success-900', 'placeholder:text-success-400'],
            Bleet::COLOR_DANGER => ['text-danger-900', 'placeholder:text-danger-400'],
            Bleet::COLOR_WARNING => ['text-warning-900', 'placeholder:text-warning-400'],
            Bleet::COLOR_INFO => ['text-info-900', 'placeholder:text-info-400'],
            Bleet::COLOR_ACCENT => ['text-accent-900', 'placeholder:text-accent-400'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * Search field magnifier, stacked in the same grid cell.
     *
     * @return string[]
     */
    protected function getSearchIconClasses(): array
    {
        $baseClasses = [
            'pointer-events-none',
            'col-start-1',
            'row-start-1',
            'ml-4',
            'size-5',
            'self-center',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-400'],
            Bleet::COLOR_SECONDARY => ['text-secondary-400'],
            Bleet::COLOR_SUCCESS => ['text-success-400'],
            Bleet::COLOR_DANGER => ['text-danger-400'],
            Bleet::COLOR_WARNING => ['text-warning-400'],
            Bleet::COLOR_INFO => ['text-info-400'],
            Bleet::COLOR_ACCENT => ['text-accent-400'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * Value tag, from Tailwind Plus
     * Application UI > Elements > Badges > With border and remove button.
     *
     * @return string[]
     */
    protected function getTagClasses(): array
    {
        $baseClasses = [
            'inline-flex',
            'items-center',
            'gap-x-0.5',
            'rounded-md',
            'px-2',
            'py-1',
            'text-xs',
            'font-medium',
            'inset-ring',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-50', 'text-primary-700', 'inset-ring-primary-700/10'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-50', 'text-secondary-600', 'inset-ring-secondary-500/10'],
            Bleet::COLOR_SUCCESS => ['bg-success-50', 'text-success-700', 'inset-ring-success-600/20'],
            Bleet::COLOR_DANGER => ['bg-danger-50', 'text-danger-700', 'inset-ring-danger-600/10'],
            Bleet::COLOR_WARNING => ['bg-warning-50', 'text-warning-800', 'inset-ring-warning-600/20'],
            Bleet::COLOR_INFO => ['bg-info-50', 'text-info-700', 'inset-ring-info-700/10'],
            Bleet::COLOR_ACCENT => ['bg-accent-50', 'text-accent-700', 'inset-ring-accent-700/10'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    protected function getTagRemoveButtonClasses(): array
    {
        $baseClasses = [
            'group',
            'relative',
            '-mr-1',
            'size-3.5',
            'rounded-xs',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['hover:bg-primary-600/20'],
            Bleet::COLOR_SECONDARY => ['hover:bg-secondary-500/20'],
            Bleet::COLOR_SUCCESS => ['hover:bg-success-600/20'],
            Bleet::COLOR_DANGER => ['hover:bg-danger-600/20'],
            Bleet::COLOR_WARNING => ['hover:bg-warning-600/20'],
            Bleet::COLOR_INFO => ['hover:bg-info-600/20'],
            Bleet::COLOR_ACCENT => ['hover:bg-accent-600/20'],
        };

        return [...$baseClasses, ...$colorClasses];
    }

    /**
     * The cross is a solid heroicon: the shade goes through text-*, where the
     * Tailwind block colors a stroked path with stroke-*.
     *
     * @return string[]
     */
    protected function getTagRemoveSvgClasses(): array
    {
        $baseClasses = [
            'size-3.5',
        ];

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-600/50', 'group-hover:text-primary-600/75'],
            Bleet::COLOR_SECONDARY => ['text-secondary-600/50', 'group-hover:text-secondary-600/75'],
            Bleet::COLOR_SUCCESS => ['text-success-700/50', 'group-hover:text-success-700/75'],
            Bleet::COLOR_DANGER => ['text-danger-600/50', 'group-hover:text-danger-600/75'],
            Bleet::COLOR_WARNING => ['text-warning-700/50', 'group-hover:text-warning-700/75'],
            Bleet::COLOR_INFO => ['text-info-700/50', 'group-hover:text-info-700/75'],
            Bleet::COLOR_ACCENT => ['text-accent-600/50', 'group-hover:text-accent-600/75'],
        };

        return [...$baseClasses, ...$colorClasses];
    }
    use BleetColorTrait;
    use BleetFieldTrait;

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    /**
     * @return string[]
     */
    protected function getPanelColorClasses(): array
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
    protected function getItemColorClasses(): array
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
    protected function getSelectedItemColorClasses(): array
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
    protected function getChevronColorClasses(): array
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
    protected function getCheckColorClasses(): array
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
    protected function buttonColorClasses(): array
    {
        return $this->inputColorClasses();
    }

    /**
     * @return string[]
     */
    protected function buttonClasses(): array
    {
        return [
            'grid',
            'w-full',
            'cursor-pointer',
            'grid-cols-1',
            'rounded-md',
            'bg-white',
            'py-1.5',
            'pr-2',
            'pl-3',
            'text-left',
            'text-base',
            'sm:text-sm/6',
            'outline-1',
            '-outline-offset-1',
            'focus:outline-2',
            'focus:-outline-offset-2',
            ...$this->buttonColorClasses(),
        ];
    }

    /**
     * @return string[]
     */
    protected function panelClasses(): array
    {
        return [
            'absolute',
            'z-10',
            'mt-1',
            'max-h-60',
            'w-full',
            'overflow-auto',
            'rounded-md',
            'bg-white',
            'py-1',
            'text-base',
            'shadow-lg',
            'hidden',
            ...$this->getPanelColorClasses(),
        ];
    }

    /**
     * @return string[]
     */
    protected function itemBaseClasses(): array
    {
        return [
            'relative',
            'w-full',
            'cursor-pointer',
            'py-2',
            'pr-4',
            'pl-8',
            'text-left',
            'focus:outline-none',
        ];
    }

    /**
     * @return string[]
     */
    protected function groupHeaderClasses(): array
    {
        return [
            'relative',
            'w-full',
            'cursor-default',
            'py-2',
            'pr-4',
            'pl-8',
            'text-left',
            'font-bold',
            'text-secondary-500',
            'bg-secondary-50',
        ];
    }

    /**
     * @return string[]
     */
    protected function chevronClasses(): array
    {
        return [
            'col-start-1',
            'row-start-1',
            'size-5',
            'self-center',
            'justify-self-end',
            ...$this->getChevronColorClasses(),
        ];
    }

    /**
     * @return string[]
     */
    protected function checkBaseClasses(): array
    {
        return [
            'absolute',
            'inset-y-0',
            'left-0',
            'flex',
            'items-center',
            'pl-1.5',
        ];
    }

    /**
     * @return string[]
     */
    protected function checkInactiveClasses(): array
    {
        return [
            'hidden',
            ...$this->getCheckColorClasses(),
        ];
    }

    /**
     * @return string[]
     */
    protected function checkActiveClasses(): array
    {
        return ['text-white'];
    }

    /**
     * @return string[]
     */
    protected function checkIconClasses(): array
    {
        return ['size-5'];
    }

    /**
     * @return string[]
     */
    protected function disabledClasses(): array
    {
        return [
            'opacity-50',
            'cursor-not-allowed',
        ];
    }
}
