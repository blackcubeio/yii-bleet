<?php

declare(strict_types=1);

/**
 * profile.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var string|null $initials
 * @var Img|null $avatarImg
 * @var string|null $name
 * @var array<Anchor|Button> $items
 * @var array $containerAttributes
 * @var string $color
 * @var array $buttonClasses
 * @var array $avatarInitialsClasses
 * @var array $avatarImageClasses
 * @var array $initialsTextClasses
 * @var array $nameContainerClasses
 * @var array $nameClasses
 * @var array $chevronClasses
 * @var array $dropdownClasses
 * @var array $itemClasses
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Widgets\Anchor;
use Blackcube\Bleet\Widgets\Button;
use Blackcube\Bleet\Widgets\Img;
use Yiisoft\Html\Html;

// Container attributes
$containerAttrs = $containerAttributes;
$containerAttrs['class'] = trim(($containerAttrs['class'] ?? '') . ' relative');
$containerAttrs['bleet-profile'] = '';

?>
<?php echo Html::openTag('div', $containerAttrs); ?>

    <!-- Profile button -->
    <?php echo Html::openTag('button', [
        'type' => 'button',
        'data-profile' => 'toggle',
        'class' => $buttonClasses,
        'aria-haspopup' => 'menu',
        'aria-expanded' => 'false',
    ]); ?>
        <span class="absolute -inset-1.5"></span>
        <span class="sr-only">Open menu utilisateur</span>

        <?php if ($avatarImg !== null): ?>
            <!-- Avatar image -->
            <?php echo $avatarImg->addClass(...$avatarImageClasses); ?>
        <?php elseif ($initials !== null): ?>
            <!-- Avatar initials -->
            <?php echo Html::openTag('span', ['class' => $avatarInitialsClasses]); ?>
                <?php echo Html::tag('span', Html::encode($initials), ['class' => $initialsTextClasses]); ?>
            <?php echo Html::closeTag('span'); ?>
        <?php endif; ?>

        <?php if ($name !== null): ?>
            <!-- Name (desktop only) -->
            <?php echo Html::openTag('span', ['class' => $nameContainerClasses]); ?>
                <?php echo Html::tag('span', Html::encode($name), ['aria-hidden' => 'true', 'class' => $nameClasses]); ?>
                <?php echo Bleet::svg()->solid('chevron-down')->addClass(...$chevronClasses); ?>
            <?php echo Html::closeTag('span'); ?>
        <?php endif; ?>
    <?php echo Html::closeTag('button'); ?>

    <?php if (!empty($items)): ?>
    <!-- Dropdown menu -->
    <?php echo Html::openTag('div', ['data-profile' => 'panel', 'class' => $dropdownClasses]); ?>
        <?php foreach ($items as $item): ?>
            <?php echo $item->renderAsMenuItem(...$itemClasses); ?>
        <?php endforeach; ?>
    <?php echo Html::closeTag('div'); ?>
    <?php endif; ?>

<?php echo Html::closeTag('div'); ?>
