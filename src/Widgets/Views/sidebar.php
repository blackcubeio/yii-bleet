<?php

declare(strict_types=1);

/**
 * sidebar.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var string|Img|null $logo
 * @var bool $encodeLogo
 * @var SidebarItem[] $items
 * @var array $containerAttributes
 * @var string $color
 * @var array $asideClasses
 * @var array $headerMobileClasses
 * @var array $closeButtonClasses
 * @var array $headerDesktopClasses
 * @var array $logoClasses
 * @var array $navClasses
 * @var array $ulClasses
 * @var array $itemBaseClasses
 * @var array $itemActiveClasses
 * @var array $itemInactiveClasses
 * @var array $iconClasses
 * @var array $toggleButtonClasses
 * @var array $toggleIconClasses
 * @var array $sublistClasses
 * @var array $subItemClasses
 */

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Widgets\Img;
use Blackcube\Bleet\Widgets\SidebarItem;
use Yiisoft\Html\Html;

// Merge aside classes with container attributes
$asideAttributes = $containerAttributes;
$existingClass = $asideAttributes['class'] ?? '';
$asideAttributes['class'] = trim(implode(' ', $asideClasses) . ' ' . $existingClass);

?>
<?php echo Html::openTag('aside', $asideAttributes); ?>

    <!-- Header mobile avec logo et bouton fermer -->
    <?php echo Html::openTag('div', ['class' => $headerMobileClasses]); ?>
        <?php if ($logo !== null): ?>
            <?php if ($logo instanceof Img): ?>
                <?php echo $logo; ?>
            <?php elseif ($encodeLogo): ?>
                <?php echo Html::tag('div', Html::encode($logo), ['class' => $logoClasses]); ?>
            <?php else: ?>
                <?php echo $logo; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php echo Html::openTag('button', [
            'type' => 'button',
            'data-menu' => 'close',
            'class' => $closeButtonClasses,
        ]); ?>
            <span class="sr-only">Close menu</span>
            <?php echo Bleet::svg()->outline('x-mark')->addClass('size-6'); ?>
        <?php echo Html::closeTag('button'); ?>
    <?php echo Html::closeTag('div'); ?>

    <!-- Header desktop avec logo -->
    <?php if ($logo !== null): ?>
    <?php echo Html::openTag('div', ['class' => $headerDesktopClasses]); ?>
        <?php if ($logo instanceof Img): ?>
            <?php echo $logo; ?>
        <?php elseif ($encodeLogo): ?>
            <?php echo Html::tag('div', Html::encode($logo), ['class' => $logoClasses]); ?>
        <?php else: ?>
            <?php echo $logo; ?>
        <?php endif; ?>
    <?php echo Html::closeTag('div'); ?>
    <?php endif; ?>

    <!-- Navigation -->
    <?php echo Html::openTag('nav', ['class' => $navClasses]); ?>
        <?php echo Html::openTag('ul', ['class' => $ulClasses]); ?>
            <?php foreach ($items as $item): ?>
            <?php if ($item->hasChildren()): ?>
            <?php // Item with submenu ?>
            <?php $toggleId = $item->getToggleId() ?? uniqid('sidebar-'); ?>
            <li>
                <?php echo Html::openTag('button', [
                    'data-menu' => 'toggle-button-' . $toggleId,
                    'aria-expanded' => 'false',
                    'class' => $toggleButtonClasses,
                ]); ?>
                    <span class="flex items-center gap-3">
                        <?php if ($item->getIconName() !== null): ?>
                            <?php
                            $iconType = $item->getIconType();
                            if ($iconType === 'solid') {
                                echo Bleet::svg()->solid($item->getIconName())->addClass(...$iconClasses);
                            } else {
                                echo Bleet::svg()->outline($item->getIconName())->addClass(...$iconClasses);
                            }
                            ?>
                        <?php endif; ?>
                        <?php echo Html::encode($item->getLabel()); ?>
                    </span>
                    <?php echo Bleet::svg()->solid('chevron-down')->addClass(...$toggleIconClasses)->attribute('data-menu', 'icon'); ?>
                <?php echo Html::closeTag('button'); ?>
                <?php echo Html::openTag('ul', [
                    'data-menu' => 'toggle-list-' . $toggleId,
                    'class' => $sublistClasses,
                ]); ?>
                    <?php foreach ($item->getChildren() as $child): ?>
                    <?php
                    $childClasses = $child->isActive()
                        ? [...$subItemClasses, 'bg-primary-50', 'text-primary-700', 'font-semibold']
                        : $subItemClasses;
                    ?>
                    <li>
                        <?php echo Html::a(Html::encode($child->getLabel()), $child->getUrl() ?? '#', ['class' => $childClasses]); ?>
                    </li>
                    <?php endforeach; ?>
                <?php echo Html::closeTag('ul'); ?>
            </li>
            <?php else: ?>
            <?php // Item simple ?>
            <?php
            $itemClasses = $item->isActive()
                ? [...$itemBaseClasses, ...$itemActiveClasses]
                : [...$itemBaseClasses, ...$itemInactiveClasses];
            $href = $item->getUrl() ?? '#';
            ?>
            <li>
                <?php echo Html::openTag('a', ['href' => $href, 'class' => $itemClasses]); ?>
                    <?php if ($item->getIconName() !== null): ?>
                        <?php
                        $iconType = $item->getIconType();
                        if ($iconType === 'solid') {
                            echo Bleet::svg()->solid($item->getIconName())->addClass(...$iconClasses);
                        } else {
                            echo Bleet::svg()->outline($item->getIconName())->addClass(...$iconClasses);
                        }
                        ?>
                    <?php endif; ?>
                    <?php echo Html::encode($item->getLabel()); ?>
                <?php echo Html::closeTag('a'); ?>
            </li>
            <?php endif; ?>
            <?php endforeach; ?>
        <?php echo Html::closeTag('ul'); ?>
    <?php echo Html::closeTag('nav'); ?>

<?php echo Html::closeTag('aside'); ?>
