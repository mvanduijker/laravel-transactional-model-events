<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Support;

class TestObserver
{
    public function afterCommitCreated(TestModel $testModel)
    {
        $testModel->observer_call_created = true;
    }

    public function afterCommitSaved(TestModel $testModel)
    {
        $testModel->observer_call_saved = true;
    }

    public function afterRollbackSaved(TestModel $testModel)
    {
        $testModel->observer_call_rollback_saved = true;
    }
}
