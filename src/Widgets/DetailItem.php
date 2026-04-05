<?php

declare(strict_types=1);

/**
 * DetailItem.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

/**
 * DetailItem widget for description list details (dd element)
 *
 * Usage:
 *   Bleet::detailItem('Jean Dupont');
 *   Bleet::detailItem('<a href="/edit">Modifier</a>')->encode(false);
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class DetailItem
{
    private string $content;
    private bool $encode = true;

    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Sets the content
     */
    public function content(string $content): self
    {
        $new = clone $this;
        $new->content = $content;
        return $new;
    }

    /**
     * Disables HTML encoding of the content
     */
    public function encode(bool $encode = true): self
    {
        $new = clone $this;
        $new->encode = $encode;
        return $new;
    }

    /**
     * Returns the content
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Returns si l'encodage est actif
     */
    public function isEncoded(): bool
    {
        return $this->encode;
    }

    /**
     * Generates content, encoded or not
     */
    public function renderContent(): string
    {
        return $this->encode ? htmlspecialchars($this->content, ENT_QUOTES | ENT_HTML5) : $this->content;
    }
}
