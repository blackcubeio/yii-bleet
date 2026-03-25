<?php

declare(strict_types=1);

/**
 * RadioList.php
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
 * RadioList widget - Groupe de boutons radio pour formulaires
 *
 * Usage:
 *   Bleet::radioList()
 *       ->name('plan')
 *       ->items(['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'])
 *       ->value('medium')
 *       ->render()
 *
 *   Bleet::radioList()
 *       ->active($model, 'plan')
 *       ->items(['small' => 'Small', 'medium' => 'Medium'])
 *       ->render()
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class RadioList extends AbstractWidget
{
    use BleetColorTrait;
    use BleetFieldDataTrait;
    use BleetModelAwareTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private ?string $name = null;
    private ?string $id = null;
    /** @var array<string, string> value => label */
    private array $items = [];
    private ?string $value = null;
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
     * Sets the selected value
     */
    public function value(?string $value): self
    {
        $new = clone $this;
        $new->value = $value;
        return $new;
    }

    /**
     * Disables the radio list
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
     * Renders the radio list
     */
    public function render(): string
    {
        $html = '';

        // Label
        $labelHtml = $this->renderLabel();
        if ($labelHtml !== '') {
            $html .= $labelHtml;
        }

        // Radio items container
        $html .= Html::openTag('div', ['class' => 'space-y-2']);

        // Value resolution: explicit > model > null
        $name = $this->name ?? $this->getInputName();
        $selectedValue = $this->value ?? $this->getValue();
        $baseId = $this->id ?? $this->getInputId() ?? 'radio-list';

        $i = 0;
        foreach ($this->items as $itemValue => $itemLabel) {
            $radio = Bleet::radio()
                ->color($this->color)
                ->value((string) $itemValue)
                ->label($itemLabel)
                ->checked((string) $selectedValue === (string) $itemValue)
                ->id($baseId . '-' . $i);

            if ($name !== null) {
                $radio = $radio->name($name);
            }

            if ($this->disabled) {
                $radio = $radio->disabled();
            }

            if ($this->required && $i === 0) {
                $radio = $radio->required();
            }

            if (!empty($this->fieldData)) {
                $radio = $radio->fieldData($this->fieldData);
            }

            $html .= $radio->render();
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
