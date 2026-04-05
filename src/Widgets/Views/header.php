<?php

declare(strict_types=1);

/**
 * header.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var string $title
 * @var bool $showBurgerMenu
 * @var bool $hasExtras
 * @var string|null $searchAction
 * @var string $searchPlaceholder
 * @var array<Anchor|Button> $actions
 * @var array<string|null> $widgets
 * @var Profile|null $profile
 * @var array $containerAttributes
 * @var string $color
 * @var array $innerClasses
 * @var array $leftSectionClasses
 * @var array $burgerButtonClasses
 * @var array $burgerButtonAttributes
 * @var array $burgerIconClasses
 * @var array $titleClasses
 * @var array $separatorClasses
 * @var array $searchInputClasses
 * @var array $searchIconClasses
 * @var array $actionButtonClasses
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Widgets\Anchor;
use Blackcube\Bleet\Widgets\Button;
use Blackcube\Bleet\Widgets\Profile;
use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('header', $containerAttributes); ?>

<?php if ($hasExtras): ?>
    <!-- Layout with search/actions/profile -->

    <?php if ($showBurgerMenu): ?>
        <!-- Burger menu button (mobile) -->
        <?php echo Html::openTag('button', array_merge(['class' => $burgerButtonClasses], $burgerButtonAttributes)); ?>
            <span class="sr-only">Open menu</span>
            <?php echo Bleet::svg()->outline('bars-3')->addClass(...$burgerIconClasses); ?>
        <?php echo Html::closeTag('button'); ?>

        <!-- Separator -->
        <?php echo Html::tag('div', '', ['aria-hidden' => 'true', 'class' => [...$separatorClasses, 'lg:hidden']]); ?>
    <?php endif; ?>

    <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
        <?php if ($searchAction !== null): ?>
        <!-- Search form -->
        <?php echo Html::form()->get($searchAction)->attribute('class', 'grid flex-1 grid-cols-1')->open(); ?>
            <?php echo Html::input('search', 'search', '', [
                'placeholder' => $searchPlaceholder,
                'aria-label' => $searchPlaceholder,
                'class' => $searchInputClasses,
            ]); ?>
            <?php echo Bleet::svg()->solid('magnifying-glass')->addClass(...$searchIconClasses); ?>
        <?php echo Html::closeTag('form'); ?>
        <?php else: ?>
        <div class="flex-1"></div>
        <?php endif; ?>

        <div class="flex items-center gap-x-4 lg:gap-x-6">
            <?php foreach ($widgets as $widget): ?>
                <?php if ($widget === null): ?>
                    <?php echo Html::tag('div', '', ['aria-hidden' => 'true', 'class' => [...$separatorClasses, 'hidden', 'lg:block']]); ?>
                <?php else: ?>
                    <?php echo $widget; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php foreach ($actions as $action): ?>
                <?php echo $action->addClass(...$actionButtonClasses); ?>
            <?php endforeach; ?>

            <?php if ($profile !== null && !empty($actions)): ?>
                <!-- Separator -->
                <?php echo Html::tag('div', '', ['aria-hidden' => 'true', 'class' => [...$separatorClasses, 'hidden', 'lg:block']]); ?>
            <?php endif; ?>

            <?php if ($profile !== null): ?>
                <!-- Profile dropdown -->
                <?php echo $profile; ?>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- Simple layout (title only) -->

    <?php echo Html::openTag('div', ['class' => $innerClasses]); ?>

        <?php echo Html::openTag('div', ['class' => $leftSectionClasses]); ?>

            <?php if ($showBurgerMenu): ?>
                <?php echo Html::openTag('button', array_merge(['class' => $burgerButtonClasses], $burgerButtonAttributes)); ?>
                    <?php echo Bleet::svg()->outline('bars-3')->addClass(...$burgerIconClasses); ?>
                <?php echo Html::closeTag('button'); ?>
            <?php endif; ?>

            <?php echo Html::tag('h1', Html::encode($title), ['class' => $titleClasses]); ?>

        <?php echo Html::closeTag('div'); ?>

    <?php echo Html::closeTag('div'); ?>

<?php endif; ?>

<?php echo Html::closeTag('header'); ?>
