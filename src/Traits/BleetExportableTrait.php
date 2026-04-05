<?php

declare(strict_types=1);

/**
 * BleetExportableTrait.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

/**
 * Trait pour les widgets exportables en array
 * Utilise pour : attributs trigger, messages EA, reponses AJAX
 */
trait BleetExportableTrait
{
    /**
     * Returns les donnees du widget en array
     * @return array<string, mixed>
     */
    abstract public function asArray(): array;
}
