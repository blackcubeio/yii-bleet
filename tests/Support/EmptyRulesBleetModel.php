<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\BridgeModel\BridgeFormModel;

/**
 * BridgeFormModel without rules() override - tests default empty rules behavior.
 */
final class EmptyRulesBleetModel extends BridgeFormModel
{
    public string $name = '';
    public string $email = '';

    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['name', 'email'],
        ];
    }

    // Ne PAS override rules() → utilise le défaut qui retourne []
}
