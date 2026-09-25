<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\ActiveRecord\Elastic\ElasticTrait;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\ActiveRecord\ActiveRecord;
use Yiisoft\ActiveRecord\ActiveRecordInterface;

/**
 * Url model using ElasticTrait.
 */
class Url extends ActiveRecord
{
    use ElasticTrait;

    protected int $id;
    protected string $name = '';

    public static function query(ActiveRecordInterface|string|null $modelClass = null): ActiveQueryInterface
    {
        return new UrlQuery($modelClass ?? static::class);
    }

    public function tableName(): string
    {
        return '{{%urls}}';
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
