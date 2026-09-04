<?php

declare(strict_types=1);

/**
 * BleetFieldTrait.php
 *
 * PHP Version 8.4
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Traits;

use Blackcube\Bleet\Bleet;
use InvalidArgumentException;

/**
 * The styling shared by the Bleet fields: the shade and the three parts of the
 * block the field does not draw itself.
 *
 * The field mechanics come from blackcube/yii-form; this trait only sets what
 * belongs to the interface.
 *
 * @copyright 2010-2026 Blackcube - Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait BleetFieldTrait
{
    public const LABEL_CLASS = 'block text-sm/6 font-medium text-secondary-900';
    public const HINT_CLASS = 'mt-2 text-sm text-secondary-500';
    public const ERROR_CLASS = 'mt-2 text-sm text-danger-600';
    public const INPUT_CONTAINER_CLASS = 'mt-2';

    protected string $color = Bleet::COLOR_SECONDARY;

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

    public function getColor(): string
    {
        return $this->color;
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
     * Styling of the three parts PartsField keeps private: the structure comes
     * from the field, the classes from here.
     */
    public function bleetParts(): static
    {
        return $this
            ->fieldParts()
            ->inputContainerAttributes(['class' => self::INPUT_CONTAINER_CLASS])
            ->labelClass(self::LABEL_CLASS)
            ->hintClass(self::HINT_CLASS)
            ->errorClass(self::ERROR_CLASS);
    }
}
