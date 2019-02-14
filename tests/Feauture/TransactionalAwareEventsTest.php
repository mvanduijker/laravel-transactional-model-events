<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Feauture;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use MVanDuijker\TransactionalModelEvents\Tests\Support\TestModel;
use MVanDuijker\TransactionalModelEvents\Tests\TestCase;

class TransactionalAwareEventsTest extends TestCase
{
    /** @test */
    public function it_fires_commit_created()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test create']);
        Event::assertNotDispatched('eloquent.afterCommit.created: ' . TestModel::class);
        DB::commit();

        Event::assertDispatched('eloquent.afterCommit.created: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_saved()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test saved']);
        Event::assertNotDispatched('eloquent.afterCommit.saved: ' . TestModel::class);
        DB::commit();

        Event::assertDispatched('eloquent.afterCommit.saved: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_updated()
    {
        $this->fakeTransactionalEvents(TestModel::class);
        $model = TestModel::create(['name' => 'test saved']);

        DB::beginTransaction();
        $model->update(['name' => 'new name']);
        Event::assertNotDispatched('eloquent.afterCommit.updated: ' . TestModel::class);
        DB::commit();

        Event::assertDispatched('eloquent.afterCommit.updated: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_deleted()
    {
        $this->fakeTransactionalEvents(TestModel::class);
        $model = TestModel::create(['name' => 'test delete']);

        DB::beginTransaction();
        $model->delete();
        Event::assertNotDispatched('eloquent.afterCommit.deleted: ' . TestModel::class);
        DB::commit();

        Event::assertDispatched('eloquent.afterCommit.deleted: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_created()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test create']);
        Event::assertNotDispatched('eloquent.afterRollback.created: ' . TestModel::class);
        DB::rollBack();

        Event::assertDispatched('eloquent.afterRollback.created: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_saved()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test saved']);
        Event::assertNotDispatched('eloquent.afterRollback.saved: ' . TestModel::class);
        DB::rollBack();

        Event::assertDispatched('eloquent.afterRollback.saved: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_updated()
    {
        $this->fakeTransactionalEvents(TestModel::class);
        $model = TestModel::create(['name' => 'test saved']);

        DB::beginTransaction();
        $model->update(['name' => 'new name']);
        Event::assertNotDispatched('eloquent.afterRollback.updated: ' . TestModel::class);
        DB::rollBack();

        Event::assertDispatched('eloquent.afterRollback.updated: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_deleted()
    {
        $this->fakeTransactionalEvents(TestModel::class);
        $model = TestModel::create(['name' => 'test delete']);

        DB::beginTransaction();
        $model->delete();
        Event::assertNotDispatched('eloquent.afterRollback.deleted: ' . TestModel::class);
        DB::rollBack();

        Event::assertDispatched('eloquent.afterRollback.deleted: ' . TestModel::class);
    }

    public function test_it_fires_with_multiple_models()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test create first']);
        TestModel::create(['name' => 'test create second']);
        DB::rollBack();

        Event::assertDispatchedTimes('eloquent.afterRollback.created: ' . TestModel::class, 2);
    }

    private function fakeTransactionalEvents($className)
    {
        Event::fake(array_map(function ($eventName) use ($className) {
            return "eloquent.$eventName: " . $className;
        }, $this->transactionalEvents()));
    }
}
