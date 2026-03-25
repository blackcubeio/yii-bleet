<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\BridgeModel\BridgeFormModel;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

/**
 * Simple BridgeFormModel for testing (without #[Elastic]).
 */
final class SimpleBleetModel extends BridgeFormModel
{
    public string $name = '';
    public string $email = '';

    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['name', 'email'],
        ];
    }

    public function rules(): array
    {
        return [
            'name' => [
                new Required(),
                new Length(min: 2, max: 100),
            ],
            'email' => [
                new Required(),
                new Email(),
            ],
        ];
    }

    public function getPropertyLabels(): array
    {
        return [
            'name' => 'Nom complet',
            'email' => 'Adresse email',
        ];
    }

    public function getPropertyHints(): array
    {
        return [
            'name' => 'Votre nom et prénom',
            'email' => 'Un email valide est requis',
        ];
    }

    public function getPropertyPlaceholders(): array
    {
        return [
            'name' => 'Jean Dupont',
            'email' => 'jean@example.com',
        ];
    }
}
