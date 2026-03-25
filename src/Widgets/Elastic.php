<?php

declare(strict_types=1);

/**
 * Elastic.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Helper\ActiveHelper;
use Blackcube\Bleet\Traits\BleetModelAwareTrait;
use Blackcube\BridgeModel\BridgeFormModel;
use Yiisoft\Html\Html;

/**
 * Elastic field widget — auto-renders based on JSON Schema meta.
 *
 * Usage:
 *   Bleet::elastic($elasticOptions)->active($model, $attribute)->render()
 *
 * @copyright 2010-2026 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Elastic
{
    use BleetModelAwareTrait;

    public function __construct(private array $elasticOptions = [])
    {
    }

    public function render(): string
    {
        $model = $this->getModel();
        $propertyName = $this->getAttributeName();
        $attribute = $this->getProperty();

        if (!$model instanceof BridgeFormModel) {
            return '';
        }

        $properties = $model->getProperties();
        if (!isset($properties[$propertyName])) {
            return '';
        }

        $meta = $properties[$propertyName]->getMeta();
        $field = $meta['field'] ?? 'text';

        return match ($field) {
            'textarea' => $this->renderTextarea($model, $attribute),
            'wysiwyg' => $this->renderWysiwyg($model, $attribute, $propertyName, $meta),
            'email' => $this->renderInput('email', $model, $attribute),
            'number' => $this->renderInput('number', $model, $attribute),
            'date' => $this->renderInput('date', $model, $attribute),
            'datetime-local' => $this->renderInput('datetime-local', $model, $attribute),
            'dropdownList', 'dropdownlist' => $this->renderDropdown($model, $attribute, $meta),
            'radioList', 'radiolist' => $this->renderRadioList($model, $attribute, $meta),
            'checkboxList', 'checkboxlist' => $this->renderCheckboxList($model, $attribute, $meta),
            'checkbox' => $this->renderCheckbox($model, $attribute),
            'file' => $this->renderFileUpload($model, $attribute, $meta, false),
            'files' => $this->renderFileUpload($model, $attribute, $meta, true),
            default => $this->renderInput('text', $model, $attribute),
        };
    }

    private function renderInput(string $type, BridgeFormModel $model, string $attribute): string
    {
        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= Bleet::label()->active($model, $attribute)->secondary()->render();
        $html .= Html::openTag('div', ['class' => 'mt-2']);
        $html .= Bleet::input()
            ->type($type)
            ->active($model, $attribute)
            ->secondary()
            ->render();
        $html .= Html::closeTag('div');
        $hint = $this->getHint();
        if ($hint !== null) {
            $html .= Html::tag('p', Html::encode($hint), ['class' => 'mt-1 text-sm text-secondary-500']);
        }
        $html .= Html::closeTag('div');
        return $html;
    }

    private function renderTextarea(BridgeFormModel $model, string $attribute): string
    {
        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= Bleet::label()->active($model, $attribute)->secondary()->render();
        $html .= Html::openTag('div', ['class' => 'mt-2']);
        $html .= Bleet::textarea()
            ->active($model, $attribute)
            ->secondary()
            ->render();
        $html .= Html::closeTag('div');
        $hint = $this->getHint();
        if ($hint !== null) {
            $html .= Html::tag('p', Html::encode($hint), ['class' => 'mt-1 text-sm text-secondary-500']);
        }
        $html .= Html::closeTag('div');
        return $html;
    }

    private function renderWysiwyg(BridgeFormModel $model, string $attribute, string $propertyName, array $meta): string
    {
        $id = ActiveHelper::getInputId($model, $attribute);
        $name = ActiveHelper::getInputName($model, $attribute);
        $value = $model->{$propertyName} ?? '';

        $aureliaOptions = [
            'fieldId' => $id,
            'fieldName' => $name,
            'content' => $value,
        ];

        if (!empty($meta['options'])) {
            $aureliaOptions['options.bind'] = $meta['options'];
        }

        $attributes = Aurelia::attributesCustomElement($aureliaOptions);

        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= Bleet::label()->active($model, $attribute)->secondary()->render();
        $html .= Html::openTag('div', ['class' => 'mt-2']);
        $html .= Html::tag('bleet-quilljs', '')
            ->attributes($attributes)
            ->encode(false)
            ->render();
        $html .= Html::closeTag('div');
        $hint = $this->getHint();
        if ($hint !== null) {
            $html .= Html::tag('p', Html::encode($hint), ['class' => 'mt-1 text-sm text-secondary-500']);
        }
        $html .= Html::closeTag('div');
        return $html;
    }

    private function renderDropdown(BridgeFormModel $model, string $attribute, array $meta): string
    {
        $options = $this->extractItems($meta);

        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= Bleet::label()->active($model, $attribute)->secondary()->render();
        $html .= Html::openTag('div', ['class' => 'mt-2']);
        $html .= Bleet::select()
            ->active($model, $attribute)
            ->options($options)
            ->secondary()
            ->render();
        $html .= Html::closeTag('div');
        $hint = $this->getHint();
        if ($hint !== null) {
            $html .= Html::tag('p', Html::encode($hint), ['class' => 'mt-1 text-sm text-secondary-500']);
        }
        $html .= Html::closeTag('div');
        return $html;
    }

    private function renderRadioList(BridgeFormModel $model, string $attribute, array $meta): string
    {
        $items = $this->extractItems($meta);

        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= Bleet::radioList()
            ->active($model, $attribute)
            ->items($items)
            ->secondary()
            ->render();
        $html .= Html::closeTag('div');
        return $html;
    }

    private function renderCheckboxList(BridgeFormModel $model, string $attribute, array $meta): string
    {
        $items = $this->extractItems($meta);

        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= Bleet::checkboxList()
            ->active($model, $attribute)
            ->items($items)
            ->secondary()
            ->render();
        $html .= Html::closeTag('div');
        return $html;
    }

    private function renderCheckbox(BridgeFormModel $model, string $attribute): string
    {
        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= Bleet::checkbox()
            ->active($model, $attribute)
            ->uncheckValue('0')
            ->value('1')
            ->secondary()
            ->render();
        $html .= Html::closeTag('div');
        return $html;
    }

    private function renderFileUpload(BridgeFormModel $model, string $attribute, array $meta, bool $multiple): string
    {
        $accept = [];
        if (!empty($meta['fileType'])) {
            $accept = array_map('trim', explode(',', $meta['fileType']));
        }

        $upload = Bleet::upload()->active($model, $attribute);

        if (isset($this->elasticOptions['upload'])) {
            $upload = $upload->endpoint($this->elasticOptions['upload']);
        }
        if (isset($this->elasticOptions['preview'])) {
            $upload = $upload->previewEndpoint($this->elasticOptions['preview']);
        }
        if (isset($this->elasticOptions['delete'])) {
            $upload = $upload->deleteEndpoint($this->elasticOptions['delete']);
        }

        if (!empty($accept)) {
            $upload = $upload->accept($accept);
        }

        if ($multiple) {
            $upload = $upload->multiple();
        }

        $html = Html::openTag('div', ['class' => 'mb-4']);
        $html .= $upload->render();
        $html .= Html::closeTag('div');
        return $html;
    }

    private function extractItems(array $meta): array
    {
        $options = [];
        if (!empty($meta['items']) && is_array($meta['items'])) {
            foreach ($meta['items'] as $item) {
                if (isset($item['value'], $item['title'])) {
                    $options[(string) $item['value']] = $item['title'];
                }
            }
        }
        return $options;
    }
}
