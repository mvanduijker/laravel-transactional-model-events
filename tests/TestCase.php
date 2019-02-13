<?php

namespace MVanDuijker\TransactionalModelEvents\Tests;

use Illuminate\Database\Schema\Blueprint;
use MVanDuijker\TransactionalModelEvents\Tests\Support\TestModel;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['db']->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->softDeletes();
        });
    }

    protected function createTestModel($name): TestModel
    {
        return TestModel::create(['name' => $name]);
    }

    protected function transactionalEvents()
    {
        return [
            'after_commit_created',
            'after_commit_saved',
            'after_commit_updated',
            'after_commit_deleted',
            'after_rollback_created',
            'after_rollback_saved',
            'after_rollback_updated',
            'after_rollback_deleted',
        ];
    }
}
