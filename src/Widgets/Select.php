<?php

declare(strict_types=1);

/**
 * Select.php
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
final class Select extends AbstractWidget
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
    private ?string $selected = null;
    private bool $disabled = false;
    private ?string $labelledBy = null;
    private ?string $describedBy = null;
    private bool $withLabel = false;

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
     * Sets the label or controls its visibility.
     * @param string|bool $label String for custom label, true to show (default), false to hide
     */
    public function label(string|bool $label): self
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
    public function placeholder(string $placeholder): self
    {
        $new = clone $this;
        $new->placeholder = $placeholder;
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
     * Sets the selected value
     */
    public function selected(?string $value): self
    {
        $new = clone $this;
        $new->selected = $value;
        return $new;
    }

    /**
     * Marks the field as disabled
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
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
        return $this->renderView('select', $this->prepareViewParams());
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

        $containerAttributes = $this->prepareWrapperAttributes();
        $containerAttributes['bleet-select'] = '';
        \Yiisoft\Html\Html::addCssClass($containerAttributes, ['relative']);

        return [
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
            'chevronClasses' => ['col-start-1', 'row-start-1', 'size-5', 'self-center', 'justify-self-end', ...$this->getChevronColorClasses()],
            'checkBaseClasses' => ['absolute', 'inset-y-0', 'left-0', 'flex', 'items-center', 'pl-1.5'],
            'checkInactiveClasses' => ['hidden', ...$this->getCheckColorClasses()],
            'checkActiveClasses' => ['text-white'],
        ];
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
}
