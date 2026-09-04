<?php

declare(strict_types=1);

/**
 * AureliaCommunication.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Helper;

use Blackcube\Bleet\Enums\AjaxifyAction;
use Blackcube\Bleet\Enums\DialogAction;
use Blackcube\Bleet\Enums\UiColor;

/**
 * Class to build communication data for Aurelia frontend.
 */

class AureliaCommunication
{
    public const ACTION = 'action';
    public const TOAST = 'toast';
    public const AJAXIFY = 'ajaxify';
    public const DIALOG = 'dialog';
    public static function toast(string $title = '', string $content = '', UiColor $color = UiColor::Primary, int $duration = 5000): array
    {
        return [
            self::TOAST => [
                'title' => $title,
                'content' => $content,
                'color' => $color->value,
                'duration' => $duration,
            ]
        ];
    }

    public static function ajaxify(string $id, ?string $url = null, AjaxifyAction $action = AjaxifyAction::Refresh, ?string $method = null): array
    {
        $data = [
            'id' => $id,
            'url' => $url,
            'action' => $action->value,
        ];
        if ($method !== null) {
            $data['method'] = $method;
        }
        return [
            self::AJAXIFY => $data,
        ];
    }

    public static function dialog(DialogAction $action = DialogAction::Close): array
    {
        return [
            self::ACTION => $action->value,
        ];
    }

    public static function dialogContent(string $header, string $content, ?UiColor $color = null): array
    {
        $data = [
            'header' => $header,
            'content' => $content,
        ];
        if ($color !== null) {
            $data['color'] = $color->value;
        }
        return $data;
    }
}