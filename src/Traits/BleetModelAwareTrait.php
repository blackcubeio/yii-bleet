<?php

declare(strict_types=1);

/**
 * BleetModelAwareTrait.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

use Blackcube\Bleet\Helper\ActiveHelper;
use Yiisoft\FormModel\FormModelInterface;
use Yiisoft\Html\Html;
use Yiisoft\Validator\Helper\RulesNormalizer;
use Yiisoft\Validator\Rule\AbstractNumber;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Regex;
use Yiisoft\Validator\Rule\Required;
use Yiisoft\Validator\Rule\Url;

/**
 * Trait for model-aware widgets.
 *
 * Provides model binding capabilities allowing widgets to get values,
 * labels, hints, placeholders, errors, and validation attributes from a FormModel.
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait BleetModelAwareTrait
{
    private ?FormModelInterface $model = null;
    private ?string $property = null;

    /**
     * Binds the widget to a model property.
     *
     * @param FormModelInterface $model The form model
     * @param string $property The property name
     * @return static
     */
    public function active(FormModelInterface $model, string $property): static
    {
        $new = clone $this;
        $new->model = $model;
        $new->property = $property;
        return $new;
    }

    /**
     * Returns the bound model.
     */
    protected function getModel(): ?FormModelInterface
    {
        return $this->model;
    }

    /**
     * Returns the bound property name.
     */
    protected function getProperty(): ?string
    {
        return $this->property;
    }

    /**
     * Extracts the actual attribute name from tabular property format.
     *
     * Examples:
     * - `name` → `name`
     * - `[0]allowed` → `allowed`
     * - `[items][0]name` → `name`
     */
    protected function getAttributeName(): ?string
    {
        if ($this->property === null) {
            return null;
        }

        return ActiveHelper::getAttributeName($this->property);
    }

    /**
     * Returns the property value from the model.
     */
    protected function getValue(): mixed
    {
        if ($this->model === null || $this->property === null) {
            return null;
        }

        return $this->model->getPropertyValue($this->getAttributeName());
    }

    /**
     * Returns the property label from the model.
     */
    protected function getLabel(): ?string
    {
        if ($this->model === null || $this->property === null) {
            return null;
        }

        return $this->model->getPropertyLabel($this->getAttributeName());
    }

    /**
     * Returns the property hint from the model.
     */
    protected function getHint(): ?string
    {
        if ($this->model === null || $this->property === null) {
            return null;
        }

        $hint = $this->model->getPropertyHint($this->getAttributeName());

        return $hint !== '' ? $hint : null;
    }

    /**
     * Returns the property placeholder from the model.
     */
    protected function getPlaceholder(): ?string
    {
        if ($this->model === null || $this->property === null) {
            return null;
        }

        $placeholder = $this->model->getPropertyPlaceholder($this->getAttributeName());

        return $placeholder !== '' ? $placeholder : null;
    }

    /**
     * Returns validation error messages for the bound property.
     *
     * @return list<string>
     */
    protected function getErrors(): array
    {
        if ($this->model === null || $this->property === null) {
            return [];
        }

        if (!$this->model->isValidated()) {
            return [];
        }

        return $this->model->getValidationResult()->getPropertyErrorMessages($this->getAttributeName());
    }

    /**
     * Returns whether the bound property has validation errors.
     */
    protected function hasErrors(): bool
    {
        return $this->getErrors() !== [];
    }

    /**
     * Returns whether the bound property is required.
     */
    protected function isRequired(): bool
    {
        if ($this->model === null || $this->property === null) {
            return false;
        }

        $rules = RulesNormalizer::normalize(null, $this->model);
        $propertyRules = $rules[$this->getAttributeName()] ?? [];

        foreach ($propertyRules as $rule) {
            if ($rule instanceof Required) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns HTML input attributes derived from validation rules.
     *
     * @return array<string, mixed>
     */
    protected function getInputAttributes(): array
    {
        if ($this->model === null || $this->property === null) {
            return [];
        }

        $rules = RulesNormalizer::normalize(null, $this->model);
        $propertyRules = $rules[$this->getAttributeName()] ?? [];
        $attributes = [];

        foreach ($propertyRules as $rule) {
            $this->enrichAttributesFromRule($rule, $attributes);
        }

        return $attributes;
    }

    /**
     * Enriches HTML attributes from a single validation rule.
     *
     * @param mixed $rule
     * @param array<string, mixed> $attributes
     */
    private function enrichAttributesFromRule(mixed $rule, array &$attributes): void
    {
        if ($rule instanceof Required) {
            $attributes['required'] = true;
            return;
        }

        if ($rule instanceof Length) {
            if (($min = $rule->getMin()) !== null) {
                $attributes['minlength'] = $min;
            }
            if (($max = $rule->getMax()) !== null) {
                $attributes['maxlength'] = $max;
            }
            return;
        }

        if ($rule instanceof AbstractNumber) {
            if (($min = $rule->getMin()) !== null) {
                $attributes['min'] = $min;
            }
            if (($max = $rule->getMax()) !== null) {
                $attributes['max'] = $max;
            }
            return;
        }

        if ($rule instanceof Regex && !$rule->isNot()) {
            $attributes['pattern'] = Html::normalizeRegexpPattern($rule->getPattern());
            return;
        }

        if ($rule instanceof Email) {
            $attributes['type'] = 'email';
            return;
        }

        if ($rule instanceof Url) {
            $attributes['type'] = 'url';
            return;
        }
    }

    /**
     * Returns the input name for the bound property.
     *
     * Supports Yii2-style tabular input syntax:
     * - `[0]allowed` → `FormName[0][allowed]`
     * - `[items][0]name` → `FormName[items][0][name]`
     */
    protected function getInputName(): ?string
    {
        if ($this->model === null || $this->property === null) {
            return null;
        }

        return ActiveHelper::getInputName($this->model, $this->property);
    }

    /**
     * Returns the input id for the bound property.
     *
     * Uses the input name and converts brackets to dashes (Yii2 pattern).
     */
    protected function getInputId(): ?string
    {
        if ($this->model === null || $this->property === null) {
            return null;
        }

        return ActiveHelper::getInputId($this->model, $this->property);
    }

    /**
     * Returns whether the model has been bound.
     */
    protected function hasModel(): bool
    {
        return $this->model !== null && $this->property !== null;
    }
}
