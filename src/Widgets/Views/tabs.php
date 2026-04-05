<?php

declare(strict_types=1);

/**
 * tabs.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var TabPanel[] $panels
 * @var array $containerAttributes
 * @var array $mobileContainerClasses
 * @var array $mobileSelectClasses
 * @var array $desktopContainerClasses
 * @var array $desktopBorderClasses
 * @var array $desktopNavClasses
 * @var array $tabBaseClasses
 * @var array $tabInactiveClasses
 * @var array $tabActiveClasses
 * @var array $badgeClasses
 * @var array $panelsContainerClasses
 */

use Blackcube\Bleet\Widgets\TabPanel;
use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('div', $containerAttributes); ?>

    <!-- Mobile select -->
    <?php echo Html::openTag('div', ['class' => $mobileContainerClasses]); ?>
        <?php echo Html::openTag('select', ['class' => $mobileSelectClasses, 'aria-label' => 'Select a tab', 'data-tabs' => 'select']); ?>
            <?php foreach ($panels as $index => $panel): ?>
            <?php echo Html::openTag('option', ['value' => $index, 'selected' => $panel->isActive()]); ?>
                <?php echo Html::encode($panel->getLabel()); ?>
            <?php echo Html::closeTag('option'); ?>
            <?php endforeach; ?>
        <?php echo Html::closeTag('select'); ?>
    <?php echo Html::closeTag('div'); ?>

    <!-- Desktop nav -->
    <?php echo Html::openTag('div', ['class' => $desktopContainerClasses]); ?>
        <?php echo Html::openTag('div', ['class' => $desktopBorderClasses]); ?>
            <?php echo Html::openTag('nav', ['class' => $desktopNavClasses, 'aria-label' => 'Tabs']); ?>
                <?php foreach ($panels as $index => $panel): ?>
                <?php
                $tabClasses = $panel->isActive()
                    ? [...$tabBaseClasses, ...$tabActiveClasses]
                    : [...$tabBaseClasses, ...$tabInactiveClasses];
                $tabAttributes = [
                    'type' => 'button',
                    'role' => 'tab',
                    'class' => $tabClasses,
                    'data-tabs' => 'tab-' . $index,
                    'aria-selected' => $panel->isActive() ? 'true' : 'false',
                ];
                if ($panel->isActive()) {
                    $tabAttributes['aria-current'] = 'page';
                }
                ?>
                <?php echo Html::openTag('button', $tabAttributes); ?>
                    <?php echo Html::encode($panel->getLabel()); ?>
                    <?php if ($panel->getBadge() !== null): ?>
                    <?php echo Html::tag('span', Html::encode($panel->getBadge()), ['class' => $badgeClasses]); ?>
                    <?php endif; ?>
                <?php echo Html::closeTag('button'); ?>
                <?php endforeach; ?>
            <?php echo Html::closeTag('nav'); ?>
        <?php echo Html::closeTag('div'); ?>
    <?php echo Html::closeTag('div'); ?>

    <!-- Panels container -->
    <?php echo Html::openTag('div', ['class' => $panelsContainerClasses]); ?>
        <?php foreach ($panels as $index => $panel): ?>
        <?php
        $panelAttributes = [
            'role' => 'tabpanel',
            'class' => $panel->isActive() ? '' : 'hidden',
            'data-tabs' => 'panel-' . $index,
            'aria-hidden' => $panel->isActive() ? 'false' : 'true',
        ];
        ?>
        <?php echo Html::openTag('div', $panelAttributes); ?>
            <?php echo $panel->getContent(); ?>
        <?php echo Html::closeTag('div'); ?>
        <?php endforeach; ?>
    <?php echo Html::closeTag('div'); ?>

<?php echo Html::closeTag('div'); ?>
