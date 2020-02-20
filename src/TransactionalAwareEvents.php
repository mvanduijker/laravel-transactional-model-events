<?php

namespace MVanDuijker\TransactionalModelEvents;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;

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
                if ($model->getConnection()->transactionLevel()) {
                    self::$queuedTransactionalEvents[$model->getConnectionName()][$event][] = $model;
                } else {
                    // auto fire the afterCommit callback when we are not in a transaction
                    $model->fireModelEvent('afterCommit.' . $event);
                    $model->fireModelEvent('afterCommit' . ucfirst($event));
                }
            });
        }

        $dispatcher->listen(TransactionCommitted::class, function (TransactionCommitted $event) {
            if ($event->connection->transactionLevel() > 0) {
                return;
            }

            foreach ((self::$queuedTransactionalEvents[$event->connectionName] ?? []) as $eventName => $models) {
                /** @var Model $model */
                foreach ($models as $model) {
                    $model->fireModelEvent('afterCommit.' . $eventName);
                    $model->fireModelEvent('afterCommit' . ucfirst($eventName));
                }
            }
            self::$queuedTransactionalEvents[$event->connectionName] = [];
        });

        $dispatcher->listen(TransactionRolledBack::class, function (TransactionRolledBack $event) {
            if ($event->connection->transactionLevel() > 0) {
                return;
            }

            foreach ((self::$queuedTransactionalEvents[$event->connectionName] ?? []) as $eventName => $models) {
                /** @var Model $model */
                foreach ($models as $model) {
                    $model->fireModelEvent('afterRollback.' . $eventName);
                    $model->fireModelEvent('afterRollback' . ucfirst($eventName));
                }
            }
            self::$queuedTransactionalEvents[$event->connectionName] = [];
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
