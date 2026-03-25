<?php

declare(strict_types=1);

/**
 * Toast.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Enums\UiIcon;
use Blackcube\Bleet\Traits\BleetExportableTrait;
use Yiisoft\Html\Html;

/**
 * Toast widget - Trigger toast notifications
 *
 * Usage - Toast on page load:
 *   <?= Bleet::toast()->success()->title('Succes')->content('Action reussie')->render() ?>
 *
 * Usage - Toast trigger attribute on button:
 *   <?= Bleet::button('Save')->attributes(Bleet::toast()->success()->content('Saved!')->trigger()) ?>
 *
 * Icons are auto-selected based on color:
 * - success -> check-circle
 * - danger -> x-circle
 * - warning -> exclamation-triangle
 * - info/primary/secondary -> information-circle
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Toast extends AbstractWidget
{
    use BleetExportableTrait;

    protected string $color = Bleet::COLOR_INFO;

    private ?string $id = null;
    private ?string $title = null;
    private string $content = '';
    private int $duration = 0;
    private ?string $icon = null;

    /**
     * Definit l'ID du toast
     */
    public function id(string $id): self
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    /**
     * Definit the title du toast
     */
    public function title(string $title): self
    {
        $new = clone $this;
        $new->title = $title;
        return $new;
    }

    /**
     * Definit the content du toast
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Definit la duree avant auto-fermeture (en ms, 0 = pas d'auto-close)
     */
    public function duration(int $duration): self
    {
        $new = clone $this;
        $new->duration = $duration;
        return $new;
    }

    /**
     * Definit l'icone manuellement (override auto-selection)
     */
    public function icon(UiIcon|string $icon): self
    {
        $new = clone $this;
        $new->icon = $icon instanceof UiIcon ? $icon->value : $icon;
        return $new;
    }

    /**
     * Export for EA, AJAX, attributs
     * @return array<string, mixed>
     */
    public function asArray(): array
    {
        $data = [
            'color' => $this->color,
            'content' => $this->content,
            'icon' => $this->icon ?? $this->getIconForColor(),
        ];

        if ($this->id !== null) {
            $data['id'] = $this->id;
        }
        if ($this->title !== null) {
            $data['title'] = $this->title;
        }
        if ($this->duration > 0) {
            $data['duration'] = $this->duration;
        }

        return $data;
    }

    /**
     * Attributs pour declencher un toast sur click
     * @return array<string, string>
     */
    public function trigger(): array
    {
        return ['bleet-toaster-trigger' => Aurelia::attributesCustomAttribute($this->asArray())];
    }

    /**
     * Render invisible component (triggers on page load)
     */
    public function render(): string
    {
        return Html::tag('bleet-toast', '', Aurelia::attributesCustomElement($this->asArray()))->render();
    }

    /**
     * @deprecated Use trigger() instead
     * @return array<string, string>
     */
    public function toAttribute(): array
    {
        return $this->trigger();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    private function getIconForColor(): string
    {
        return match ($this->color) {
            Bleet::COLOR_SUCCESS => UiIcon::Success->value,
            Bleet::COLOR_DANGER => UiIcon::Danger->value,
            Bleet::COLOR_WARNING => UiIcon::Warning->value,
            default => UiIcon::Info->value,
        };
    }
}
