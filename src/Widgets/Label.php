<?php

declare(strict_types=1);

/**
 * Label.php
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
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Yiisoft\Html\Html;

/**
 * Label widget - Label for form elements
 *
 * Usage:
 *   Bleet::label('Nom')->for('input-name')->render()
 *   Bleet::label('Email')->for('input-email')->primary()->render()
 *   Bleet::label('Requis')->required()->danger()->render()
 *   Bleet::label()->active($model, 'email')->render() // model-bound
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Label extends AbstractWidget
{
    use BleetAttributesTrait;
    use BleetColorTrait;
    use BleetModelAwareTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    private string $content = '';
    private ?string $for = null;
    private bool $required = false;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Sets the content of the label
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Returns the content of the label
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Sets the for attribute (lie to the field)
     */
    public function for(string $for): self
    {
        $new = clone $this;
        $new->for = $for;
        return $new;
    }

    /**
     * Marks the field as required (adds *)
     */
    public function required(bool $required = true): self
    {
        $new = clone $this;
        $new->required = $required;
        return $new;
    }

    public function render(): string
    {
        // Value resolution : explicit > model > empty
        $content = $this->content !== '' ? $this->content : ($this->getLabel() ?? '');
        $for = $this->for ?? $this->getInputId();
        $required = $this->required || $this->isRequired();

        $innerHtml = Html::encode($content);

        if ($required) {
            $innerHtml .= Html::tag('span', ' *', ['class' => $this->getRequiredClasses()])->encode(false);
        }

        $defaults = [];
        if ($for !== null) {
            $defaults['for'] = $for;
        }

        $attributes = $this->prepareTagAttributes($defaults);
        Html::addCssClass($attributes, $this->prepareClasses());

        return Html::tag('label', $innerHtml, $attributes)->encode(false)->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return ['block', 'text-sm', 'font-medium', $this->textColorClass()];
    }

    /**
     * @return string
     */
    private function getRequiredClasses(): string
    {
        return $this->textMutedColorClass();
    }
}
