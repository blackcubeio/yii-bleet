<?php

declare(strict_types=1);

/**
 * pager.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var array $containerAttributes
 * @var array|null $info
 * @var array $pages
 * @var int $currentPage
 * @var int $totalPages
 * @var array|null $prevPage
 * @var array|null $nextPage
 * @var bool $showMobileSelect
 * @var array $infoContainerClasses
 * @var array $infoTextClasses
 * @var array $infoNumberClasses
 * @var array $navClasses
 * @var array $navInnerClasses
 * @var array $desktopContainerClasses
 * @var array $mobileContainerClasses
 * @var array $mobileSelectClasses
 * @var array $buttonClasses
 * @var array $buttonDisabledClasses
 * @var array $numberButtonClasses
 * @var array $numberButtonActiveClasses
 * @var array $svgClasses
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('div', $containerAttributes); ?>

<?php // Pagination info (desktop only) ?>
<?php if ($info !== null): ?>
    <?php echo Html::openTag('div', ['class' => $infoContainerClasses]); ?>
    <?php echo Html::openTag('p', ['class' => $infoTextClasses]); ?>
    Showing
    <?php echo Html::tag('span', (string) $info['begin'], ['class' => $infoNumberClasses]); ?>
    to
    <?php echo Html::tag('span', (string) $info['end'], ['class' => $infoNumberClasses]); ?>
    of
    <?php echo Html::tag('span', (string) $info['total'], ['class' => $infoNumberClasses]); ?>
    results
    <?php echo Html::closeTag('p'); ?>
    <?php echo Html::closeTag('div'); ?>
<?php endif; ?>

<?php // Navigation ?>
<?php echo Html::openTag('div', ['class' => $navClasses]); ?>
<?php echo Html::openTag('nav', ['class' => $navInnerClasses, 'aria-label' => 'Pagination']); ?>

<?php // Bouton Previous ?>
<?php if ($prevPage !== null): ?>
    <?php echo Html::openTag('a', ['href' => $prevPage['url'], 'class' => $buttonClasses]); ?>
    <?php echo Bleet::svg()->solid('chevron-left')->addClass(...$svgClasses); ?>
    <?php echo Html::tag('span', 'Previous', ['class' => 'sr-only']); ?>
    <?php echo Html::closeTag('a'); ?>
<?php else: ?>
    <?php echo Html::openTag('span', ['class' => $buttonDisabledClasses, 'aria-disabled' => 'true']); ?>
    <?php echo Bleet::svg()->solid('chevron-left')->addClass(...$svgClasses); ?>
    <?php echo Html::tag('span', 'Previous', ['class' => 'sr-only']); ?>
    <?php echo Html::closeTag('span'); ?>
<?php endif; ?>

<?php // Page numbers (desktop only) ?>
<?php echo Html::openTag('div', ['class' => $desktopContainerClasses]); ?>
<?php foreach ($pages as $page): ?>
    <?php if ($page['active']): ?>
        <?php echo Html::tag('a', (string) $page['number'], [
            'href' => $page['url'],
            'class' => $numberButtonActiveClasses,
            'aria-current' => 'page',
            'data-pager' => 'page-' . $page['number'],
        ]); ?>
    <?php else: ?>
        <?php echo Html::tag('a', (string) $page['number'], [
            'href' => $page['url'],
            'class' => $numberButtonClasses,
            'data-pager' => 'page-' . $page['number'],
        ]); ?>
    <?php endif; ?>
<?php endforeach; ?>
<?php echo Html::closeTag('div'); ?>

<?php // Select mobile ?>
<?php if ($showMobileSelect): ?>
    <?php echo Html::openTag('div', ['class' => $mobileContainerClasses]); ?>
        <?php echo Html::openTag('select', ['class' => $mobileSelectClasses, 'aria-label' => 'Select a page', 'data-pager' => 'select']); ?>
            <?php foreach ($pages as $page): ?>
            <?php echo Html::openTag('option', ['value' => $page['number'], 'selected' => $page['active']]); ?>
                <?php echo 'Page ' . $page['number']; ?>
            <?php echo Html::closeTag('option'); ?>
            <?php endforeach; ?>
        <?php echo Html::closeTag('select'); ?>
    <?php echo Html::closeTag('div'); ?>
<?php endif; ?>

<?php // Bouton Suivant ?>
<?php if ($nextPage !== null): ?>
    <?php echo Html::openTag('a', ['href' => $nextPage['url'], 'class' => $buttonClasses]); ?>
    <?php echo Html::tag('span', 'Suivant', ['class' => 'sr-only']); ?>
    <?php echo Bleet::svg()->solid('chevron-right')->addClass(...$svgClasses); ?>
    <?php echo Html::closeTag('a'); ?>
<?php else: ?>
    <?php echo Html::openTag('span', ['class' => $buttonDisabledClasses, 'aria-disabled' => 'true']); ?>
    <?php echo Html::tag('span', 'Suivant', ['class' => 'sr-only']); ?>
    <?php echo Bleet::svg()->solid('chevron-right')->addClass(...$svgClasses); ?>
    <?php echo Html::closeTag('span'); ?>
<?php endif; ?>

<?php echo Html::closeTag('nav'); ?>
<?php echo Html::closeTag('div'); ?>

<?php echo Html::closeTag('div'); ?>
