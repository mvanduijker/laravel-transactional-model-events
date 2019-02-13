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
        static::created(function (Model $model) {
            if (DB::transactionLevel()) {
                self::$queuedTransactionalEvents['created'][] = $model;
            }
        });

        static::saved(function (Model $model) {
            if (DB::transactionLevel()) {
                self::$queuedTransactionalEvents['saved'][] = $model;
            }
        });

        static::updated(function (Model $model) {
            if (DB::transactionLevel()) {
                self::$queuedTransactionalEvents['updated'][] = $model;
            }
        });

        static::deleted(function (Model $model) {
            if (DB::transactionLevel()) {
                self::$queuedTransactionalEvents['deleted'][] = $model;
            }
        });

        static::getEventDispatcher()->listen(TransactionCommitted::class, function () {
            foreach (self::$queuedTransactionalEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $model->fireModelEvent('after_commit_' . $eventName);
                }
            }
            self::$queuedTransactionalEvents = [];
        });

        static::getEventDispatcher()->listen(TransactionRolledBack::class, function () {
            foreach (self::$queuedTransactionalEvents as $eventName => $models) {
                foreach ($models as $model) {
                    $model->fireModelEvent('after_rollback_' . $eventName);
                }
            }
            self::$queuedTransactionalEvents = [];
        });
    }

    public static function afterCommitCreated($callback)
    {
        static::registerModelEvent('after_commit_created', $callback);
    }

    public static function afterCommitSaved($callback)
    {
        static::registerModelEvent('after_commit_saved', $callback);
    }

    public static function afterCommitUpdated($callback)
    {
        static::registerModelEvent('after_commit_updated', $callback);
    }

    public static function afterCommitDeleted($callback)
    {
        static::registerModelEvent('after_commit_deleted', $callback);
    }

    public static function afterRollbackCreated($callback)
    {
        static::registerModelEvent('after_rollback_created', $callback);
    }

    public static function afterRollbackSaved($callback)
    {
        static::registerModelEvent('after_rollback_saved', $callback);
    }

    public static function afterRollbackUpdated($callback)
    {
        static::registerModelEvent('after_rollback_updated', $callback);
    }

    public static function afterRollbackDeleted($callback)
    {
        static::registerModelEvent('after_rollback_deleted', $callback);
    }
}
