<?php

declare(strict_types=1);

/**
 * select.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var string $name
 * @var string|null $id
 * @var string $label
 * @var string $placeholder
 * @var array<string, string|array<string, string>> $options
 * @var string|null $selected
 * @var bool $disabled
 * @var string|null $labelledBy
 * @var string|null $describedBy
 * @var array $containerAttributes
 * @var array<string, string> $fieldData
 * @var array $buttonClasses
 * @var array $panelClasses
 * @var array $itemBaseClasses
 * @var array $itemInactiveClasses
 * @var array $itemActiveClasses
 * @var array $groupHeaderClasses
 * @var array $chevronClasses
 * @var array $checkBaseClasses
 * @var array $checkInactiveClasses
 * @var array $checkActiveClasses
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('div', $containerAttributes); ?>

<?php if ($label !== ''): ?>
    <?php echo $label; ?>
<?php endif; ?>

<?php
$selectAttributes = ['name' => $name, 'class' => 'sr-only', 'aria-hidden' => 'true', 'tabindex' => '-1'];
if ($id !== null) {
    $selectAttributes['id'] = $id;
}
if ($disabled) {
    $selectAttributes['disabled'] = true;
}
$selectAttributes = array_merge($selectAttributes, $fieldData);
?>
<?php echo Html::openTag('select', $selectAttributes); ?>
<?php if ($placeholder !== ''): ?>
    <?php echo Html::tag('option', Html::encode($placeholder), ['value' => '']); ?>
<?php endif; ?>
<?php foreach ($options as $key => $optionValue): ?>
    <?php if (is_array($optionValue)): ?>
        <?php echo Html::openTag('optgroup', ['label' => $key]); ?>
        <?php foreach ($optionValue as $value => $optionLabel): ?>
            <?php
            $optionAttributes = ['value' => $value];
            if ((string)$selected === (string)$value) {
                $optionAttributes['selected'] = true;
            }
            ?>
            <?php echo Html::tag('option', Html::encode($optionLabel), $optionAttributes); ?>
        <?php endforeach; ?>
        <?php echo Html::closeTag('optgroup'); ?>
    <?php else: ?>
        <?php
        $optionAttributes = ['value' => $key];
        if ((string)$selected === (string)$key) {
            $optionAttributes['selected'] = true;
        }
        ?>
        <?php echo Html::tag('option', Html::encode($optionValue), $optionAttributes); ?>
    <?php endif; ?>
<?php endforeach; ?>
<?php echo Html::closeTag('select'); ?>

<?php
$buttonAttributes = [
    'type' => 'button',
    'class' => $buttonClasses,
    'aria-haspopup' => 'listbox',
    'aria-expanded' => 'false',
];
if ($labelledBy !== null) {
    $buttonAttributes['aria-labelledby'] = $labelledBy;
}
if ($describedBy !== null) {
    $buttonAttributes['aria-describedby'] = $describedBy;
}
if ($disabled) {
    $buttonAttributes['disabled'] = true;
    $buttonAttributes['class'] = array_merge((array)$buttonAttributes['class'], ['opacity-50', 'cursor-not-allowed']);
}
?>
<?php echo Html::openTag('button', $buttonAttributes); ?>
    <span data-select="value" class="col-start-1 row-start-1 truncate pr-6"></span>
    <?php echo Bleet::svg()->solid('chevron-down')->addClass(...$chevronClasses); ?>
<?php echo Html::closeTag('button'); ?>

<?php echo Html::openTag('div', [
    'data-select' => 'items',
    'role' => 'listbox',
    'class' => $panelClasses,
    'tabindex' => '-1',
]); ?>
    <template data-select="item-template">
        <?php echo Html::openTag('button', [
            'type' => 'button',
            'data-value' => true,
            'class' => $itemBaseClasses,
            'data-class-inactive' => implode(' ', $itemInactiveClasses),
            'data-class-active' => implode(' ', $itemActiveClasses),
        ]); ?>
            <span data-select="item-text" class="block truncate"></span>
            <?php echo Html::openTag('span', [
                'data-select' => 'item-check',
                'class' => $checkBaseClasses,
                'data-class-inactive' => implode(' ', $checkInactiveClasses),
                'data-class-active' => implode(' ', $checkActiveClasses),
            ]); ?>
                <?php echo Bleet::svg()->solid('check')->addClass('size-5'); ?>
            <?php echo Html::closeTag('span'); ?>
        <?php echo Html::closeTag('button'); ?>
    </template>
    <template data-select="group-template">
        <?php echo Html::openTag('div', [
            'data-select' => 'group',
            'role' => 'presentation',
            'class' => $groupHeaderClasses,
        ]); ?>
            <span data-select="group-label" class="block truncate"></span>
        <?php echo Html::closeTag('div'); ?>
    </template>
<?php echo Html::closeTag('div'); ?>

<?php echo Html::closeTag('div'); ?>
