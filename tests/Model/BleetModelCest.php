<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Model;

use Blackcube\Bleet\Tests\Support\EmptyRulesBleetModel;
use Blackcube\Bleet\Tests\Support\ModelTester;
use Blackcube\Bleet\Tests\Support\SimpleBleetModel;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

class BleetModelCest
{
    public function testModelConstruction(ModelTester $I): void
    {
        $I->wantTo('verify BridgeFormModel can be constructed');

        $model = new SimpleBleetModel();

        $I->assertInstanceOf(SimpleBleetModel::class, $model);
        $I->assertSame('', $model->name);
        $I->assertSame('', $model->email);
    }

    public function testModelGetRulesReturnsRules(ModelTester $I): void
    {
        $I->wantTo('verify getRules returns validation rules');

        $model = new SimpleBleetModel();
        $rules = $model->getRules();

        $I->assertIsIterable($rules);
        $I->assertArrayHasKey('name', $rules);
        $I->assertArrayHasKey('email', $rules);
    }

    public function testModelNameRulesContainRequiredAndLength(ModelTester $I): void
    {
        $I->wantTo('verify name property has Required and Length rules');

        $model = new SimpleBleetModel();
        $rules = $model->getRules();

        $nameRules = $rules['name'];
        $I->assertCount(2, $nameRules);

        $ruleTypes = array_map(fn($rule) => $rule::class, $nameRules);
        $I->assertContains(Required::class, $ruleTypes);
        $I->assertContains(Length::class, $ruleTypes);
    }

    public function testModelEmailRulesContainRequiredAndEmail(ModelTester $I): void
    {
        $I->wantTo('verify email property has Required and Email rules');

        $model = new SimpleBleetModel();
        $rules = $model->getRules();

        $emailRules = $rules['email'];
        $I->assertCount(2, $emailRules);

        $ruleTypes = array_map(fn($rule) => $rule::class, $emailRules);
        $I->assertContains(Required::class, $ruleTypes);
        $I->assertContains(Email::class, $ruleTypes);
    }

    public function testModelLengthRuleHasCorrectMinMax(ModelTester $I): void
    {
        $I->wantTo('verify Length rule has correct min and max values');

        $model = new SimpleBleetModel();
        $rules = $model->getRules();

        $nameRules = $rules['name'];
        $lengthRule = null;
        foreach ($nameRules as $rule) {
            if ($rule instanceof Length) {
                $lengthRule = $rule;
                break;
            }
        }

        $I->assertNotNull($lengthRule);
        $I->assertSame(2, $lengthRule->getMin());
        $I->assertSame(100, $lengthRule->getMax());
    }

    public function testModelWithoutRulesReturnsEmptyIterable(ModelTester $I): void
    {
        $I->wantTo('verify model without rules() override returns empty iterable');

        $model = new EmptyRulesBleetModel();
        $rules = $model->getRules();

        $I->assertIsIterable($rules);
        $I->assertEmpty($rules);
    }

    public function testModelWithoutRulesValidationAlwaysPasses(ModelTester $I): void
    {
        $I->wantTo('verify model without rules always passes validation');

        $model = new EmptyRulesBleetModel();
        $model->name = '';
        $model->email = 'invalid';

        $I->assertTrue($model->validate());
    }

    public function testModelValidationPasses(ModelTester $I): void
    {
        $I->wantTo('verify model validation passes with valid data');

        $model = new SimpleBleetModel();
        $model->name = 'Jean Dupont';
        $model->email = 'jean@example.com';

        $I->assertTrue($model->validate());
        $I->assertTrue($model->isValid());
    }

    public function testModelValidationFailsWithEmptyName(ModelTester $I): void
    {
        $I->wantTo('verify model validation fails with empty name');

        $model = new SimpleBleetModel();
        $model->name = '';
        $model->email = 'jean@example.com';

        $I->assertFalse($model->validate());
        $I->assertFalse($model->isValid());
    }

    public function testModelValidationFailsWithInvalidEmail(ModelTester $I): void
    {
        $I->wantTo('verify model validation fails with invalid email');

        $model = new SimpleBleetModel();
        $model->name = 'Jean Dupont';
        $model->email = 'not-an-email';

        $I->assertFalse($model->validate());
        $I->assertFalse($model->isValid());
    }

    public function testModelValidationFailsWithMultipleErrors(ModelTester $I): void
    {
        $I->wantTo('verify model validation collects multiple errors');

        $model = new SimpleBleetModel();
        $model->name = '';
        $model->email = 'invalid';

        $I->assertFalse($model->validate());
        $I->assertFalse($model->isValid());
    }

    public function testModelLabelsHintsPlaceholders(ModelTester $I): void
    {
        $I->wantTo('verify model provides labels, hints and placeholders');

        $model = new SimpleBleetModel();

        $I->assertSame('Nom complet', $model->getPropertyLabel('name'));
        $I->assertSame('Adresse email', $model->getPropertyLabel('email'));

        $I->assertSame('Votre nom et prénom', $model->getPropertyHint('name'));
        $I->assertSame('Un email valide est requis', $model->getPropertyHint('email'));

        $I->assertSame('Jean Dupont', $model->getPropertyPlaceholder('name'));
        $I->assertSame('jean@example.com', $model->getPropertyPlaceholder('email'));
    }

    public function testModelLoadWithCorrectScope(ModelTester $I): void
    {
        $I->wantTo('verify model load() populates data with correct scope');

        $model = new SimpleBleetModel();

        $data = [
            'SimpleBleetModel' => [
                'name' => 'Philippe Gaultier',
                'email' => 'pgaultier@gmail.com',
            ],
        ];

        $result = $model->load($data);

        $I->assertTrue($result);
        $I->assertSame('Philippe Gaultier', $model->name);
        $I->assertSame('pgaultier@gmail.com', $model->email);
    }

    public function testModelLoadWithWrongScopeFails(ModelTester $I): void
    {
        $I->wantTo('verify model load() fails with wrong scope');

        $model = new SimpleBleetModel();

        $data = [
            'WrongModel' => [
                'name' => 'Philippe Gaultier',
                'email' => 'pgaultier@gmail.com',
            ],
        ];

        $result = $model->load($data);

        $I->assertFalse($result);
        $I->assertSame('', $model->name);
        $I->assertSame('', $model->email);
    }

    public function testModelLoadAndValidate(ModelTester $I): void
    {
        $I->wantTo('verify model load() and validate() work together');

        $model = new SimpleBleetModel();

        $data = [
            'SimpleBleetModel' => [
                'name' => 'Philippe Gaultier',
                'email' => 'pgaultier@gmail.com',
            ],
        ];

        $loaded = $model->load($data);
        $I->assertTrue($loaded);

        $I->assertTrue($model->validate());
        $I->assertTrue($model->isValid());
    }

    public function testModelLoadWithInvalidDataFailsValidation(ModelTester $I): void
    {
        $I->wantTo('verify model load() with invalid data fails validation');

        $model = new SimpleBleetModel();

        $data = [
            'SimpleBleetModel' => [
                'name' => '',
                'email' => 'not-an-email',
            ],
        ];

        $loaded = $model->load($data);
        $I->assertTrue($loaded);

        $I->assertFalse($model->validate());
        $I->assertFalse($model->isValid());
    }
}
