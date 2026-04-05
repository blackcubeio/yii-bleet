<?php

declare(strict_types=1);

/**
 * upload.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var string|null $name
 * @var string|null $id
 * @var string|null $value
 * @var bool $required
 * @var bool $disabled
 * @var string $labelHtml
 * @var array $containerAttributes
 * @var string $dropzoneText
 * @var string|null $hint
 * @var array $dropzoneClasses
 * @var array $dropzoneIconClasses
 * @var array $dropzoneTextClasses
 * @var array $hintClasses
 * @var array $previewItemClasses
 * @var array $previewLinkClasses
 * @var array $previewImageClasses
 * @var array $previewIconClasses
 * @var array $previewNameClasses
 * @var array $previewRemoveClasses
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

?>
<?php if ($labelHtml !== ''): ?>
    <?php echo $labelHtml; ?>
<?php endif; ?>

<?php echo Html::openTag('div', $containerAttributes); ?>

<?php // Dropzone ?>
<?php echo Html::openTag('div', [
    'class' => $dropzoneClasses,
    'data-upload' => 'dropzone',
]); ?>
    <div>
        <?php echo Bleet::svg()->outline('arrow-up-tray')->addClass(...$dropzoneIconClasses); ?>
    </div>
    <div class="space-y-1">
        <?php echo Html::tag('span', Html::encode($dropzoneText), ['class' => $dropzoneTextClasses]); ?>
        <?php echo Html::openTag('span', ['class' => $hintClasses]); ?>
            <?php if ($hint !== null && $hint !== ''): ?>
                <?php echo Html::encode($hint); ?>
            <?php else: ?>
                ou
            <?php endif; ?>
            <?php echo Bleet::button('parcourir')
                ->color(Bleet::COLOR_SECONDARY)
                ->size(Bleet::SIZE_XS)
                ->disabled($disabled)
                ->attribute('data-upload', 'browse'); ?>
        <?php echo Html::closeTag('span'); ?>
    </div>
<?php echo Html::closeTag('div'); ?>

<?php // File list container ?>
<?php echo Html::tag('div', '', [
    'class' => 'mt-4 space-y-2',
    'data-upload' => 'list',
]); ?>

<?php // Hidden input ?>
<?php
$hiddenAttributes = [
    'type' => 'hidden',
    'data-upload' => 'value',
];
if ($name !== null) {
    $hiddenAttributes['name'] = $name;
}
if ($id !== null) {
    $hiddenAttributes['id'] = $id;
}
if ($value !== null) {
    $hiddenAttributes['value'] = $value;
}
if ($required) {
    $hiddenAttributes['required'] = true;
}
?>
<?php echo Html::input('hidden', $name, $value, $hiddenAttributes); ?>

<?php // Preview template ?>
<template data-upload="preview-template">
    <?php echo Html::openTag('div', ['class' => $previewItemClasses]); ?>
        <?php echo Html::openTag('a', [
            'data-upload' => 'preview-link',
            'href' => '#',
            'target' => '_blank',
            'class' => $previewLinkClasses,
        ]); ?>
            <?php echo Html::tag('img', '', [
                'data-upload' => 'preview-image',
                'src' => '',
                'alt' => '',
                'class' => $previewImageClasses,
            ]); ?>
            <?php echo Bleet::svg()->outline('document')->addClass(...$previewIconClasses)->attribute('data-upload', 'preview-icon'); ?>
        <?php echo Html::closeTag('a'); ?>
        <div class="flex-1 min-w-0">
            <?php echo Html::tag('div', '', [
                'data-upload' => 'preview-name',
                'class' => $previewNameClasses,
            ]); ?>
        </div>
        <?php echo Html::openTag('button', [
            'data-upload' => 'preview-remove',
            'type' => 'button',
            'class' => $previewRemoveClasses,
        ]); ?>
            <?php echo Bleet::svg()->outline('x-mark')->addClass('size-5'); ?>
        <?php echo Html::closeTag('button'); ?>
    <?php echo Html::closeTag('div'); ?>
</template>

<?php echo Html::closeTag('div'); ?>
