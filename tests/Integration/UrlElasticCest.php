<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Integration;

use Blackcube\Bleet\Tests\Support\DatabaseCestTrait;
use Blackcube\Bleet\Tests\Support\IntegrationTester;
use Blackcube\Bleet\Tests\Support\Url;
use Blackcube\ActiveRecord\Elastic\ElasticSchema;
use Blackcube\ActiveRecord\Elastic\Validator\ElasticRuleResolver;
use Yiisoft\Validator\Validator;

class UrlElasticCest
{
    use DatabaseCestTrait;

    private int $schemaId = 0;

    private static bool $dataSeeded = false;

    private function ensureTestData(): void
    {
        if (self::$dataSeeded) {
            return;
        }

        $schema = new ElasticSchema();
        $schema->setName('UrlSchema');
        $schema->setSchema(json_encode([
            'type' => 'object',
            'properties' => [
                'url' => [
                    'title' => 'Url externe',
                    'type' => 'string',
                    'pattern' => '^https',
                    'minLength' => 8,
                    'description' => 'Url au format https://xxx.yyy.com',
                ],
            ],
            'required' => ['url'],
        ]));
        $schema->save();
        $this->schemaId = $schema->getId();

        self::$dataSeeded = true;
    }

    public function testTablesAndSchemaCreated(IntegrationTester $I): void
    {
        $this->ensureTestData();
        $I->wantTo('verify tables are created and schema is inserted');

        // Verify elasticSchemas table has our schema
        $schema = ElasticSchema::query()->where(['id' => $this->schemaId])->one();

        $I->assertNotNull($schema);
        $I->assertEquals('UrlSchema', $schema->getName());

        // Verify schema content
        $decoded = json_decode($schema->getSchema(), true);
        $I->assertArrayHasKey('properties', $decoded);
        $I->assertArrayHasKey('url', $decoded['properties']);
        $I->assertEquals('string', $decoded['properties']['url']['type']);
        $I->assertEquals('^https', $decoded['properties']['url']['pattern']);
        $I->assertEquals(8, $decoded['properties']['url']['minLength']);
        $I->assertContains('url', $decoded['required']);
    }

    public function testInsertUrlsWithElasticProperties(IntegrationTester $I): void
    {
        $this->ensureTestData();
        $I->wantTo('insert several urls with elastic properties');

        // Insert url 1
        $url1 = new Url();
        $url1->setName('Google');
        $url1->elasticSchemaId = $this->schemaId;
        $url1->url = 'https://www.google.com';
        $url1->insert();

        // Insert url 2
        $url2 = new Url();
        $url2->setName('GitHub');
        $url2->elasticSchemaId = $this->schemaId;
        $url2->url = 'https://github.com';
        $url2->insert();

        // Insert url 3
        $url3 = new Url();
        $url3->setName('Blackcube');
        $url3->elasticSchemaId = $this->schemaId;
        $url3->url = 'https://www.blackcube.io';
        $url3->insert();

        // Insert url 4 - for testing query later
        $url4 = new Url();
        $url4->setName('Urltest Site');
        $url4->elasticSchemaId = $this->schemaId;
        $url4->url = 'https://urltest.example.com';
        $url4->insert();

        // Verify all urls are inserted
        $allUrls = Url::query()->all();
        $I->assertCount(4, $allUrls);

        // Verify elastic properties are stored
        $loaded = Url::query()->where(['id' => $url1->getId()])->one();
        $I->assertNotNull($loaded);
        $I->assertEquals('Google', $loaded->getName());
        $I->assertEquals('https://www.google.com', $loaded->url);

        // Verify url4 for query test later
        $loaded4 = Url::query()->where(['id' => $url4->getId()])->one();
        $I->assertEquals('https://urltest.example.com', $loaded4->url);
    }

    public function testValidationPassesWithValidHttpsUrl(IntegrationTester $I): void
    {
        $this->ensureTestData();
        $I->wantTo('verify validation passes with valid https url');

        $url = new Url();
        $url->setName('Valid Site');
        $url->elasticSchemaId = $this->schemaId;
        $url->url = 'https://valid.example.com';

        // Get rules from elastic schema
        $resolver = new ElasticRuleResolver();
        $rules = $resolver->resolve($url);

        // Validate
        $validator = new Validator();
        $result = $validator->validate($url->getElasticValues(), $rules);

        $I->assertTrue($result->isValid());
    }

