<?php

declare(strict_types=1);

/**
 * breadcrumb.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var string|null $homeUrl
 * @var string $homeLabel
 * @var string $homeIcon
 * @var array $items
 * @var bool $showBorder
 * @var array $navAttributes
 * @var array $olClasses
 * @var array $homeItemClasses
 * @var array $homeWrapperClasses
 * @var array|null $homeLinkClasses
 * @var array|null $homeSpanClasses
 * @var array $homeIconClasses
 * @var array $itemClasses
 * @var array $itemWrapperClasses
 * @var array $separatorAttributes
 * @var array $itemLinkClasses
 * @var array $lastItemClasses
 * @var array $disabledItemClasses
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('nav', $navAttributes); ?>

<?php echo Html::openTag('ol', ['role' => 'list', 'class' => $olClasses]); ?>

<?php if ($homeUrl !== null || $homeLabel !== ''): ?>

    <?php echo Html::openTag('li', ['class' => $homeItemClasses]); ?>

    <?php echo Html::openTag('div', ['class' => $homeWrapperClasses]); ?>

    <?php if ($homeLinkClasses !== null): ?>
        <?php echo Html::openTag('a', ['href' => $homeUrl, 'class' => $homeLinkClasses]); ?>
    <?php else: ?>
        <?php echo Html::openTag('span', ['class' => $homeSpanClasses ?? []]); ?>
    <?php endif; ?>

    <?php echo Bleet::svg()->solid($homeIcon)->addClass(...$homeIconClasses); ?>

    <?php echo Html::tag('span', Html::encode($homeLabel), ['class' => 'sr-only']); ?>

    <?php if ($homeLinkClasses !== null): ?>
        <?php echo Html::closeTag('a'); ?>
    <?php else: ?>
        <?php echo Html::closeTag('span'); ?>
    <?php endif; ?>

    <?php echo Html::closeTag('div'); ?>

    <?php echo Html::closeTag('li'); ?>

<?php endif; ?>

<?php $totalItems = count($items); ?>
<?php foreach ($items as $index => $item): ?>
    <?php $isLast = ($index === $totalItems - 1); ?>
    <?php $hasUrl = !empty($item['url']); ?>

    <?php echo Html::openTag('li', ['class' => $itemClasses]); ?>

    <?php echo Html::openTag('div', ['class' => $itemWrapperClasses]); ?>

    <?php if ($showBorder): ?>
        <?php echo Bleet::icon()->ui('navigation-separator')->addClass($separatorAttributes['class'])->attribute('preserveAspectRatio', 'none'); ?>
    <?php else: ?>
        <?php echo Bleet::svg()->solid('chevron-right')->addClass($separatorAttributes['class']); ?>
    <?php endif; ?>

    <?php if ($hasUrl): ?>
        <?php
        $linkAttributes = ['href' => $item['url'], 'class' => $itemLinkClasses];
        if ($isLast) {
            $linkAttributes['aria-current'] = 'page';
        }
        ?>
        <?php echo Html::openTag('a', $linkAttributes); ?>
        <?php echo Html::encode($item['label']); ?>
        <?php echo Html::closeTag('a'); ?>
    <?php else: ?>
        <?php if ($isLast): ?>
            <?php echo Html::tag('span', Html::encode($item['label']), ['class' => $lastItemClasses]); ?>
        <?php else: ?>
            <?php echo Html::tag('span', Html::encode($item['label']), ['class' => $disabledItemClasses]); ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php echo Html::closeTag('div'); ?>

    <?php echo Html::closeTag('li'); ?>

<?php endforeach; ?>

<?php echo Html::closeTag('ol'); ?>

<?php echo Html::closeTag('nav'); ?>
