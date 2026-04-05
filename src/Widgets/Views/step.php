<?php

declare(strict_types=1);

/**
 * step.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var array $steps Steps data with status, label, url, number
 * @var array $navAttributes
 * @var array $listClasses
 * @var array $itemClasses
 * @var array $separatorClasses
 * @var array $separatorIconClasses
 * @var array $completedLinkClasses
 * @var array $completedContentClasses
 * @var array $completedBadgeClasses
 * @var array $completedIconClasses
 * @var array $completedLabelClasses
 * @var array $currentSpanClasses
 * @var array $currentBadgeClasses
 * @var array $currentNumberClasses
 * @var array $currentLabelClasses
 * @var array $upcomingSpanClasses
 * @var array $upcomingContentClasses
 * @var array $upcomingBadgeClasses
 * @var array $upcomingNumberClasses
 * @var array $upcomingLabelClasses
 */

use Blackcube\Bleet\Bleet;
use Yiisoft\Html\Html;

$totalSteps = count($steps);

?>
<?php echo Html::openTag('nav', $navAttributes); ?>

<?php echo Html::openTag('ol', ['role' => 'list', 'class' => $listClasses]); ?>

<?php foreach ($steps as $index => $step): ?>
    <?php
    $status = $step['status'];
    $isLast = ($index === $totalSteps - 1);
    ?>

    <?php echo Html::openTag('li', ['class' => $itemClasses]); ?>

    <?php if ($status === 'completed'): ?>
        <?php // Completed: lien cliquable ?>
        <?php echo Html::openTag('a', ['href' => $step['url'] ?? '#', 'class' => $completedLinkClasses]); ?>
            <?php echo Html::openTag('span', ['class' => $completedContentClasses]); ?>
                <?php echo Html::openTag('span', ['class' => $completedBadgeClasses]); ?>
                    <?php echo Bleet::svg()->solid('check')->addClass(...$completedIconClasses); ?>
                <?php echo Html::closeTag('span'); ?>
                <?php echo Html::tag('span', Html::encode($step['label']), ['class' => $completedLabelClasses]); ?>
            <?php echo Html::closeTag('span'); ?>
        <?php echo Html::closeTag('a'); ?>

    <?php elseif ($status === 'current'): ?>
        <?php // Current: span with aria-current ?>
        <?php echo Html::openTag('span', ['aria-current' => 'step', 'class' => $currentSpanClasses]); ?>
            <?php echo Html::openTag('span', ['class' => $currentBadgeClasses]); ?>
                <?php echo Html::tag('span', (string)$step['number'], ['class' => $currentNumberClasses]); ?>
            <?php echo Html::closeTag('span'); ?>
            <?php echo Html::tag('span', Html::encode($step['label']), ['class' => $currentLabelClasses]); ?>
        <?php echo Html::closeTag('span'); ?>

    <?php else: ?>
        <?php // Upcoming: disabled span ?>
        <?php echo Html::openTag('span', ['class' => $upcomingSpanClasses]); ?>
            <?php echo Html::openTag('span', ['class' => $upcomingContentClasses]); ?>
                <?php echo Html::openTag('span', ['class' => $upcomingBadgeClasses]); ?>
                    <?php echo Html::tag('span', (string)$step['number'], ['class' => $upcomingNumberClasses]); ?>
                <?php echo Html::closeTag('span'); ?>
                <?php echo Html::tag('span', Html::encode($step['label']), ['class' => $upcomingLabelClasses]); ?>
            <?php echo Html::closeTag('span'); ?>
        <?php echo Html::closeTag('span'); ?>
    <?php endif; ?>

    <?php if (!$isLast): ?>
        <?php // Chevron separator ?>
        <?php echo Html::openTag('div', ['aria-hidden' => 'true', 'class' => $separatorClasses]); ?>
            <?php echo Bleet::icon()->ui('navigation-separator')->addClass(...$separatorIconClasses)->attribute('preserveAspectRatio', 'none'); ?>
        <?php echo Html::closeTag('div'); ?>
    <?php endif; ?>

    <?php echo Html::closeTag('li'); ?>

<?php endforeach; ?>

<?php echo Html::closeTag('ol'); ?>

<?php echo Html::closeTag('nav'); ?>
