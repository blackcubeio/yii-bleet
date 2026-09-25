<?php

declare(strict_types=1);

/**
 * DescriptionList.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Blackcube\Bleet\Traits\BleetAttributesTrait;
use Yiisoft\Html\Html;

/**
 * DescriptionList widget (dl element)
 *
 * Usage:
 *   // Simple avec array
 *   Bleet::dl([
 *       ['Nom' => 'Jean Dupont'],
 *       ['Email' => 'jean@example.com'],
 *   ])->render();
 *
 *   // Fluent
 *   Bleet::dl()
 *       ->addItem(Bleet::termItem('Nom')->detail('Jean Dupont'))
 *       ->addItem(Bleet::termItem('Email')->detail('jean@example.com'))
 *       ->primary()
 *       ->render();
 *
 *   // Avec colonnes
 *   Bleet::dl()
 *       ->cols(2)
 *       ->addItem(Bleet::termItem('Nom')->detail('Jean Dupont'))
 *       ->render();
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class DescriptionList extends AbstractWidget
{
    use BleetAttributesTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    /** @var array<TermItem|array> */
    private array $items = [];
    private int $cols = 0;
    private string|array|null $colTemplateClass = null;
    private ?bool $tableMode = null;

    /**
     * @param array<TermItem|array> $items
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Sets list elements
     * @param array<TermItem|array> $items
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
    public function addItem(TermItem|array $item): self
    {
        $new = clone $this;
        $new->items[] = $item;
        return $new;
    }

    /**
     * Sets number of columns (0 = stacked, 1 = flex, 2+ = grid)
     */
    public function cols(int $cols): self
    {
        $new = clone $this;
        $new->cols = $cols;
        return $new;
    }

    /**
     * Sets custom Tailwind grid-cols class(es) replacing the default from cols().
     *
     * @param string|string[] $class e.g. 'md:grid-cols-[3fr_1fr_1fr]' or ['md:grid-cols-[3fr_1fr_1fr]', 'lg:grid-cols-[4fr_1fr_1fr]']
     */
    public function colTemplateClass(string|array $class): self
    {
        $new = clone $this;
        $new->colTemplateClass = $class;
        return $new;
    }

    /**
     * Enables table mode: terms in header (first row), details in data rows
     */
    public function tableMode(bool $tableMode = true): self
    {
        $new = clone $this;
        $new->tableMode = $tableMode;
        return $new;
    }

    public function render(): string
    {
        $attributes = $this->prepareTagAttributes();
        Html::addCssClass($attributes, $this->prepareClasses());

        $useTableMode = $this->tableMode ?? ($this->cols >= 2);
        $html = $useTableMode ? $this->renderTableMode() : $this->renderNormalMode();

        return Html::tag('dl', $html, $attributes)
            ->encode(false)
            ->render();
    }

    /**
     * Normal rendering: each item with its term and details
     */
    private function renderNormalMode(): string
    {
        $html = '';
        foreach ($this->items as $item) {
            $html .= $this->renderItem($item);
        }
        return $html;
    }

    /**
     * Table mode rendering: first row = terms (headers), following rows = details only
     */
    private function renderTableMode(): string
    {
        $termItems = [];
        foreach ($this->items as $item) {
            if ($item instanceof TermItem) {
                $termItems[] = $item;
            } else {
                foreach ($item as $term => $definitions) {
                    $termItem = new TermItem((string) $term);
                    if (is_array($definitions) === false) {
                        $definitions = [$definitions];
                    }
                    foreach ($definitions as $definition) {
                        if ($definition instanceof DetailItem) {
                            $termItem = $termItem->addDetail($definition);
                        } else {
                            $termItem = $termItem->detail((string) $definition);
                        }
                    }
                    $termItems[] = $termItem;
                }
            }
        }

        if (empty($termItems) === true) {
            return '';
        }

        $cols = max(1, $this->cols);
        $rows = array_chunk($termItems, $cols);

        $html = '';
        $dtClasses = $this->getDtClasses();
        $ddClasses = $this->getDdClasses();

        $containerClasses = $this->getContainerClasses();

        $isArbo = $this->hasArboItems();
        if ($isArbo) {
            $containerClasses[] = 'py-2';
        }

        foreach ($rows as $rowIndex => $rowItems) {
            $isFirstRow = ($rowIndex === 0);

            $firstItem = $rowItems[0] ?? null;
            $rowAttributes = $firstItem instanceof TermItem ? $firstItem->getRowAttributes() : [];
            $hasRowWrapper = empty($rowAttributes) === false;

            $rowContent = '';

            foreach ($rowItems as $termItem) {
                $containerHtml = '';

                $dtDisplayClasses = $isFirstRow ? $dtClasses : [...$dtClasses, 'md:hidden'];
                $containerHtml .= Html::tag('dt', $termItem->renderTerm(), ['class' => $dtDisplayClasses])
                    ->encode(false)
                    ->render();

                foreach ($termItem->getDetails() as $detail) {
                    $containerHtml .= Html::tag('dd', $detail->renderContent(), ['class' => $ddClasses])
                        ->encode(false)
                        ->render();
                }

                $content = Html::tag('div', $containerHtml, ['class' => $containerClasses])
                    ->encode(false)
                    ->render();

                $level = $termItem->getLevel();
                if ($level > 1) {
                    $content = $this->wrapWithLevelBars($content, $level);
                }

                $rowContent .= $content;
            }

            if ($hasRowWrapper) {
                $rowWrapperClasses = $this->getRowWrapperClasses();
                $rowAttributes['class'] = isset($rowAttributes['class']) === true
                    ? array_merge((array) $rowAttributes['class'], $rowWrapperClasses)
                    : $rowWrapperClasses;
                $html .= Html::tag('div', $rowContent, $rowAttributes)
                    ->encode(false)
                    ->render();
            } else {
                $html .= $rowContent;
            }
        }

        return $html;
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        $isArbo = $this->hasArboItems();

        return match ($this->cols) {
            0 => ['flex', 'flex-col', $isArbo ? 'gap-x-4' : 'gap-4'],
            1 => ['flex', 'flex-col', $isArbo ? 'gap-x-3' : 'gap-3'],
            default => ['grid', 'grid-cols-1', ...$this->getGridColsClasses(), $isArbo ? 'gap-x-4' : 'gap-4'],
        };
    }

    /**
     * @return string[]
     */
    private function getGridColsClasses(): array
    {
        if ($this->colTemplateClass !== null) {
            return (array) $this->colTemplateClass;
        }
        return match ($this->cols) {
            2 => ['md:grid-cols-2'],
            3 => ['md:grid-cols-3'],
            4 => ['md:grid-cols-4'],
            5 => ['md:grid-cols-5'],
            6 => ['md:grid-cols-6'],
            default => ['md:grid-cols-2'],
        };
    }

    /**
     * Renders one item of the list
     */
    private function renderItem(TermItem|array $item): string
    {
        if ($item instanceof TermItem) {
            return $this->renderTermItem($item);
        }

        return $this->renderArrayItem($item);
    }

    /**
     * Renders an array item ['term' => 'detail'] or ['term' => ['detail1', 'detail2']]
     */
    private function renderArrayItem(array $item): string
    {
        $html = '';

        foreach ($item as $term => $definitions) {
            $termItem = new TermItem((string) $term);

            if (is_array($definitions) === false) {
                $definitions = [$definitions];
            }

            foreach ($definitions as $definition) {
                if ($definition instanceof DetailItem) {
                    $termItem = $termItem->addDetail($definition);
                } else {
                    $termItem = $termItem->detail((string) $definition);
                }
            }

            $html .= $this->renderTermItem($termItem);
        }

        return $html;
    }

    /**
     * Renders a complete TermItem (container + dt + dd)
     */
    private function renderTermItem(TermItem $item): string
    {
        $dtClasses = $this->getDtClasses();
        $ddClasses = $this->getDdClasses();
        $containerClasses = $this->getContainerClasses();

        $isArbo = $this->hasArboItems();
        if ($isArbo) {
            $containerClasses[] = 'py-2';
        }

        $dtHtml = Html::tag('dt', $item->renderTerm(), ['class' => $dtClasses])
            ->encode(false)
            ->render();

        $ddHtml = '';
        foreach ($item->getDetails() as $detail) {
            $ddHtml .= Html::tag('dd', $detail->renderContent(), ['class' => $ddClasses])
                ->encode(false)
                ->render();
        }

        $content = Html::tag('div', $dtHtml.$ddHtml, ['class' => $containerClasses])
            ->encode(false)
            ->render();

        $level = $item->getLevel();
        if ($level > 1) {
            $content = $this->wrapWithLevelBars($content, $level);
        }

        return $content;
    }

    /**
     * @return string[]
     */
    private function getContainerClasses(): array
    {
        return match ($this->cols) {
            1 => ['flex', 'flex-col', 'sm:flex-row', 'sm:justify-between', 'gap-1'],
            default => ['flex', 'flex-col', 'gap-1'],
        };
    }

    /**
     * Returns CSS classes for row wrapper (used with rowAttributes)
     * @return string[]
     */
    private function getRowWrapperClasses(): array
    {
        $classes = ['grid', 'grid-cols-subgrid'];
        $classes[] = match ($this->cols) {
            2 => 'col-span-2',
            3 => 'col-span-3',
            4 => 'col-span-4',
            5 => 'col-span-5',
            6 => 'col-span-6',
            default => 'col-span-full',
        };
        return $classes;
    }

    /**
     * Wraps content with nested divs for each level bar (level 2+)
     * Level 2 = 1 bar, Level 3 = 2 nested bars, etc.
     */
    private function wrapWithLevelBars(string $content, int $level): string
    {
        for ($i = $level; $i >= 2; $i--) {
            $classes = ['border-l-2', 'border-secondary-200', 'pl-4'];
            if ($i === 2) {
                $classes[] = 'ml-4';
            }
            $content = Html::tag('div', $content, ['class' => $classes])
                ->encode(false)
                ->render();
        }
        return $content;
    }

    /**
     * Checks if any item has level > 1 (arbo mode)
     */
    private function hasArboItems(): bool
    {
        foreach ($this->items as $item) {
            if ($item instanceof TermItem && $item->getLevel() > 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string[]
     */
    private function getDtClasses(): array
    {
        $baseClasses = ['font-medium'];

        $sizeClasses = match ($this->size) {
            Bleet::SIZE_XS => ['text-xs'],
            Bleet::SIZE_SM => ['text-sm'],
            Bleet::SIZE_MD => ['text-base'],
            Bleet::SIZE_LG => ['text-lg'],
            Bleet::SIZE_XL => ['text-xl'],
        };

        $colorClasses = match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-800'],
            Bleet::COLOR_SECONDARY => ['text-secondary-800'],
            Bleet::COLOR_SUCCESS => ['text-success-800'],
            Bleet::COLOR_DANGER => ['text-danger-800'],
            Bleet::COLOR_WARNING => ['text-warning-800'],
            Bleet::COLOR_INFO => ['text-info-800'],
            Bleet::COLOR_ACCENT => ['text-accent-800'],
        };

        return [...$baseClasses, ...$sizeClasses, ...$colorClasses];
    }

    /**
     * @return string[]
     */
    private function getDdClasses(): array
    {
        $sizeClasses = match ($this->size) {
            Bleet::SIZE_XS => ['text-xs'],
            Bleet::SIZE_SM => ['text-sm'],
            Bleet::SIZE_MD => ['text-base'],
            Bleet::SIZE_LG => ['text-lg'],
            Bleet::SIZE_XL => ['text-xl'],
        };

        $colorClasses = ['text-secondary-600'];

        return [...$sizeClasses, ...$colorClasses];
    }
}
