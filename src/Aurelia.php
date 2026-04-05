<?php

declare(strict_types=1);

/**
 * Aurelia.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet;

use Yiisoft\Json\Json;
use Yiisoft\Strings\Inflector;

/**
 * Aurelia - Helper for generating Aurelia 2 attributes
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class Aurelia
{
    private const BINDING_COMMANDS = [
        'bind', 'one-way', 'two-way', 'one-time', 'trigger', 'capture', 'ref', 'attr',
    ];

    private static ?Inflector $inflector = null;

    /**
     * Generates a string for custom attribute (options binding)
     * Usage: <button bleet-modal="id: myModal; url.bind: '/api/user/1';">
     *
     * @param array<string, mixed> $options
     * @return string
     */
    public static function attributesCustomAttribute(array $options): string
    {
        $parts = [];
        foreach ($options as $key => $value) {
            if ($value === null) {
                continue;
            }

            $bindingCommand = self::extractBindingCommand($key);
            if ($bindingCommand !== null) {
                $key = self::camelToKebab(self::getBaseName($key, $bindingCommand)) . '.' . $bindingCommand;
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } else {
                    $encoded = Json::encode($value);
                    $inner = substr($encoded, 1, -1);
                    $inner = str_replace("'", "\\'", $inner);
                    $value = "'" . $inner . "'";
                }
            } else {
                $key = self::camelToKebab($key);
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } else {
                    $value = (string) $value;
                    $value = str_replace(':', '\:', $value);
                    $value = str_replace(';', '\;', $value);
                }
            }
            $parts[] = $key . ': ' . $value;
        }
        return implode('; ', $parts) . ';';
    }

    /**
     * Generates an array of attributes for custom element (dedicated tag)
     * Usage: Html::tag('bleet-modal', '', Aurelia::attributesCustomElement([...]))
     *
     * @param array<string, mixed> $options
     * @return array<string, string|bool>
     */
    public static function attributesCustomElement(array $options): array
    {
        $result = [];
        foreach ($options as $key => $value) {
            if ($value === null) {
                continue;
            }

            $bindingCommand = self::extractBindingCommand($key);
            if ($bindingCommand !== null) {
                $key = self::camelToKebab(self::getBaseName($key, $bindingCommand)) . '.' . $bindingCommand;
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif(is_array($value)) {
                    $encoded = Json::encode($value);
                    $inner = str_replace("'", "\\'", $encoded);
                    $value = $inner;
                } else {
                    $encoded = Json::encode($value);
                    $inner = substr($encoded, 1, -1);
                    $inner = str_replace("'", "\\'", $inner);
                    $value = "'" . $inner . "'";
                }
            } else {
                $key = self::camelToKebab($key);
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } else {
                    $value = (string) $value;
                }
            }
            $result[$key] = $value;
        }
        return $result;
    }

    private static function extractBindingCommand(string $key): ?string
    {
        foreach (self::BINDING_COMMANDS as $command) {
            if (str_ends_with($key, '.' . $command)) {
                return $command;
            }
        }
        return null;
    }

    private static function getBaseName(string $key, string $command): string
    {
        return substr($key, 0, -strlen('.' . $command));
    }

    private static function camelToKebab(string $input): string
    {
        if (self::$inflector === null) {
            self::$inflector = new Inflector();
        }
        return self::$inflector->pascalCaseToId($input, '-');
    }
}
