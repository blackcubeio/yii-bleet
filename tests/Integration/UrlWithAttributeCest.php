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

        // Verify we can create a model without elasticSchemaId
        $url = new UrlWithAttribute();
        $url->setName('Test');
        $url->url = 'https://test.example.com';

        // Schema should be available from defaultJsonSchema
        $schema = $url->getSchema();
        $I->assertNotNull($schema);

        $properties = $schema->getProperties();
        $I->assertNotNull($properties);
    }

    public function testInsertUrlsWithElasticProperties(IntegrationTester $I): void
    {
        $I->wantTo('insert several urls with elastic properties (no elasticSchemaId)');

        // Insert url 1
        $url1 = new UrlWithAttribute();
        $url1->setName('Google');
        $url1->url = 'https://www.google.com';
        $url1->insert();

        // Insert url 2
        $url2 = new UrlWithAttribute();
        $url2->setName('GitHub');
        $url2->url = 'https://github.com';
        $url2->insert();

        // Insert url 3
        $url3 = new UrlWithAttribute();
        $url3->setName('Blackcube');
        $url3->url = 'https://www.blackcube.io';
        $url3->insert();

        // Verify all urls are inserted
        $allUrls = UrlWithAttribute::query()->all();
        $I->assertCount(3, $allUrls);

        // Verify elastic properties are stored
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

        // Simple validation using $model->validate()
        $result = $url->validate();

        $I->assertTrue($result->isValid());
    }

    public function testValidationFailsWithHttpUrl(IntegrationTester $I): void
    {
        $I->wantTo('verify validation fails with http url using $model->validate()');

        $url = new UrlWithAttribute();
        $url->setName('Invalid Site');
        $url->url = 'http://invalid.example.com';

        // Simple validation using $model->validate()
        $result = $url->validate();

        $I->assertFalse($result->isValid());
        $I->assertNotEmpty($result->getErrors());
    }

    public function testValidationFailsWithUrlTooShort(IntegrationTester $I): void
    {
        $I->wantTo('verify validation fails with url too short using $model->validate()');

        $url = new UrlWithAttribute();
        $url->setName('Short');
        $url->url = 'https:/';  // 7 chars, minLength is 8

        // Simple validation using $model->validate()
        $result = $url->validate();

        $I->assertFalse($result->isValid());
    }

    public function testValidationFailsWithEmptyUrl(IntegrationTester $I): void
    {
        $I->wantTo('verify validation fails with empty url using $model->validate()');

        $url = new UrlWithAttribute();
        $url->setName('Empty');
        // url is not set - should fail required validation

        // Simple validation using $model->validate()
        $result = $url->validate();

        $I->assertFalse($result->isValid());
    }

    public function testSaveOnlyIfValid(IntegrationTester $I): void
    {
        $I->wantTo('verify model is saved only if validation passes using $model->validate()');

        // Valid url
        $validUrl = new UrlWithAttribute();
        $validUrl->setName('Valid');
        $validUrl->url = 'https://valid.test.com';

        // Simple validation using $model->validate()
        if ($validUrl->validate()->isValid()) {
            $validUrl->insert();
        }

        // Verify it was saved
        $saved = UrlWithAttribute::query()->where(['name' => 'Valid'])->one();
        $I->assertNotNull($saved);
        $I->assertEquals('https://valid.test.com', $saved->url);

        // Invalid url - should not be saved
        $invalidUrl = new UrlWithAttribute();
        $invalidUrl->setName('Invalid');
        $invalidUrl->url = 'http://invalid.test.com';  // http not https

        // Simple validation using $model->validate()
        if ($invalidUrl->validate()->isValid()) {
            $invalidUrl->insert();
        }

        // Verify it was NOT saved
        $notSaved = UrlWithAttribute::query()->where(['name' => 'Invalid'])->one();
        $I->assertNull($notSaved);
    }

    public function testElasticQueryWithLikeOnVirtualColumn(IntegrationTester $I): void
    {
        $I->wantTo('verify elastic query works with LIKE on virtual url column (attribute-based)');

        // First insert some urls
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

        // Query with LIKE on virtual column 'url'
        $results = UrlWithAttribute::query()
            ->andWhere(['like', 'url', 'urltest'])
            ->all();

        $I->assertCount(2, $results);

        // Verify the correct urls were found
        $names = array_map(fn($u) => $u->getName(), $results);
        $I->assertContains('Second', $names);
        $I->assertContains('Third', $names);
        $I->assertNotContains('First', $names);
    }
}