    public function testValidationFailsWithHttpUrl(IntegrationTester $I): void
    {
        $this->ensureTestData();
        $I->wantTo('verify validation fails with http url (not https)');

        $url = new Url();
        $url->setName('Invalid Site');
        $url->elasticSchemaId = $this->schemaId;
        $url->url = 'http://invalid.example.com';

        $resolver = new ElasticRuleResolver();
        $rules = $resolver->resolve($url);

        $validator = new Validator();
        $result = $validator->validate($url->getElasticValues(), $rules);

        $I->assertFalse($result->isValid());
        $I->assertNotEmpty($result->getErrors());
    }

    public function testValidationFailsWithUrlTooShort(IntegrationTester $I): void
    {
        $this->ensureTestData();
        $I->wantTo('verify validation fails with url too short (minLength: 8)');

        $url = new Url();
        $url->setName('Short');
        $url->elasticSchemaId = $this->schemaId;
        $url->url = 'https:/';  // 7 chars, minLength is 8

        $resolver = new ElasticRuleResolver();
        $rules = $resolver->resolve($url);

        $validator = new Validator();
        $result = $validator->validate($url->getElasticValues(), $rules);

        $I->assertFalse($result->isValid());
    }

    public function testValidationFailsWithEmptyUrl(IntegrationTester $I): void
    {
        $this->ensureTestData();
        $I->wantTo('verify validation fails with empty url (required)');

        $url = new Url();
        $url->setName('Empty');
        $url->elasticSchemaId = $this->schemaId;
        // url is not set - should fail required validation

        $resolver = new ElasticRuleResolver();
        $rules = $resolver->resolve($url);

        $validator = new Validator();
        $result = $validator->validate($url->getElasticValues(), $rules);

        $I->assertFalse($result->isValid());
    }

    public function testSaveOnlyIfValid(IntegrationTester $I): void
    {
        $this->ensureTestData();
        $I->wantTo('verify model is saved only if validation passes');

        // Valid url
        $validUrl = new Url();
        $validUrl->setName('Valid');
        $validUrl->elasticSchemaId = $this->schemaId;
        $validUrl->url = 'https://valid.test.com';

        $resolver = new ElasticRuleResolver();
        $rules = $resolver->resolve($validUrl);
        $validator = new Validator();
        $result = $validator->validate($validUrl->getElasticValues(), $rules);

        if ($result->isValid()) {
            $validUrl->insert();
        }

        // Verify it was saved
        $saved = Url::query()->where(['name' => 'Valid'])->one();
        $I->assertNotNull($saved);
        $I->assertEquals('https://valid.test.com', $saved->url);

        // Invalid url - should not be saved
        $invalidUrl = new Url();
        $invalidUrl->setName('Invalid');
        $invalidUrl->elasticSchemaId = $this->schemaId;
        $invalidUrl->url = 'http://invalid.test.com';  // http not https

        $rules = $resolver->resolve($invalidUrl);
        $result = $validator->validate($invalidUrl->getElasticValues(), $rules);

        if ($result->isValid()) {
            $invalidUrl->insert();
        }

        // Verify it was NOT saved
        $notSaved = Url::query()->where(['name' => 'Invalid'])->one();
        $I->assertNull($notSaved);
    }

    public function testElasticQueryWithLikeOnVirtualColumn(IntegrationTester $I): void
    {
        $this->ensureTestData();
        $I->wantTo('verify elastic query works with LIKE on virtual url column');

        // First insert some urls
        $url1 = new Url();
        $url1->setName('First');
        $url1->elasticSchemaId = $this->schemaId;
        $url1->url = 'https://www.google.com';
        $url1->insert();

        $url2 = new Url();
        $url2->setName('Second');
        $url2->elasticSchemaId = $this->schemaId;
        $url2->url = 'https://urltest.example.com';
        $url2->insert();

        $url3 = new Url();
        $url3->setName('Third');
        $url3->elasticSchemaId = $this->schemaId;
        $url3->url = 'https://another-urltest-site.com';
        $url3->insert();

        // Query with LIKE on virtual column 'url' scoped to our IDs
        $ids = [$url1->getId(), $url2->getId(), $url3->getId()];
        $results = Url::query()
            ->andWhere(['id' => $ids])
            ->andWhere(['like', 'url', 'urltest'])
            ->all();

        $I->assertCount(2, $results);

        $names = array_map(fn($u) => $u->getName(), $results);
        $I->assertContains('Second', $names);
        $I->assertContains('Third', $names);
        $I->assertNotContains('First', $names);
    }
}
