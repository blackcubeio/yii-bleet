<?php

declare(strict_types=1);

/**
 * footer.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 *
 * @var string $currentYear
 * @var string|null $version
 * @var string $copyright
 * @var array $containerAttributes
 * @var array $innerClasses
 * @var array $textClasses
 */

use Yiisoft\Html\Html;

?>
<?php echo Html::openTag('footer', $containerAttributes); ?>

    <?php echo Html::openTag('div', ['class' => $innerClasses]); ?>

        <?php echo Html::openTag('div', ['class' => $textClasses]); ?>

            <p>&copy; <?php echo $currentYear; ?> - <?php echo Html::encode($copyright); ?><?php if ($version !== null): ?> | v<?php echo Html::encode($version); ?><?php endif; ?></p>

        <?php echo Html::closeTag('div'); ?>

    <?php echo Html::closeTag('div'); ?>

<?php echo Html::closeTag('footer'); ?>
