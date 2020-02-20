<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Feauture;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use MVanDuijker\TransactionalModelEvents\Tests\Support\TestModel;
use MVanDuijker\TransactionalModelEvents\Tests\Support\TestObserver;
use MVanDuijker\TransactionalModelEvents\Tests\TestCase;

class TransactionalAwareEventsTest extends TestCase
{
    private $recordedEvents = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->recordedEvents = [];
    }

    /** @test */
    public function it_fires_commit_created()
    {
        $this->recordEvents();

        DB::beginTransaction();
        TestModel::create(['name' => 'test create']);
        $this->assertNotDispatched('eloquent.afterCommit.created: ' . TestModel::class);
        DB::commit();

        $this->assertDispatched('eloquent.afterCommit.created: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_saved()
    {
        $this->recordEvents();

        DB::beginTransaction();
        TestModel::create(['name' => 'test saved']);
        $this->assertNotDispatched('eloquent.afterCommit.saved: ' . TestModel::class);
        DB::commit();

        $this->assertDispatched('eloquent.afterCommit.saved: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_updated()
    {
        $this->recordEvents();
        $model = TestModel::create(['name' => 'test saved']);

        DB::beginTransaction();
        $model->update(['name' => 'new name']);
        $this->assertNotDispatched('eloquent.afterCommit.updated: ' . TestModel::class);
        DB::commit();

        $this->assertDispatched('eloquent.afterCommit.updated: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_commit_deleted()
    {
        $this->recordEvents();
        $model = TestModel::create(['name' => 'test delete']);

        DB::beginTransaction();
        $model->delete();
        $this->assertNotDispatched('eloquent.afterCommit.deleted: ' . TestModel::class);
        DB::commit();

        $this->assertDispatched('eloquent.afterCommit.deleted: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_created()
    {
        $this->recordEvents();

        DB::beginTransaction();
        TestModel::create(['name' => 'test create']);
        $this->assertNotDispatched('eloquent.afterRollback.created: ' . TestModel::class);
        DB::rollBack();

        $this->assertDispatched('eloquent.afterRollback.created: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_saved()
    {
        $this->recordEvents();

        DB::beginTransaction();
        TestModel::create(['name' => 'test saved']);
        $this->assertNotDispatched('eloquent.afterRollback.saved: ' . TestModel::class);
        DB::rollBack();

        $this->assertDispatched('eloquent.afterRollback.saved: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_updated()
    {
        $this->recordEvents();
        $model = TestModel::create(['name' => 'test saved']);

        DB::beginTransaction();
        $model->update(['name' => 'new name']);
        $this->assertNotDispatched('eloquent.afterRollback.updated: ' . TestModel::class);
        DB::rollBack();

        $this->assertDispatched('eloquent.afterRollback.updated: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_rollback_deleted()
    {
        $this->recordEvents();
        $model = TestModel::create(['name' => 'test delete']);

        DB::beginTransaction();
        $model->delete();
        $this->assertNotDispatched('eloquent.afterRollback.deleted: ' . TestModel::class);
        DB::rollBack();

        $this->assertDispatched('eloquent.afterRollback.deleted: ' . TestModel::class);
    }

    /** @test */
    public function it_fires_with_multiple_models()
    {
        $this->recordEvents();

        DB::beginTransaction();
        TestModel::create(['name' => 'test create first']);
        TestModel::create(['name' => 'test create second']);
        DB::rollBack();

        $this->assertDispatchedTimes('eloquent.afterRollback.created: ' . TestModel::class, 2);
    }

    /** @test */
    public function it_can_observe_events_on_commit()
    {
        TestModel::observe(TestObserver::class);

        DB::beginTransaction();
        $model = TestModel::create(['name' => 'test create first']);
        $this->assertEmpty($model->observer_call);
        DB::commit();

        $this->assertTrue($model->observer_call);
    }

    /** @test */
    public function it_can_observe_events_on_rollback()
    {
        TestModel::observe(TestObserver::class);

        DB::beginTransaction();
        $model = TestModel::create(['name' => 'test create first']);
        $this->assertEmpty($model->observer_call);
        DB::rollback();

        $this->assertTrue($model->observer_call);
    }

    /** @test */
    public function it_can_handle_multiple_connections()
    {
        $this->recordEvents();

        DB::connection('other')->beginTransaction();

        /** @var TestModel $testModel */
        $testModel = TestModel::make(['name' => 'test create']);
        $testModel->setConnection('other');
        $testModel->save();

        $this->assertNotDispatched('eloquent.afterCommit.created: ' . TestModel::class);

        DB::commit();
        $this->assertNotDispatched('eloquent.afterCommit.created: ' . TestModel::class);

        DB::connection('other')->commit();
        $this->assertDispatched('eloquent.afterCommit.created: ' . TestModel::class);
    }

    private function recordEvents()
    {
        Event::listen('eloquent.*', function ($eventName, $_) {
            $this->recordedEvents[] = $eventName;
        });
    }

    private function assertDispatched($event)
    {
        $this->assertTrue(in_array($event, $this->recordedEvents), "$event not dispatched");
    }

    private function assertNotDispatched($event)
    {
        $this->assertFalse(in_array($event, $this->recordedEvents), "$event dispatched");
    }

    private function assertDispatchedTimes($event, $times = 1)
    {
        $this->assertCount($times, array_filter($this->recordedEvents, function ($recordedEvent) use ($event) {
            return $recordedEvent === $event;
        }));
    }
}
