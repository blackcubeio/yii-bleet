<?php

declare(strict_types=1);

/**
 * Ajaxify.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Aurelia;
use Yiisoft\Html\Html;

/**
 * Ajaxify widget - AJAX component and trigger
 *
 * Usage:
 *   // Trigger attribute on element
 *   Bleet::toggle()->attributes(Bleet::ajaxify()->event('change')->trigger())->render()
 *   Bleet::button()->attributes(Bleet::ajaxify()->url('/api/action')->trigger())->render()
 *
 *   // Ajaxify zone (component)
 *   Bleet::ajaxify('myZone')->url('/api/content')->open()
 *   ... content ...
 *   Bleet::ajaxify()->close()
 */
final class Ajaxify
{
    private string $id = '';
    private ?string $url = null;
    private string $verb = 'POST';
    private string $event = 'click';

    public function __construct(string $id = '')
    {
        $this->id = $id;
    }

    public function id(string $id): self
    {
        $new = clone $this;
        $new->id = $id;
        return $new;
    }

    public function url(string $url): self
    {
        $new = clone $this;
        $new->url = $url;
        return $new;
    }

    public function verb(string $verb): self
    {
        $new = clone $this;
        $new->verb = $verb;
        return $new;
    }

    public function event(string $event): self
    {
        $new = clone $this;
        $new->event = $event;
        return $new;
    }

    /**
     * Returns attributes array for the trigger
     * @return array<string, string>
     */
    public function trigger(): array
    {
        $options = [];

        if ($this->url !== null) {
            $options['url'] = $this->url;
        }
        if ($this->verb !== 'POST') {
            $options['verb'] = $this->verb;
        }
        if ($this->event !== 'click') {
            $options['event'] = $this->event;
        }
        if ($this->id !== '') {
            $options['id'] = $this->id;
        }

        if (empty($options)) {
            return ['bleet-ajaxify-trigger' => ''];
        }

        return ['bleet-ajaxify-trigger' => Aurelia::attributesCustomAttribute($options)];
    }

    /**
     * Opens the ajaxify component tag
     */
    public function open(): string
    {
        $attributes = [];
        if ($this->id !== '') {
            $attributes['id'] = $this->id;
        }
        if ($this->url !== null) {
            $attributes['url'] = $this->url;
        }

        return Html::openTag('bleet-ajaxify', $attributes);
    }

    /**
     * Closes the ajaxify component tag
     */
    public function close(): string
    {
        return Html::closeTag('bleet-ajaxify');
    }
}
