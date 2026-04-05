<?php

declare(strict_types=1);

/**
 * dropdown.php
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
 * @var string $searchPlaceholder
 * @var string $emptyText
 * @var array<string, string|array<string, string>> $options
 * @var array<string> $selected
 * @var string|null $labelledBy
 * @var string|null $describedBy
 * @var bool $searchable
 * @var bool $multiple
 * @var bool $withTags
 * @var bool $disabled
 * @var array $containerAttributes
 * @var array<string, string> $fieldData
 * @var array $buttonClasses
 * @var array $panelClasses
 * @var array $searchClasses
 * @var array $itemBaseClasses
 * @var array $itemInactiveClasses
 * @var array $itemActiveClasses
 * @var array $groupHeaderClasses
 * @var array $chevronClasses
 * @var array $checkBaseClasses
 * @var array $checkInactiveClasses
 * @var array $checkActiveClasses
 * @var array $tagClasses
 * @var array $tagRemoveButtonClasses
 * @var array $tagRemoveSvgClasses
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('div', $containerAttributes); ?>

<?php if ($label !== ''): ?>
    <?php echo $label; ?>
<?php endif; ?>

<?php
$selectAttributes = [
    'name' => $name,
    'class' => 'sr-only',
    'aria-hidden' => 'true',
    'tabindex' => '-1',
];
if ($id !== null) {
    $selectAttributes['id'] = $id;
}
if ($multiple) {
    $selectAttributes['multiple'] = true;
}
if ($disabled) {
    $selectAttributes['disabled'] = true;
}
$selectAttributes = array_merge($selectAttributes, $fieldData);
?>
<?php echo Html::openTag('select', $selectAttributes); ?>
<?php if ($placeholder !== '' && !$multiple): ?>
    <?php echo Html::tag('option', Html::encode($placeholder), ['value' => '']); ?>
<?php endif; ?>
<?php $selectedStrings = array_map('strval', $selected); ?>
<?php foreach ($options as $key => $optionValue): ?>
    <?php if (is_array($optionValue)): ?>
        <?php echo Html::openTag('optgroup', ['label' => $key]); ?>
        <?php foreach ($optionValue as $value => $optionLabel): ?>
            <?php
            $optionAttributes = ['value' => $value];
            if (in_array((string)$value, $selectedStrings, true)) {
                $optionAttributes['selected'] = true;
            }
            ?>
            <?php echo Html::tag('option', Html::encode($optionLabel), $optionAttributes); ?>
        <?php endforeach; ?>
        <?php echo Html::closeTag('optgroup'); ?>
    <?php else: ?>
        <?php
        $optionAttributes = ['value' => $key];
        if (in_array((string)$key, $selectedStrings, true)) {
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
<?php if ($withTags): ?>
    <span data-dropdown="tags" class="col-start-1 row-start-1 flex flex-wrap gap-1 pr-6">
        <span data-dropdown="placeholder" class="text-secondary-400"><?php echo Html::encode($placeholder); ?></span>
    </span>
<?php else: ?>
    <span data-dropdown="value" class="col-start-1 row-start-1 truncate pr-6"></span>
<?php endif; ?>
    <?php echo Bleet::svg()->solid('chevron-down')->addClass(...$chevronClasses); ?>
<?php echo Html::closeTag('button'); ?>

<?php if ($withTags): ?>
<template data-dropdown="tag-template">
    <?php echo Html::openTag('span', ['class' => $tagClasses, 'data-tag-value' => true]); ?>
        <span data-dropdown="tag-text"></span>
        <?php echo Html::openTag('button', [
            'type' => 'button',
            'data-dropdown' => 'tag-remove',
            'class' => $tagRemoveButtonClasses,
        ]); ?>
            <?php echo Bleet::svg()->solid('x-mark')->addClass(...$tagRemoveSvgClasses); ?>
        <?php echo Html::closeTag('button'); ?>
    <?php echo Html::closeTag('span'); ?>
</template>
<?php endif; ?>

<?php echo Html::openTag('div', [
    'data-dropdown' => 'items',
    'role' => 'listbox',
    'class' => $panelClasses,
    'tabindex' => '-1',
]); ?>
<?php if ($searchable): ?>
    <div class="sticky top-0 z-10 bg-white p-2">
        <?php echo Html::input('text', '', '', [
            'data-dropdown' => 'search',
            'class' => $searchClasses,
            'placeholder' => $searchPlaceholder,
        ]); ?>
    </div>
<?php endif; ?>
    <div data-dropdown="items-container">
    </div>
    <div data-dropdown="empty" class="hidden py-2 px-3 text-sm text-secondary-500">
        <?php echo Html::encode($emptyText); ?>
    </div>
    <template data-dropdown="item-template">
        <?php echo Html::openTag('button', [
            'type' => 'button',
            'data-value' => true,
            'class' => $itemBaseClasses,
            'data-class-inactive' => implode(' ', $itemInactiveClasses),
            'data-class-active' => implode(' ', $itemActiveClasses),
        ]); ?>
            <span data-dropdown="item-text" class="block truncate"></span>
            <?php echo Html::openTag('span', [
                'data-dropdown' => 'item-check',
                'class' => $checkBaseClasses,
                'data-class-inactive' => implode(' ', $checkInactiveClasses),
                'data-class-active' => implode(' ', $checkActiveClasses),
            ]); ?>
                <?php echo Bleet::svg()->solid('check')->addClass('size-5'); ?>
            <?php echo Html::closeTag('span'); ?>
        <?php echo Html::closeTag('button'); ?>
    </template>
    <template data-dropdown="group-template">
        <?php echo Html::openTag('div', [
            'data-dropdown' => 'group',
            'role' => 'presentation',
            'class' => $groupHeaderClasses,
        ]); ?>
            <span data-dropdown="group-label" class="block truncate"></span>
        <?php echo Html::closeTag('div'); ?>
    </template>
<?php echo Html::closeTag('div'); ?>

<?php echo Html::closeTag('div'); ?>
