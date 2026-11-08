<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $connection = $app['db']->connection();
        if ($connection->getDriverName() !== 'pgsql' || ! preg_match('/(?:^|_)test(?:_|$)/', $connection->getDatabaseName())) {
            throw new RuntimeException('Feature tests require a separate PostgreSQL database with "test" in its name, such as kamali_test.');
        }

        return $app;
    }
}
