<?php

namespace MVanDuijker\TransactionalModelEvents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;

trait TransactionalAwareEvents
{
    protected static $queuedTransactionalEvents = [];

    public static function bootTransactionalAwareEvents()
    {
        $eloquentEvents = [
            'created', 'updated', 'saved', 'restored',
            'deleted', 'forceDeleted',
        ];

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

        static::getEventDispatcher()->listen(TransactionCommitted::class, function () {
            foreach (self::$queuedTransactionalEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $model->fireModelEvent('afterCommit.' . $eventName);
                }
            }
            self::$queuedTransactionalEvents = [];
        });

        static::getEventDispatcher()->listen(TransactionRolledBack::class, function () {
            foreach (self::$queuedTransactionalEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $model->fireModelEvent('afterRollback.' . $eventName);
                }
            }
            self::$queuedTransactionalEvents = [];
        });
    }
}
