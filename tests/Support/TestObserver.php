<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Support;

class TestObserver
{
    public function afterCommitSaved(TestModel $testModel)
    {
        $testModel->observer_call = true;
    }

    public function afterRollbackSaved(TestModel $testModel)
    {
        $testModel->observer_call = true;
    }
}
