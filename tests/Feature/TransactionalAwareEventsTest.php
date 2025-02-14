<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use MVanDuijker\TransactionalModelEvents\Tests\Support\TestAttributeModel;
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
    public function it_can_observe_created_event_on_commit()
    {
        TestModel::observe(TestObserver::class);

        DB::beginTransaction();
        $model = TestModel::create(['name' => 'test create']);
        self::assertEmpty($model->observer_call_created);
        DB::commit();

        self::assertTrue($model->observer_call_created);
    }

    /** @test */
    public function it_can_observe_saved_event_on_commit()
    {
        TestModel::observe(TestObserver::class);

        DB::beginTransaction();
        $model = TestModel::create(['name' => 'test saved']);
        self::assertEmpty($model->observer_call_saved);
        DB::commit();

        self::assertTrue($model->observer_call_saved);
    }

    /** @test */
    public function it_can_observe_saved_event_on_rollback()
    {
        TestModel::observe(TestObserver::class);

        DB::beginTransaction();
        $model = TestModel::create(['name' => 'test saved rollback']);
        self::assertEmpty($model->observer_call_rollback_saved);
        DB::rollback();

        self::assertTrue($model->observer_call_rollback_saved);
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

    /** @test */
    public function it_can_observe_created_event_on_commit_when_null_connection_name_on_model()
    {
        TestModel::observe(TestObserver::class);

        DB::beginTransaction();
        /** @var TestModel $model */
        $model = TestModel::make(['name' => 'test create']);
        $model->setConnection(null);
        $model->save();
        self::assertEmpty($model->observer_call_created);
        DB::commit();

        self::assertTrue($model->observer_call_created);
    }

    /** @test */
    public function it_can_observed_created_event_on_commit_attribute_observer()
    {
        $this->markTestSkippedWhen(version_compare(app()->version(), '10.44.0', '<'), 'This Laravel version does not support making observers with attributes');

        DB::beginTransaction();
        $model = TestAttributeModel::create(['name' => 'test create']);
        self::assertEmpty($model->observer_call_created);
        DB::commit();

        self::assertTrue($model->observer_call_created);
    }

    private function recordEvents()
    {
        Event::listen('eloquent.*', function ($eventName, $_) {
            $this->recordedEvents[] = $eventName;
        });
    }

    private function assertDispatched($event)
    {
        self::assertTrue(in_array($event, $this->recordedEvents), "$event dispatched");
    }

    private function assertNotDispatched($event)
    {
        self::assertFalse(in_array($event, $this->recordedEvents), "$event not dispatched");
    }

    private function assertDispatchedTimes($event, $times = 1)
    {
        self::assertCount($times, array_filter($this->recordedEvents, function ($recordedEvent) use ($event) {
            return $recordedEvent === $event;
        }));
    }
}
