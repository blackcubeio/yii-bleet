<?php

declare(strict_types=1);

/**
 * Elastic.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Form\Field\Checkbox as BaseCheckbox;
use Blackcube\Form\Field\CheckboxList as BaseCheckboxList;
use Blackcube\Form\Field\Elastic as BaseElastic;
use Blackcube\Form\Field\Input as BaseInput;
use Blackcube\Form\Field\Label as BaseLabel;
use Blackcube\Form\Field\RadioList as BaseRadioList;
use Blackcube\Form\Field\Select as BaseSelect;
use Blackcube\Form\Field\Textarea as BaseTextarea;
use Blackcube\Form\Field\Upload as BaseUpload;
use Yiisoft\FormModel\FormModelInterface;

/**
 * The field derived from the schema, styled with the Bleet colors.
 *
 * The dispatch - which field for which type declared by the model - lives in
 * blackcube/yii-form; here, the fields produced are the Bleet ones.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Elastic extends BaseElastic
{
    protected function makeInput(FormModelInterface $model, string $property): BaseInput
    {
        return Bleet::input($model, $property)->secondary();
    }

    protected function makeTextarea(FormModelInterface $model, string $property): BaseTextarea
    {
        return Bleet::textarea($model, $property)->secondary();
    }

    protected function makeSelect(FormModelInterface $model, string $property): BaseSelect
    {
        return Bleet::select($model, $property)->secondary();
    }

    protected function makeRadioList(FormModelInterface $model, string $property): BaseRadioList
    {
        return Bleet::radioList($model, $property)->secondary();
    }

    protected function makeCheckboxList(FormModelInterface $model, string $property): BaseCheckboxList
    {
        return Bleet::checkboxList($model, $property)->secondary();
    }

    protected function makeCheckbox(FormModelInterface $model, string $property): BaseCheckbox
    {
        return Bleet::checkbox($model, $property)->secondary();
    }

    protected function makeUpload(FormModelInterface $model, string $property): BaseUpload
    {
        return Bleet::upload($model, $property)->secondary();
    }

    protected function makeLabel(string $content): BaseLabel
    {
        return Bleet::label($content)->secondary();
    }

    /**
     * @return string[]
     */
    protected function blockClasses(): array
    {
        return ['mb-4'];
    }

    /**
     * @return string[]
     */
    protected function controlClasses(): array
    {
        return ['mt-2'];
    }
}
