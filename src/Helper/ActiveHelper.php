<?php

declare(strict_types=1);

/**
 * ActiveHelper.php
 *
 * PHP Version 8.1
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Helper;

use Yiisoft\FormModel\FormModelInterface;

/**
 * Helper for generating input names and IDs from models.
 *
 * Follows Yii2 patterns for tabular input support.
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
final class ActiveHelper
{
    /**
     * Regex pattern for parsing attribute expressions.
     *
     * Matches: prefix (brackets), attribute name, suffix (brackets)
     * Examples:
     * - `name` → ['', 'name', '']
     * - `[0]name` → ['[0]', 'name', '']
     * - `[items][0]name` → ['[items][0]', 'name', '']
     * - `name[key]` → ['', 'name', '[key]']
     */
    private const ATTRIBUTE_REGEX = '/(^|.*\])([\w\.\+]+)(\[.*|$)/u';

    /**
     * Generates an input name for a model attribute.
     *
     * Examples:
     * - formName='User', attribute='name' → 'User[name]'
     * - formName='Bloc[16]', attribute='lang' → 'Bloc[16][lang]'
     * - formName='Item', attribute='[0]name' → 'Item[0][name]'
     *
     * @param FormModelInterface $model The form model
     * @param string $attribute The attribute name or expression
     * @return string The generated input name
     */
    public static function getInputName(FormModelInterface $model, string $attribute): string
    {
        $formName = $model->getFormName();

        if (!preg_match(self::ATTRIBUTE_REGEX, $attribute, $matches)) {
            if ($formName === '') {
                return $attribute;
            }
            return $formName . '[' . $attribute . ']';
        }

        $prefix = $matches[1];
        $attributeName = $matches[2];
        $suffix = $matches[3];

        if ($formName === '' && $prefix === '') {
            return $attributeName . $suffix;
        }

        if ($formName !== '') {
            return $formName . $prefix . '[' . $attributeName . ']' . $suffix;
        }

        return $prefix . '[' . $attributeName . ']' . $suffix;
    }

    /**
     * Generates an input ID for a model attribute.
     *
     * Uses the input name and converts brackets to dashes.
     *
     * Examples:
     * - formName='User', attribute='name' → 'user-name'
     * - formName='Bloc[16]', attribute='lang' → 'bloc-16-lang'
     *
     * @param FormModelInterface $model The form model
     * @param string $attribute The attribute name or expression
     * @return string The generated input ID
     */
    public static function getInputId(FormModelInterface $model, string $attribute): string
    {
        $name = self::getInputName($model, $attribute);
        return self::nameToId($name);
    }

    /**
     * Converts an input name to a valid HTML ID.
     *
     * Follows Yii2 pattern: brackets become dashes, lowercase.
     *
     * Examples:
     * - 'User[name]' → 'user-name'
     * - 'Bloc[16][lang]' → 'bloc-16-lang'
     * - 'items[]' → 'items'
     *
     * @param string $name The input name
     * @return string The generated ID
     */
    public static function nameToId(string $name): string
    {
        $name = mb_strtolower($name);
        return str_replace(['[]', '][', '[', ']', ' ', '.', '--'], ['', '-', '-', '', '-', '-', '-'], $name);
    }

    /**
     * Extracts the actual attribute name from a tabular attribute expression.
     *
     * Examples:
     * - 'name' → 'name'
     * - '[0]allowed' → 'allowed'
     * - '[items][0]name' → 'name'
     *
     * @param string $attribute The attribute expression
     * @return string The attribute name
     */
    public static function getAttributeName(string $attribute): string
    {
        if (preg_match(self::ATTRIBUTE_REGEX, $attribute, $matches)) {
            return $matches[2];
        }

        return $attribute;
    }
}
