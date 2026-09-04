<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\BridgeModel\BridgeFormModel;
use Yiisoft\Validator\Rule\BooleanValue;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Required;

class User extends BridgeFormModel
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
            'email' => 'Email address',
            'password' => 'Password',
            'rememberMe' => 'Remember me',
        ];
    }

    public function getPropertyHints(): array
    {
        return [
            'email' => 'Your personal email address',
            'password' => 'Choose a strong password',
        ];
    }

    public function getPropertyPlaceholders(): array
    {
        return [
            'email' => 'user@example.com',
            'password' => '********',
        ];
    }
}
