<?php

namespace MVanDuijker\TransactionalModelEvents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;

trait TransactionalAwareEvents
{
    protected static $transactionalEloquentEvents = [
        'created', 'updated', 'saved', 'restored',
        'deleted', 'forceDeleted',
    ];

    /**
     * @var Model[]
     */
    protected static $queuedTransactionalEvents = [];

    public static function bootTransactionalAwareEvents()
    {
        $dispatcher = static::getEventDispatcher();

        if (!$dispatcher) {
            // dispatcher probably unset in tests, we can safely bail out
            return;
        }

        foreach (self::$transactionalEloquentEvents as $event) {
            static::registerModelEvent($event, function (Model $model) use ($event) {
                if (DB::transactionLevel()) {
                    self::$queuedTransactionalEvents[$event][] = $model;
                } else {
                    // auto fire the afterCommit callback when we are not in a transaction
                    $model->fireModelEvent('afterCommit.' . $event);
                    $model->fireModelEvent('afterCommit' . ucfirst($event));
                }
            });
        }

        $dispatcher->listen(TransactionCommitted::class, function () use ($dispatcher) {
            if (DB::transactionLevel() > 0) {
                return;
            }

            foreach (self::$queuedTransactionalEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $model->fireModelEvent('afterCommit.' . $eventName);
                    $model->fireModelEvent('afterCommit' . ucfirst($eventName));
                }
            }
            self::$queuedTransactionalEvents = [];
        });

        $dispatcher->listen(TransactionRolledBack::class, function () {
            if (DB::transactionLevel() > 0) {
                return;
            }

            foreach (self::$queuedTransactionalEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $model->fireModelEvent('afterRollback.' . $eventName);
                    $model->fireModelEvent('afterRollback' . ucfirst($eventName));
                }
            }
            self::$queuedTransactionalEvents = [];
        });
    }

    public function initializeTransactionalAwareEvents()
    {
        foreach (self::$transactionalEloquentEvents as $eloquentEvent) {
            $this->addObservableEvents('afterCommit' . ucfirst($eloquentEvent));
            $this->addObservableEvents('afterRollback' . ucfirst($eloquentEvent));
        }
    }
}
