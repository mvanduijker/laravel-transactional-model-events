<?php

namespace MVanDuijker\TransactionalModelEvents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;

trait TransactionalAwareEvents
{
    /**
     * @var Model[]
     */
    protected static $queuedTransactionalEvents = [];

    public static function bootTransactionalAwareEvents()
    {
        $eloquentEvents = [
            'created', 'updated', 'saved', 'restored',
            'deleted', 'forceDeleted',
        ];

        $dispatcher = static::getEventDispatcher();

        foreach ($eloquentEvents as $event) {
            static::registerModelEvent($event, function (Model $model) use ($event) {
                if (DB::transactionLevel()) {
                    self::$queuedTransactionalEvents[$event][] = $model;
                } else {
                    // auto fire the afterCommit callback when we are not in a transaction
                    $model->fireModelEvent('afterCommit.' . $event);
                }
            });
        }

        if (!$dispatcher) {
            // dispatcher probably unset in tests, we can safely bail out
            return;
        }

        $dispatcher->listen(TransactionCommitted::class, function () {
            foreach (self::$queuedTransactionalEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $model->fireModelEvent('afterCommit.' . $eventName);
                }
            }
            self::$queuedTransactionalEvents = [];
        });

        $dispatcher->listen(TransactionRolledBack::class, function () {
            foreach (self::$queuedTransactionalEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $model->fireModelEvent('afterRollback.' . $eventName);
                }
            }
            self::$queuedTransactionalEvents = [];
        });
    }
}
