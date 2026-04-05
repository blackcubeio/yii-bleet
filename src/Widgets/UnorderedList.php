<?php

declare(strict_types=1);

/**
 * UnorderedList.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Yiisoft\Html\Html;
use Yiisoft\Html\Tag\Li;
use Yiisoft\Html\Tag\Ul;

/**
 * UnorderedList widget
 *
 * Usage:
 *   Bleet::ul(['Item 1', 'Item 2'])->render();
 *   Bleet::ul(['Item 1', 'Item 2'])->sm()->render();
 *   Bleet::ul(['Item 1', 'Item 2'])->primary()->render();
 *   Bleet::ul([
 *       'Item simple',
 *       ['With icon', ['solid' => 'check-circle']],
 *       ['Override couleur', ['solid' => 'x-circle', 'color' => 'danger']],
 *   ])->success()->render();
 *   Bleet::ul()
 *       ->addItem(Bleet::listItem('Texte')->solid('check-circle'))
 *       ->success()
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class UnorderedList extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_SECONDARY;

    /** @var array<string|array|ListItem> */
    private array $items = [];
    private bool $encode = true;

    /**
     * @param array<string|array|ListItem> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Sets list elements
     * @param array<string|array|ListItem> $items
     */
    public function items(array $items): self
    {
        $new = clone $this;
        $new->items = $items;
        return $new;
    }

    /**
     * Adds an item to the list
     */
    public function addItem(string|array|ListItem $item): self
    {
        $new = clone $this;
        $new->items[] = $item;
        return $new;
    }

    /**
     * Disables HTML encoding of elements
     */
    public function encode(bool $encode = true): self
    {
        $new = clone $this;
        $new->encode = $encode;
        return $new;
    }

    public function render(): string
    {
        $hasIconItems = $this->hasIconItems();
        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses($hasIconItems));

        $liItems = [];
        foreach ($this->items as $item) {
            $liItems[] = $this->renderItem($item);
        }

        return Html::tag('ul', implode("\n", $liItems), $attributes)
            ->encode(false)
            ->render();
    }

    /**
     * Checks if at least one item has an icon
     */
    private function hasIconItems(): bool
    {
        foreach ($this->items as $item) {
            if ($item instanceof ListItem && $item->hasIcon()) {
                return true;
            }
            if (is_array($item) && isset($item[1]) && is_array($item[1])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Rend un item de liste
     */
    private function renderItem(string|array|ListItem $item): string
    {
        if ($item instanceof ListItem) {
            return $this->renderListItem($item);
        }

        if (is_array($item)) {
            return $this->renderArrayItem($item);
        }

        return $this->renderStringItem($item);
    }

    /**
     * Rend un item string simple
     */
    private function renderStringItem(string $item): string
    {
        $content = $this->encode ? htmlspecialchars($item, ENT_QUOTES | ENT_HTML5) : $item;
        return Li::tag()->content($content)->encode(false)->render();
    }

    /**
     * Rend un item array ['texte', ['solid' => 'icon', 'color' => 'success']]
     */
    private function renderArrayItem(array $item): string
    {
        $text = $item[0] ?? '';
        $config = $item[1] ?? [];

        $listItem = new ListItem($text);

        if (!$this->encode) {
            $listItem = $listItem->encode(false);
        }

        // Determine the type and name of the icon
        foreach (['solid', 'outline', 'mini', 'micro'] as $type) {
            if (isset($config[$type])) {
                $listItem = $listItem->$type($config[$type]);
                break;
            }
        }

        // Couleur optionnelle
        if (isset($config['color'])) {
            $listItem = $listItem->color($config['color']);
        }

        return $this->renderListItem($listItem);
    }

    /**
     * Rend un ListItem
     */
    private function renderListItem(ListItem $item): string
    {
        $content = $item->renderContent($this->color);

        if ($item->hasIcon()) {
            return Li::tag()
                ->class('flex', 'gap-3')
                ->content($content)
                ->encode(false)
                ->render();
        }

        return Li::tag()->content($content)->encode(false)->render();
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(bool $hasIconItems = false): array
    {
        if ($hasIconItems) {
            $baseClasses = [
                'space-y-3',
            ];
        } else {
            $baseClasses = [
                'list-disc',
                'list-inside',
                'space-y-2',
            ];
        }

        return [...$baseClasses, ...$this->getSizeClasses(), ...$this->getColorClasses()];
    }

    /**
     * @return string[]
     */
    private function getSizeClasses(): array
    {
        return match ($this->size) {
            Bleet::SIZE_XS => ['text-xs'],
            Bleet::SIZE_SM => ['text-sm'],
            Bleet::SIZE_MD => ['text-base'],
            Bleet::SIZE_LG => ['text-lg'],
            Bleet::SIZE_XL => ['text-xl'],
        };
    }

    /**
     * @return string[]
     */
    private function getColorClasses(): array
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
