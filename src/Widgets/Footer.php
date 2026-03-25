<?php

declare(strict_types=1);

/**
 * Footer.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Yiisoft\Html\Html;

/**
 * Footer widget - Page footer with copyright and version
 *
 * Displays a footer at the bottom of the page with:
 * - Copyright text with current year
 * - Application version number
 * - Customizable color scheme
 *
 * Usage:
 *   Bleet::footer()
 *       ->version('4.0.0')
 *       ->copyright('Mon Entreprise')
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Footer extends AbstractWidget
{
    use BleetAttributesTrait;
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private ?string $version = null;
    private string $copyright = 'All rights reserved';

    /**
     * Sets la version de l'application
     */
    public function version(string $version): self
    {
        $new = clone $this;
        $new->version = $version;
        return $new;
    }

    /**
     * Sets le texte de copyright
     */
    public function copyright(string $text): self
    {
        $new = clone $this;
        $new->copyright = $text;
        return $new;
    }

    public function render(): string
    {
        return $this->renderView('footer', $this->prepareViewParams());
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    private function prepareViewParams(): array
    {
        // Container classes
        $containerClasses = ['bg-white', 'border-t', 'py-4', ...$this->getContainerBorderColorClasses()];

        $containerAttributes = $this->prepareTagAttributes();
        Html::addCssClass($containerAttributes, $containerClasses);

        return [
            'currentYear' => date('Y'),
            'version' => $this->version,
            'copyright' => $this->copyright,
            'containerAttributes' => $containerAttributes,
            'innerClasses' => ['max-w-screen-2xl', 'mx-auto', 'px-4', 'sm:px-6', 'lg:px-8'],
            'textClasses' => ['text-center', 'text-sm', ...$this->getTextColorClasses()],
        ];
    }

    /**
     * @return string[]
     */
    private function getContainerBorderColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['border-primary-200'],
            Bleet::COLOR_SECONDARY => ['border-secondary-200'],
            Bleet::COLOR_SUCCESS => ['border-success-200'],
            Bleet::COLOR_DANGER => ['border-danger-200'],
            Bleet::COLOR_WARNING => ['border-warning-200'],
            Bleet::COLOR_INFO => ['border-info-200'],
            Bleet::COLOR_ACCENT => ['border-accent-200'],
        };
    }

    /**
     * @return string[]
     */
    private function getTextColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700'],
            Bleet::COLOR_SUCCESS => ['text-success-700'],
            Bleet::COLOR_DANGER => ['text-danger-700'],
            Bleet::COLOR_WARNING => ['text-warning-700'],
            Bleet::COLOR_INFO => ['text-info-700'],
            Bleet::COLOR_ACCENT => ['text-accent-700'],
        };
    }
}
