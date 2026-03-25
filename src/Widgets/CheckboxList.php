<?php

declare(strict_types=1);

/**
 * CheckboxList.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetColorTrait;
use Blackcube\Bleet\Traits\BleetFieldDataTrait;
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Yiisoft\Html\Html;

/**
 * CheckboxList widget - Groupe de checkboxes pour formulaires
 *
 * Usage:
 *   Bleet::checkboxList()
 *       ->name('features[]')
 *       ->items(['wifi' => 'WiFi', 'parking' => 'Parking', 'pool' => 'Pool'])
 *       ->values(['wifi', 'pool'])
 *       ->render()
 *
 *   Bleet::checkboxList()
 *       ->active($model, 'features')
 *       ->items(['wifi' => 'WiFi', 'parking' => 'Parking'])
 *       ->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class CheckboxList extends AbstractWidget
{
    use BleetColorTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private ?string $name = null;
    private ?string $id = null;
    /** @var array<string, string> value => label */
    private array $items = [];
    /** @var string[] selected values */
    private array $values = [];
    private bool $disabled = false;
    private bool $required = false;
    private ?string $label = null;
    private ?string $hint = null;

    /**
     * Sets the name
     */
    public function name(string $name): self
    {
        $new = clone $this;
        $new->name = $name;
        return $new;
    }

    /**
     * Sets the id
     */
    public function id(string $id): self
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    /**
     * Sets the items
     * @param array<string, string> $items value => label
     */
    public function items(array $items): self
    {
        $new = clone $this;
        $new->items = $items;
        return $new;
    }

    /**
     * Sets the selected values
     * @param string[] $values
     */
    public function values(array $values): self
    {
        $new = clone $this;
        $new->values = $values;
        return $new;
    }

    /**
     * Disables the checkbox list
     */
    public function disabled(bool $disabled = true): self
    {
        $new = clone $this;
        $new->disabled = $disabled;
        return $new;
    }

    /**
     * Marks as required
     */
    public function required(bool $required = true): self
    {
        $new = clone $this;
        $new->required = $required;
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
     * Sets the hint
     */
    public function hint(string $hint): self
    {
        $new = clone $this;
        $new->hint = $hint;
        return $new;
    }

    /**
     * Renders the checkbox list
     */
    public function render(): string
    {
        $html = '';

        // Label
        $labelHtml = $this->renderLabel();
        if ($labelHtml !== '') {
            $html .= $labelHtml;
        }

        // Checkbox items container
        $html .= Html::openTag('div', ['class' => 'space-y-2']);

        // Value resolution: explicit > model > empty array
        $name = $this->name ?? $this->getInputName();
        $modelValue = $this->getValue();
        $selectedValues = !empty($this->values) ? $this->values : (is_array($modelValue) ? $modelValue : []);
        $baseId = $this->id ?? $this->getInputId() ?? 'checkbox-list';

        // Ensure name has [] for array submission
        if ($name !== null && !str_ends_with($name, '[]')) {
            $name .= '[]';
        }

        $i = 0;
        foreach ($this->items as $itemValue => $itemLabel) {
            $checkbox = Bleet::checkbox()
                ->color($this->color)
                ->value((string) $itemValue)
                ->label($itemLabel)
                ->checked(in_array((string) $itemValue, $selectedValues, true))
                ->id($baseId . '-' . $i);

            if ($name !== null) {
                $checkbox = $checkbox->name($name);
            }

            if ($this->disabled) {
                $checkbox = $checkbox->disabled();
            }

            if (!empty($this->fieldData)) {
                $checkbox = $checkbox->fieldData($this->fieldData);
            }

            $html .= $checkbox->render();
            $i++;
        }

        $html .= Html::closeTag('div');

        // Hint
        $hintHtml = $this->renderHint();
        if ($hintHtml !== '') {
            $html .= $hintHtml;
        }

        return $html;
    }

    /**
     * Renders the label
     */
    private function renderLabel(): string
    {
        // Label resolution: explicit > model > null
        $labelContent = $this->label ?? $this->getLabel();

        if ($labelContent === null || $labelContent === '') {
            return '';
        }

        $labelWidget = Bleet::label($labelContent)->color($this->color);

        if ($this->hasModel()) {
            $labelWidget = $labelWidget->active($this->getModel(), $this->getProperty());
        }

        if ($this->required || $this->isRequired()) {
            $labelWidget = $labelWidget->required();
        }

        return $labelWidget->render();
    }

    /**
     * Renders the hint
     */
    private function renderHint(): string
    {
        // Hint resolution: explicit > model > null
        $hintContent = $this->hint ?? $this->getHint();

        if ($hintContent === null || $hintContent === '') {
            return '';
        }

        return Html::p($hintContent, ['class' => 'mt-1 text-sm ' . $this->textMutedColorClass()])->render();
    }

    /**
     * Base classes (unused, required by AbstractWidget)
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }
}
