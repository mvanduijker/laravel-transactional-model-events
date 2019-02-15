<?php

namespace MVanDuijker\TransactionalModelEvents;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;

class TransactionalModelEventSubscriber
{
    /** @var array */
    protected $recordedEvents = [];

    public function subscribe(Dispatcher $events)
    {
        foreach (['created', 'updated', 'saved', 'restored','deleted', 'forceDeleted'] as $eventName) {
            $events->listen("eloquent.$eventName: *", function ($_, $args) use ($eventName) {
                if (DB::transactionLevel()) {
                    $this->recordedEvents[$eventName][] = $args[0];
                }
            });
        }

        $events->listen(TransactionCommitted::class, function () use ($events) {
            foreach ($this->recordedEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $events->until("eloquent.afterCommit.{$eventName}: " . get_class($model));
                }
            }
            $this->recordedEvents = [];
        });

        $events->listen(TransactionRolledBack::class, function () use ($events) {
            foreach ($this->recordedEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $events->until("eloquent.afterRollback.{$eventName}: " . get_class($model));
                }
            }
            $this->recordedEvents = [];
        });
    }
}
