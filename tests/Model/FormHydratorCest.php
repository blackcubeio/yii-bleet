<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Model;

use Blackcube\Bleet\Tests\Support\Administrator;
use Blackcube\Bleet\Tests\Support\ModelTester;
use Blackcube\Bleet\Tests\Support\User;

class FormHydratorCest
{
    public function testLoadAdministratorWithAdministratorData(ModelTester $I): void
    {
        $I->wantTo('verify Administrator model is loaded with Administrator[] data');

        $model = new Administrator();

        // Data with Administrator scope
        $data = [
            'Administrator' => [
                'email' => 'pgaultier@gmail.com',
                'password' => 'Password',
                'rememberMe' => '1',
            ],
        ];

        $result = $model->load($data);

        $I->assertTrue($result);
        $I->assertEquals('pgaultier@gmail.com', $model->getEmail());
        $I->assertEquals('Password', $model->getPassword());
        $I->assertTrue($model->isRememberMe());
    }

    public function testLoadUserWithAdministratorDataFails(ModelTester $I): void
    {
        $I->wantTo('verify User model is NOT loaded with Administrator[] data');

        $model = new User();

        // Data with Administrator scope (wrong scope for User model)
        $data = [
            'Administrator' => [
                'email' => 'pgaultier@gmail.com',
                'password' => 'Password',
                'rememberMe' => '1',
            ],
        ];

        $result = $model->load($data);

        // Load should return false because scope doesn't match
        $I->assertFalse($result);

        // Model properties should remain at default values
        $I->assertEquals('', $model->getEmail());
        $I->assertEquals('', $model->getPassword());
        $I->assertFalse($model->isRememberMe());
    }

    public function testLoadUserWithUserData(ModelTester $I): void
    {
        $I->wantTo('verify User model is loaded with User[] data');

        $model = new User();

        // Data with User scope
        $data = [
            'User' => [
                'email' => 'user@example.com',
                'password' => 'secret',
                'rememberMe' => '1',
            ],
        ];

        $result = $model->load($data);

        $I->assertTrue($result);
        $I->assertEquals('user@example.com', $model->getEmail());
        $I->assertEquals('secret', $model->getPassword());
        $I->assertTrue($model->isRememberMe());
    }

    public function testGetFormNameReturnsClassName(ModelTester $I): void
    {
        $I->wantTo('verify getFormName returns the short class name');

        $admin = new Administrator();
        $user = new User();

        $I->assertEquals('Administrator', $admin->getFormName());
        $I->assertEquals('User', $user->getFormName());
    }

    public function testAdministratorLabels(ModelTester $I): void
    {
        $I->wantTo('verify Administrator model provides correct labels');

        $model = new Administrator();

        $I->assertEquals('Adresse email', $model->getPropertyLabel('email'));
        $I->assertEquals('Mot de passe', $model->getPropertyLabel('password'));
        $I->assertEquals('Se souvenir de moi', $model->getPropertyLabel('rememberMe'));
    }

    public function testAdministratorHints(ModelTester $I): void
    {
        $I->wantTo('verify Administrator model provides correct hints');

        $model = new Administrator();

        $I->assertEquals('Utilisez votre email professionnel', $model->getPropertyHint('email'));
        $I->assertEquals('Minimum 8 caractères', $model->getPropertyHint('password'));
        $I->assertEquals('', $model->getPropertyHint('rememberMe'));
    }

    public function testAdministratorPlaceholders(ModelTester $I): void
    {
        $I->wantTo('verify Administrator model provides correct placeholders');

        $model = new Administrator();

        $I->assertEquals('admin@example.com', $model->getPropertyPlaceholder('email'));
        $I->assertEquals('********', $model->getPropertyPlaceholder('password'));
        $I->assertEquals('', $model->getPropertyPlaceholder('rememberMe'));
    }

    public function testDefaultLabelGeneration(ModelTester $I): void
    {
        $I->wantTo('verify default label is generated from property name');

        $model = new User();

        // User model has labels defined
        $I->assertEquals('Adresse email', $model->getPropertyLabel('email'));

        // For undefined label, FormModel generates from property name
        // 'rememberMe' becomes 'Remember Me'
        $I->assertEquals('Se souvenir de moi', $model->getPropertyLabel('rememberMe'));
    }
}
