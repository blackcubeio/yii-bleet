<?php

declare(strict_types=1);

/**
 * DatabaseCestTrait.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2026 Blackcube
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace Blackcube\Bleet\Tests\Support;

use Blackcube\ActiveRecord\Elastic\Migrations\M000000000000CreateElasticSchemas;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Db\Connection\ConnectionProvider;
use Yiisoft\Db\Migration\Informer\NullMigrationInformer;
use Yiisoft\Db\Migration\MigrationBuilder;

/**
 * Trait for Cest classes that need database setup.
 *
 * Lifecycle per Cest:
 * 1. drop + create tables — once before the first test
 * 2. clear schema caches — before each test
 * 3. run all tests
 * 4. leave DB as-is
 */
trait DatabaseCestTrait
{
    protected ConnectionInterface $db;

    private static array $setupDone = [];

    public function _before(IntegrationTester $I): void
    {
        $this->initializeDatabase();

        $className = static::class;
        if (!isset(self::$setupDone[$className])) {
            $this->createTables();
            self::$setupDone[$className] = true;
        }

        Url::clearSchemaCache();
        UrlWithAttribute::clearSchemaCache();
    }

    private function initializeDatabase(): void
    {
        $helper = new MysqlHelper();
        $this->db = $helper->createConnection();
        ConnectionProvider::set($this->db);
    }

    private function createTables(): void
    {
        $this->db->createCommand('DROP TABLE IF EXISTS `urls`')->execute();
        $this->db->createCommand('DROP TABLE IF EXISTS `urlsWithAttribute`')->execute();
        $this->db->createCommand('DROP TABLE IF EXISTS `elasticSchemas`')->execute();

        $migration = new M000000000000CreateElasticSchemas();
        $builder = new MigrationBuilder($this->db, new NullMigrationInformer());
        $migration->up($builder);

        $this->db->createCommand('
            CREATE TABLE `urls` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `elasticSchemaId` INT,
                `_extras` TEXT
            )
        ')->execute();

        $this->db->createCommand('
            CREATE TABLE `urlsWithAttribute` (
                `id` INT PRIMARY KEY AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `_extras` TEXT
            )
        ')->execute();
    }
}
