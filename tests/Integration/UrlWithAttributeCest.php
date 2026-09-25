<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Integration;

use Blackcube\Bleet\Tests\Support\DatabaseCestTrait;
use Blackcube\Bleet\Tests\Support\IntegrationTester;
use Blackcube\Bleet\Tests\Support\UrlWithAttribute;

class UrlWithAttributeCest
{
    use DatabaseCestTrait;

    public function testTableCreatedWithoutSchemaTable(IntegrationTester $I): void
    {
        $I->wantTo('verify table is created without needing elasticSchemas table');

        $url = new UrlWithAttribute();
        $url->setName('Test');
        $url->url = 'https://test.example.com';

        $schema = $url->getSchema();
        $I->assertNotNull($schema);

        $properties = $schema->getProperties();
        $I->assertNotNull($properties);
    }

    public function testInsertUrlsWithElasticProperties(IntegrationTester $I): void
    {
        $I->wantTo('insert several urls with elastic properties (no elasticSchemaId)');

        $url1 = new UrlWithAttribute();
        $url1->setName('Google');
        $url1->url = 'https://www.google.com';
        $url1->insert();

        $url2 = new UrlWithAttribute();
        $url2->setName('GitHub');
        $url2->url = 'https://github.com';
        $url2->insert();

        $url3 = new UrlWithAttribute();
        $url3->setName('Blackcube');
        $url3->url = 'https://www.blackcube.io';
        $url3->insert();

        $allUrls = UrlWithAttribute::query()->all();
        $I->assertCount(3, $allUrls);

        $loaded = UrlWithAttribute::query()->where(['id' => $url1->getId()])->one();
        $I->assertNotNull($loaded);
        $I->assertEquals('Google', $loaded->getName());
        $I->assertEquals('https://www.google.com', $loaded->url);
    }

    public function testValidationPassesWithValidHttpsUrl(IntegrationTester $I): void
    {
        $I->wantTo('verify validation passes with valid https url using $model->validate()');

        $url = new UrlWithAttribute();
        $url->setName('Valid Site');
        $url->url = 'https://valid.example.com';

        $result = $url->validate();

        $I->assertTrue($result->isValid());
    }

    public function testValidationFailsWithHttpUrl(IntegrationTester $I): void
    {
        $I->wantTo('verify validation fails with http url using $model->validate()');

        $url = new UrlWithAttribute();
        $url->setName('Invalid Site');
        $url->url = 'http://invalid.example.com';

        $result = $url->validate();

        $I->assertFalse($result->isValid());
        $I->assertNotEmpty($result->getErrors());
    }

    public function testValidationFailsWithUrlTooShort(IntegrationTester $I): void
    {
        $I->wantTo('verify validation fails with url too short using $model->validate()');

        $url = new UrlWithAttribute();
        $url->setName('Short');
        $url->url = 'https:/';

        $result = $url->validate();

        $I->assertFalse($result->isValid());
    }

    public function testValidationFailsWithEmptyUrl(IntegrationTester $I): void
    {
        $I->wantTo('verify validation fails with empty url using $model->validate()');

        $url = new UrlWithAttribute();
        $url->setName('Empty');

        $result = $url->validate();

        $I->assertFalse($result->isValid());
    }

    public function testSaveOnlyIfValid(IntegrationTester $I): void
    {
        $I->wantTo('verify model is saved only if validation passes using $model->validate()');

        $validUrl = new UrlWithAttribute();
        $validUrl->setName('Valid');
        $validUrl->url = 'https://valid.test.com';

        if ($validUrl->validate()->isValid()) {
            $validUrl->insert();
        }

        $saved = UrlWithAttribute::query()->where(['name' => 'Valid'])->one();
        $I->assertNotNull($saved);
        $I->assertEquals('https://valid.test.com', $saved->url);

        $invalidUrl = new UrlWithAttribute();
        $invalidUrl->setName('Invalid');
        $invalidUrl->url = 'http://invalid.test.com';

        if ($invalidUrl->validate()->isValid()) {
            $invalidUrl->insert();
        }

        $notSaved = UrlWithAttribute::query()->where(['name' => 'Invalid'])->one();
        $I->assertNull($notSaved);
    }

    public function testElasticQueryWithLikeOnVirtualColumn(IntegrationTester $I): void
    {
        $I->wantTo('verify elastic query works with LIKE on virtual url column (attribute-based)');

        $url1 = new UrlWithAttribute();
        $url1->setName('First');
        $url1->url = 'https://www.google.com';
        $url1->insert();

        $url2 = new UrlWithAttribute();
        $url2->setName('Second');
        $url2->url = 'https://urltest.example.com';
        $url2->insert();

        $url3 = new UrlWithAttribute();
        $url3->setName('Third');
        $url3->url = 'https://another-urltest-site.com';
        $url3->insert();

        $results = UrlWithAttribute::query()
            ->andWhere(['like', 'url', 'urltest'])
            ->all();

        $I->assertCount(2, $results);

        $names = array_map(fn($u) => $u->getName(), $results);
        $I->assertContains('Second', $names);
        $I->assertContains('Third', $names);
        $I->assertNotContains('First', $names);
    }
}
