<?php

declare(strict_types=1);

/**
 * AbstractBleetField.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Widgets;

use Blackcube\Bleet\Bleet;
use InvalidArgumentException;
use Yiisoft\Form\Field\Base\InputField;

/**
 * Bleet field base: the block assembly and its styling.
 *
 * The name, the id, the value, the label, the hint, the errors and the
 * attributes derived from the validation rules all come from yiisoft/form.
 * What lives here is the look: the template, the containers, the classes.
 *
 * The label, hint and error classes are set by the Bleet factories: PartsField
 * keeps those three configurations private, and the widget() factory of
 * Yiisoft\Widget\Widget is final.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
abstract class AbstractBleetField extends InputField
{
    public const LABEL_CLASS = 'block text-sm/6 font-medium text-secondary-900';
    public const HINT_CLASS = 'mt-2 text-sm text-secondary-500';
    public const ERROR_CLASS = 'mt-2 text-sm text-danger-600';

    protected string $color = Bleet::COLOR_SECONDARY;
    protected string $template = "{label}\n{input}\n{hint}\n{error}";
    protected ?string $inputContainerTag = 'div';
    protected array $inputContainerAttributes = ['class' => 'mt-2'];

    public function color(string $color): static
    {
        if (in_array($color, Bleet::COLORS, true) === false) {
            throw new InvalidArgumentException(
                sprintf('Invalid color "%s". Valid: %s', $color, implode(', ', Bleet::COLORS))
            );
        }

        $new = clone $this;
        $new->color = $color;

        return $new;
    }

    public function primary(): static
    {
        return $this->color(Bleet::COLOR_PRIMARY);
    }

    public function secondary(): static
    {
        return $this->color(Bleet::COLOR_SECONDARY);
    }

    public function success(): static
    {
        return $this->color(Bleet::COLOR_SUCCESS);
    }

    public function danger(): static
    {
        return $this->color(Bleet::COLOR_DANGER);
    }

    public function warning(): static
    {
        return $this->color(Bleet::COLOR_WARNING);
    }

    public function info(): static
    {
        return $this->color(Bleet::COLOR_INFO);
    }

    public function accent(): static
    {
        return $this->color(Bleet::COLOR_ACCENT);
    }

    /**
     * The component classes, unless the caller has set its own.
     *
     * Bleet styles the field by default; as soon as the call states its own
     * classes - inputClass() or addInputClass() -, those are the ones that
     * count, and the standard steps aside instead of competing with them.
     *
     * @param string[] $classes
     * @return string[]
     */
    protected function bleetClasses(array $classes): array
    {
        $replaced = $this->classesReplaced === true || isset($this->inputAttributes['class']) === true;

        return $replaced ? [] : $classes;
    }

    /**
     * Styling of the three parts PartsField keeps private.
     */
    public function bleetParts(): static
    {
        $id = $this->getInputData()->getId();

        $hintId = $id === null ? null : $id.'-description';
        $errorId = $id === null ? null : $id.'-error';

        return $this
            ->labelClass(self::LABEL_CLASS)
            ->hintClass(self::HINT_CLASS)
            ->errorClass(self::ERROR_CLASS)
            ->hintConfig([
                'tag()' => ['p'],
                'id()' => [$hintId],
            ])
            ->errorConfig([
                'tag()' => ['p'],
                'id()' => [$errorId],
            ]);
    }

    /**
     * Id of the part that explains the field: the error message when there is
     * one, the hint otherwise. This is what aria-describedby carries.
     */
    protected function describedById(): ?string
    {
        $inputData = $this->getInputData();
        $id = $inputData->getId();
        $describedBy = null;

        if ($id !== null) {
            if ($inputData->getValidationErrors() !== []) {
                $describedBy = $id.'-error';
            } elseif ($inputData->getHint() !== null && $inputData->getHint() !== '') {
                $describedBy = $id.'-description';
            }
        }

        return $describedBy;
    }
}
