<?php

declare(strict_types=1);

/**
 * h1.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var string $title
 * @var string|null $subtitle
 * @var array|null $primaryCta
 * @var array|null $secondaryCta
 * @var array $containerAttributes
 * @var array $contentClasses
 * @var array $titleClasses
 * @var array $subtitleClasses
 * @var array $ctaContainerClasses
 * @var array $primaryCtaClasses
 * @var array $secondaryCtaClasses
 */

use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('div', $containerAttributes); ?>
    <?php echo Html::openTag('div', ['class' => $contentClasses]); ?>
        <?php if (!empty($title)): ?>
        <?php echo Html::tag('h1', Html::encode($title), ['class' => $titleClasses]); ?>
        <?php endif; ?>

        <?php if (!empty($subtitle)): ?>
        <?php echo Html::tag('p', Html::encode($subtitle), ['class' => $subtitleClasses]); ?>
        <?php endif; ?>

        <?php if (!empty($primaryCta) || !empty($secondaryCta)): ?>
        <?php echo Html::openTag('div', ['class' => $ctaContainerClasses]); ?>
            <?php if (!empty($primaryCta)): ?>
                <?php if (!empty($primaryCta['url'])): ?>
                <?php echo Html::tag('a', Html::encode($primaryCta['label']), ['href' => $primaryCta['url'], 'class' => $primaryCtaClasses]); ?>
                <?php else: ?>
                <?php echo Html::tag('button', Html::encode($primaryCta['label']), ['type' => 'button', 'class' => $primaryCtaClasses]); ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!empty($secondaryCta)): ?>
                <?php if (!empty($secondaryCta['url'])): ?>
                <?php echo Html::tag('a', Html::encode($secondaryCta['label']), ['href' => $secondaryCta['url'], 'class' => $secondaryCtaClasses]); ?>
                <?php else: ?>
                <?php echo Html::tag('button', Html::encode($secondaryCta['label']), ['type' => 'button', 'class' => $secondaryCtaClasses]); ?>
                <?php endif; ?>
            <?php endif; ?>
        <?php echo Html::closeTag('div'); ?>
        <?php endif; ?>
    <?php echo Html::closeTag('div'); ?>
<?php echo Html::closeTag('div'); ?>
