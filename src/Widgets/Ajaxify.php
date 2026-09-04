<?php

declare(strict_types=1);

/**
 * Ajaxify.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Aurelia;
use Blackcube\Bleet\Enums\AjaxifyTriggerMode;
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
class Ajaxify
{
    private string $id = '';
    private ?string $url = null;
    private string $verb = 'POST';
    private string $event = 'click';
    private ?AjaxifyTriggerMode $mode = null;
    private ?string $target = null;
    private ?string $method = null;

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

    public function mode(AjaxifyTriggerMode $mode): self
    {
        $new = clone $this;
        $new->mode = $mode;
        return $new;
    }

    public function target(string $target): self
    {
        $new = clone $this;
        $new->target = $target;
        return $new;
    }

    public function method(string $method): self
    {
        $new = clone $this;
        $new->method = $method;
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
        if ($this->mode !== null) {
            $options['mode'] = $this->mode->value;
        }
        if ($this->target !== null) {
            $options['target'] = $this->target;
        }
        if ($this->method !== null) {
            $options['method'] = $this->method;
        }

        if (empty($options) === true) {
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
