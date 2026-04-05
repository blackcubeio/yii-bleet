<?php

declare(strict_types=1);

/**
 * TermItem.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

/**
 * TermItem widget for description list terms (dt + dd elements)
 *
 * Usage:
 *   Bleet::termItem('Nom')->detail('Jean Dupont');
 *   Bleet::termItem('Langues')
 *       ->addDetail(Bleet::detailItem('French'))
 *       ->addDetail(Bleet::detailItem('Anglais'));
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class TermItem
{
    private string $term;
    private bool $encodeTerm = true;
    /** @var array<DetailItem> */
    private array $details = [];
    private int $level = 1;
    /** @var array<string, mixed> */
    private array $rowAttributes = [];

    public function __construct(string $term = '')
    {
        $this->term = $term;
    }

    /**
     * Sets le terme (dt)
     */
    public function term(string $term): self
    {
        $new = clone $this;
        $new->term = $term;
        return $new;
    }

    /**
     * Disables HTML encoding du terme
     */
    public function encodeTerm(bool $encode = true): self
    {
        $new = clone $this;
        $new->encodeTerm = $encode;
        return $new;
    }

    /**
     * Raccourci pour ajouter un detail string simple
     */
    public function detail(string $detail): self
    {
        return $this->addDetail(new DetailItem($detail));
    }

    /**
     * Adds un DetailItem
     */
    public function addDetail(DetailItem $detail): self
    {
        $new = clone $this;
        $new->details[] = $detail;
        return $new;
    }

    /**
     * Sets tous les details d'un coup
     * @param array<DetailItem|string> $details
     */
    public function details(array $details): self
    {
        $new = clone $this;
        $new->details = [];
        foreach ($details as $detail) {
            if ($detail instanceof DetailItem) {
                $new->details[] = $detail;
            } else {
                $new->details[] = new DetailItem((string) $detail);
            }
        }
        return $new;
    }

    /**
     * Returns le terme
     */
    public function getTerm(): string
    {
        return $this->term;
    }

    /**
     * Returns si l'encodage du terme est actif
     */
    public function isTermEncoded(): bool
    {
        return $this->encodeTerm;
    }

    /**
     * Returns les details
     * @return array<DetailItem>
     */
    public function getDetails(): array
    {
        return $this->details;
    }

    /**
     * Generates the term content, encoded or not
     */
    public function renderTerm(): string
    {
        return $this->encodeTerm ? htmlspecialchars($this->term, ENT_QUOTES | ENT_HTML5) : $this->term;
    }

    /**
     * Sets the tree level for hierarchical display
     */
    public function level(int $level): self
    {
        $new = clone $this;
        $new->level = $level;
        return $new;
    }

    /**
     * Returns the tree level
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Sets row-level attributes (used to wrap a logical row in grid mode)
     * @param array<string, mixed> $attributes
     */
    public function rowAttributes(array $attributes): self
    {
        $new = clone $this;
        $new->rowAttributes = $attributes;
        return $new;
    }

    /**
     * Adds a single row-level attribute
     */
    public function rowAttribute(string $name, mixed $value): self
    {
        $new = clone $this;
        $new->rowAttributes[$name] = $value;
        return $new;
    }

    /**
     * Returns row-level attributes
     * @return array<string, mixed>
     */
    public function getRowAttributes(): array
    {
        return $this->rowAttributes;
    }

    /**
     * Checks if this item has row attributes (marks start of a logical row)
     */
    public function hasRowAttributes(): bool
    {
        return !empty($this->rowAttributes);
    }
}
