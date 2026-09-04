<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\BridgeModel\BridgeFormModel;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Number;
use Yiisoft\Validator\Rule\Required;

/**
 * Test form for scenario testing (BridgeFormModel).
 */
final class ScenarioTestForm extends BridgeFormModel
{
    public string $name = '';
    public int $age = 0;
    public bool $active = true;

    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['name', 'age', 'active'],
            'all' => ['name', 'age', 'active'],
            'partial' => ['name', 'active'],
            'empty' => [],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [new Required()],
            'age' => [new Integer(), new Number(min: 0)],
            'active' => [new BooleanValue()],
        ];
    }

    public function getPropertyLabels(): array
    {
        return [
            'name' => 'Nom',
            'age' => 'Age',
            'active' => 'Actif',
        ];
    }

    public function getPropertyHints(): array
    {
        return [];
    }
}
