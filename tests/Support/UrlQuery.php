<?php

declare(strict_types=1);

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\ActiveRecord\Elastic\BaseElasticQueryTrait;
use Blackcube\ActiveRecord\Elastic\ElasticQueryInterface;
use Blackcube\ActiveRecord\QualifyColumnTrait;
use Blackcube\ActiveRecord\BuildFormulaeTrait;
use Blackcube\ActiveRecord\Elastic\BuildElasticFormulaeTrait;
use Blackcube\ActiveRecord\FormulaeExpressionInterface;
use Yiisoft\ActiveRecord\ActiveQuery;

class BaseUrlQuery extends ActiveQuery
{
    use QualifyColumnTrait;
    use BuildFormulaeTrait;
}

class UrlQuery extends BaseUrlQuery implements ElasticQueryInterface, FormulaeExpressionInterface
{
    use BaseElasticQueryTrait;
    use BuildElasticFormulaeTrait;
}
