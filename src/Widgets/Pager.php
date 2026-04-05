<?php

declare(strict_types=1);

/**
 * Pager.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use Yiisoft\Data\Paginator\PaginatorInterface;
use Yiisoft\Router\UrlGeneratorInterface;

/**
 * Pager widget - Pagination component
 *
 * Displays pagination controls with prev/next buttons, page numbers (desktop),
 * and a select dropdown (mobile). Optional information text showing current range.
 *
 * Usage:
 *   Bleet::pager($paginator, $urlGenerator)
 *       ->pageParam('page')
 *       ->maxButtonCount(10)
 *       ->showInfo()
 *       ->primary()
 *       ->render();
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Pager extends AbstractWidget
{
    use RenderViewTrait;

    protected string $color = Bleet::COLOR_PRIMARY;

    private PaginatorInterface $paginator;
    private UrlGeneratorInterface $urlGenerator;
    private string $pageParam = 'page';
    private int $maxButtonCount = 10;
    private bool $hideOnSinglePage = true;
    private bool $showInfo = true;
    private bool $showMobileSelect = true;
    private array $containerAttributes = [];

    public function __construct(PaginatorInterface $paginator, UrlGeneratorInterface $urlGenerator)
    {
        $this->paginator = $paginator;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * Sets the page parameter name in the URL
     */
    public function pageParam(string $pageParam): self
    {
        $new = clone $this;
        $new->pageParam = $pageParam;
        return $new;
    }

    /**
     * Sets max number of page buttons to display
     */
    public function maxButtonCount(int $maxButtonCount): self
    {
        $new = clone $this;
        $new->maxButtonCount = $maxButtonCount;
        return $new;
    }

    /**
     * Cache le widget quand une seule page existe
     */
    public function hideOnSinglePage(bool $hide = true): self
    {
        $new = clone $this;
        $new->hideOnSinglePage = $hide;
        return $new;
    }

    /**
     * Displays info text (e.g., "Showing 1 to 10 of 47 results")
     */
    public function showInfo(bool $show = true): self
    {
        $new = clone $this;
        $new->showInfo = $show;
        return $new;
    }

    /**
     * Cache le texte d'information
     */
    public function hideInfo(): self
    {
        return $this->showInfo(false);
    }

    /**
     * Affiche le select mobile
     */
    public function showMobileSelect(bool $show = true): self
    {
        $new = clone $this;
        $new->showMobileSelect = $show;
        return $new;
    }

    /**
     * Cache le select mobile
     */
    public function hideMobileSelect(): self
    {
        return $this->showMobileSelect(false);
    }

    /**
     * Sets the id attribute
     */
    public function id(string $id): self
    {
        $new = clone $this;
        $new->containerAttributes['id'] = $id;
        return $new;
    }

    /**
     * Adds a CSS class
     */
    public function addClass(string ...$classes): self
    {
        $new = clone $this;
        $existing = $new->containerAttributes['class'] ?? '';
        $new->containerAttributes['class'] = trim($existing . ' ' . implode(' ', $classes));
        return $new;
    }

    /**
     * Sets an HTML attribute
     */
    public function attribute(string $name, mixed $value): self
    {
        $new = clone $this;
        $new->containerAttributes[$name] = $value;
        return $new;
    }

    public function render(): string
    {
        if (!$this->paginator->isPaginationRequired() && $this->hideOnSinglePage) {
            return '';
        }

        return $this->renderView('pager', $this->prepareViewParams());
    }

    /**
     * @return string[]
     */
    protected function prepareClasses(): array
    {
        return [];
    }

    /**
     * Generates URL for a given page
     */
    private function generatePageUrl(int $page): string
    {
        return $this->urlGenerator->generateFromCurrent([], [$this->pageParam => $page]);
    }

    /**
     * Calculates the page range to display
     * @return array{int, int} [beginPage, endPage]
     */
    private function getPageRange(): array
    {
        $currentPage = $this->paginator->getCurrentPage();
        $totalPages = $this->paginator->getTotalPages();

        $beginPageOffset = $this->maxButtonCount > 2 ? (int) ($this->maxButtonCount / 2) : 0;
        $beginPage = max(1, $currentPage - $beginPageOffset);

        $endPage = $beginPage + $this->maxButtonCount - 1;
        if ($endPage > $totalPages) {
            $endPage = $totalPages;
            $beginPage = max(1, $endPage - $this->maxButtonCount + 1);
        }

        return [$beginPage, $endPage];
    }

    private function prepareViewParams(): array
    {
        $currentPage = $this->paginator->getCurrentPage();
        $totalPages = $this->paginator->getTotalPages();
        $totalItems = $this->paginator->getTotalCount();
        $pageSize = $this->paginator->getPageSize();
        $offset = $this->paginator->getOffset();

        [$beginPage, $endPage] = $this->getPageRange();

        // Container
        $containerAttributes = $this->containerAttributes;
        $containerAttributes['bleet-pager'] = true;
        $existingClass = $containerAttributes['class'] ?? '';
        $containerAttributes['class'] = trim('flex flex-col sm:flex-row items-center justify-between gap-4 ' . $existingClass);

        // Info
        $info = null;
        if ($this->showInfo) {
            $begin = $offset + 1;
            $end = min($offset + $pageSize, $totalItems);
            $info = [
                'begin' => $begin,
                'end' => $end,
                'total' => $totalItems,
            ];
        }

        // Pages with URLs
        $pages = [];
        for ($i = $beginPage; $i <= $endPage; $i++) {
            $pages[] = [
                'number' => $i,
                'url' => $this->generatePageUrl($i),
                'active' => $i === $currentPage,
            ];
        }

        // Prev/Next
        $prevPage = $this->paginator->isOnFirstPage() ? null : [
            'number' => $currentPage - 1,
            'url' => $this->generatePageUrl($currentPage - 1),
        ];

        $nextPage = $this->paginator->isOnLastPage() ? null : [
            'number' => $currentPage + 1,
            'url' => $this->generatePageUrl($currentPage + 1),
        ];

        return [
            'containerAttributes' => $containerAttributes,
            'info' => $info,
            'pages' => $pages,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'prevPage' => $prevPage,
            'nextPage' => $nextPage,
            'showMobileSelect' => $this->showMobileSelect,
            'infoContainerClasses' => ['hidden', 'sm:block'],
            'infoTextClasses' => ['text-sm', ...$this->getInfoTextColorClasses()],
            'infoNumberClasses' => ['font-medium', ...$this->getInfoNumberColorClasses()],
            'navClasses' => ['w-full', 'sm:w-auto'],
            'navInnerClasses' => ['flex', 'items-center', 'justify-center', 'sm:justify-end', 'gap-2'],
            'desktopContainerClasses' => ['hidden', 'sm:flex', 'items-center', 'gap-2'],
            'mobileContainerClasses' => ['sm:hidden'],
            'mobileSelectClasses' => [
                'block', 'w-full', 'rounded-md', 'border-0', 'py-1.5', 'pl-3', 'pr-10',
                'ring-1', 'ring-inset', 'focus:ring-2', 'text-sm', 'leading-6',
                ...$this->getMobileSelectColorClasses(),
            ],
            'buttonClasses' => ['no-underline', 'rounded-md', 'bg-white', 'p-2', 'ring-1', 'ring-inset', ...$this->getButtonColorClasses()],
            'buttonDisabledClasses' => ['no-underline', 'rounded-md', 'bg-white', 'p-2', 'ring-1', 'ring-inset', 'opacity-50', 'cursor-not-allowed', ...$this->getButtonDisabledColorClasses()],
            'numberButtonClasses' => ['no-underline', 'rounded-md', 'bg-white', 'px-3', 'py-2', 'text-sm', 'font-semibold', 'ring-1', 'ring-inset', ...$this->getNumberButtonColorClasses()],
            'numberButtonActiveClasses' => ['no-underline', 'rounded-md', 'px-3', 'py-2', 'text-sm', 'font-semibold', 'text-white', 'shadow-xs', ...$this->getNumberButtonActiveColorClasses()],
            'svgClasses' => ['size-5'],
        ];
    }

    /**
     * @return string[]
     */
    private function getMobileSelectColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-700', 'ring-primary-300', 'focus:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-700', 'ring-secondary-300', 'focus:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-700', 'ring-success-300', 'focus:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-700', 'ring-danger-300', 'focus:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-700', 'ring-warning-300', 'focus:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-700', 'ring-info-300', 'focus:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-700', 'ring-accent-300', 'focus:ring-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getInfoTextColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-600'],
            Bleet::COLOR_INFO => ['text-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getInfoNumberColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900'],
            Bleet::COLOR_SECONDARY => ['text-secondary-900'],
            Bleet::COLOR_SUCCESS => ['text-success-900'],
            Bleet::COLOR_DANGER => ['text-danger-900'],
            Bleet::COLOR_WARNING => ['text-warning-900'],
            Bleet::COLOR_INFO => ['text-info-900'],
            Bleet::COLOR_ACCENT => ['text-accent-900'],
        };
    }

    /**
     * @return string[]
     */
    private function getButtonColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-600', 'ring-primary-300', 'hover:bg-primary-50', 'focus-visible:ring-2', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-600', 'ring-secondary-300', 'hover:bg-secondary-50', 'focus-visible:ring-2', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-600', 'ring-success-300', 'hover:bg-success-50', 'focus-visible:ring-2', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-600', 'ring-danger-300', 'hover:bg-danger-50', 'focus-visible:ring-2', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-600', 'ring-warning-300', 'hover:bg-warning-50', 'focus-visible:ring-2', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-600', 'ring-info-300', 'hover:bg-info-50', 'focus-visible:ring-2', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-600', 'ring-accent-300', 'hover:bg-accent-50', 'focus-visible:ring-2', 'focus-visible:ring-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getButtonDisabledColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-300', 'ring-primary-200'],
            Bleet::COLOR_SECONDARY => ['text-secondary-300', 'ring-secondary-200'],
            Bleet::COLOR_SUCCESS => ['text-success-300', 'ring-success-200'],
            Bleet::COLOR_DANGER => ['text-danger-300', 'ring-danger-200'],
            Bleet::COLOR_WARNING => ['text-warning-300', 'ring-warning-200'],
            Bleet::COLOR_INFO => ['text-info-300', 'ring-info-200'],
            Bleet::COLOR_ACCENT => ['text-accent-300', 'ring-accent-200'],
        };
    }

    /**
     * @return string[]
     */
    private function getNumberButtonColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['text-primary-900', 'ring-primary-300', 'hover:bg-primary-50', 'focus-visible:ring-2', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['text-secondary-900', 'ring-secondary-300', 'hover:bg-secondary-50', 'focus-visible:ring-2', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['text-success-900', 'ring-success-300', 'hover:bg-success-50', 'focus-visible:ring-2', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['text-danger-900', 'ring-danger-300', 'hover:bg-danger-50', 'focus-visible:ring-2', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['text-warning-900', 'ring-warning-300', 'hover:bg-warning-50', 'focus-visible:ring-2', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['text-info-900', 'ring-info-300', 'hover:bg-info-50', 'focus-visible:ring-2', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['text-accent-900', 'ring-accent-300', 'hover:bg-accent-50', 'focus-visible:ring-2', 'focus-visible:ring-accent-600'],
        };
    }

    /**
     * @return string[]
     */
    private function getNumberButtonActiveColorClasses(): array
    {
        return match ($this->color) {
            Bleet::COLOR_PRIMARY => ['bg-primary-600', 'hover:bg-primary-700', 'focus-visible:ring-2', 'focus-visible:ring-primary-600'],
            Bleet::COLOR_SECONDARY => ['bg-secondary-600', 'hover:bg-secondary-700', 'focus-visible:ring-2', 'focus-visible:ring-secondary-600'],
            Bleet::COLOR_SUCCESS => ['bg-success-600', 'hover:bg-success-700', 'focus-visible:ring-2', 'focus-visible:ring-success-600'],
            Bleet::COLOR_DANGER => ['bg-danger-600', 'hover:bg-danger-700', 'focus-visible:ring-2', 'focus-visible:ring-danger-600'],
            Bleet::COLOR_WARNING => ['bg-warning-600', 'hover:bg-warning-700', 'focus-visible:ring-2', 'focus-visible:ring-warning-600'],
            Bleet::COLOR_INFO => ['bg-info-600', 'hover:bg-info-700', 'focus-visible:ring-2', 'focus-visible:ring-info-600'],
            Bleet::COLOR_ACCENT => ['bg-accent-600', 'hover:bg-accent-700', 'focus-visible:ring-2', 'focus-visible:ring-accent-600'],
        };
    }
}
