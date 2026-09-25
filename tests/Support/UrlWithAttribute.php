<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\ActiveRecord\Elastic\ElasticTrait;
use Blackcube\ActiveRecord\Elastic\Validator\ElasticRuleResolver;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveRecordInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Validator;

/**
 * Url model using ElasticTrait with inline JSON Schema.
 * Schema is defined directly in the class via $defaultJsonSchema override.
 */
class UrlWithAttribute extends ActiveRecord
{
    use ElasticTrait;

    protected int $id;
    protected string $name = '';

    private const URL_SCHEMA = <<<'JSON'
{
    "type": "object",
    "properties": {
        "url": {
            "title": "Url externe",
            "type": "string",
            "pattern": "^https",
            "minLength": 8,
            "description": "Url au format https://xxx.yyy.com"
        }
    },
    "required": ["url"]
}
JSON;

    public function __construct()
    {
        $this->defaultJsonSchema = self::URL_SCHEMA;
    }

    public static function query(ActiveRecordInterface|string|null $modelClass = null): ActiveQueryInterface
    {
        return new UrlQuery($modelClass ?? static::class);
    }

    /**
     * Validate the model's elastic properties.
     */
    public function validate(): Result
    {
        $resolver = new ElasticRuleResolver();
        $rules = $resolver->resolve($this);
        $validator = new Validator();
        return $validator->validate($this->getElasticValues(), $rules);
    }

    public function tableName(): string
    {
        return '{{%urlsWithAttribute}}';
    }

    public function getId(): ?int
    {
        return $this->id ?? null;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
