<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Support;

class TestObserver
{
    public function afterCommitCreated(TestModel|TestAttributeModel $testModel)
    {
        $testModel->observer_call_created = true;
    }

    public function afterCommitSaved(TestModel|TestAttributeModel $testModel)
    {
        $testModel->observer_call_saved = true;
    }

    public function afterRollbackSaved(TestModel|TestAttributeModel $testModel)
    {
        $testModel->observer_call_rollback_saved = true;
    }
}
