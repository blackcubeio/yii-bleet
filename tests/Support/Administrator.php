<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\BridgeModel\BridgeFormModel;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Required;

class Administrator extends BridgeFormModel
{
    private string $email = '';
    private string $password = '';
    private bool $rememberMe = false;

    public function scenarios(): array
    {
        return [
            self::SCENARIO_DEFAULT => ['email', 'password', 'rememberMe'],
        ];
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isRememberMe(): bool
    {
        return $this->rememberMe;
    }

    public function rules(): array
    {
        return [
            'email' => [new Required(), new Email()],
            'password' => [new Required()],
            'rememberMe' => [new BooleanValue()],
        ];
    }

    public function getPropertyLabels(): array
    {
        return [
            'email' => 'Adresse email',
            'password' => 'Mot de passe',
            'rememberMe' => 'Se souvenir de moi',
        ];
    }

    public function getPropertyHints(): array
    {
        return [
            'email' => 'Utilisez votre email professionnel',
            'password' => 'Minimum 8 caractères',
        ];
    }

    public function getPropertyPlaceholders(): array
    {
        return [
            'email' => 'admin@example.com',
            'password' => '********',
        ];
    }
}
