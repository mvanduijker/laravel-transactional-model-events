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
        Event::assertNotDispatched('eloquent.after_commit_created: ' . TestModel::class);
        DB::commit();

        Event::assertDispatched('eloquent.after_commit_created: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_saved()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test saved']);
        Event::assertNotDispatched('eloquent.after_commit_saved: ' . TestModel::class);
        DB::commit();

        Event::assertDispatched('eloquent.after_commit_saved: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_updated()
    {
        $this->fakeTransactionalEvents(TestModel::class);
        $model = TestModel::create(['name' => 'test saved']);

        DB::beginTransaction();
        $model->update(['name' => 'new name']);
        Event::assertNotDispatched('eloquent.after_commit_updated: ' . TestModel::class);
        DB::commit();

        Event::assertDispatched('eloquent.after_commit_updated: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_deleted()
    {
        $this->fakeTransactionalEvents(TestModel::class);
        $model = TestModel::create(['name' => 'test delete']);

        DB::beginTransaction();
        $model->delete();
        Event::assertNotDispatched('eloquent.after_commit_deleted: ' . TestModel::class);
        DB::commit();

        Event::assertDispatched('eloquent.after_commit_deleted: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_created()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test create']);
        Event::assertNotDispatched('eloquent.after_rollback_created: ' . TestModel::class);
        DB::rollBack();

        Event::assertDispatched('eloquent.after_rollback_created: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_saved()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test saved']);
        Event::assertNotDispatched('eloquent.after_rollback_saved: ' . TestModel::class);
        DB::rollBack();

        Event::assertDispatched('eloquent.after_rollback_saved: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_updated()
    {
        $this->fakeTransactionalEvents(TestModel::class);
        $model = TestModel::create(['name' => 'test saved']);

        DB::beginTransaction();
        $model->update(['name' => 'new name']);
        Event::assertNotDispatched('eloquent.after_rollback_updated: ' . TestModel::class);
        DB::rollBack();

        Event::assertDispatched('eloquent.after_rollback_updated: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_deleted()
    {
        $this->fakeTransactionalEvents(TestModel::class);
        $model = TestModel::create(['name' => 'test delete']);

        DB::beginTransaction();
        $model->delete();
        Event::assertNotDispatched('eloquent.after_rollback_deleted: ' . TestModel::class);
        DB::rollBack();

        Event::assertDispatched('eloquent.after_rollback_deleted: ' . TestModel::class);
    }

    public function test_it_fires_with_multiple_models()
    {
        $this->fakeTransactionalEvents(TestModel::class);

        DB::beginTransaction();
        TestModel::create(['name' => 'test create first']);
        TestModel::create(['name' => 'test create second']);
        DB::rollBack();

        Event::assertDispatchedTimes('eloquent.after_rollback_created: ' . TestModel::class, 2);
    }

    private function fakeTransactionalEvents($className)
    {
        Event::fake(array_map(function ($eventName) use ($className) {
            return "eloquent.$eventName: " . $className;
        }, $this->transactionalEvents()));
    }
}
