<?php

declare(strict_types=1);

/**
 * RenderViewTrait.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use RuntimeException;

/**
 * Trait pour le rendu de vues dans les widgets
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait RenderViewTrait
{
    /**
     * Renders a view with provided parameters
     *
     * @param string $viewName View name (without .php extension)
     * @param array $params Parameters to pass to the view
     * @return string Le contenu rendu
     * @throws RuntimeException Si le fichier de vue n'existe pas
     */
    protected function renderView(string $viewName, array $params = []): string
    {
        $viewFile = __DIR__ . '/Views/' . $viewName . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException("View file not found: {$viewFile}");
        }

        extract($params, EXTR_OVERWRITE);
        ob_start();
        include $viewFile;
        return ob_get_clean();
    }
}
