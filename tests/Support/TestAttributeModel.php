<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Support;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use MVanDuijker\TransactionalModelEvents\TransactionalAwareEvents;

#[ObservedBy(TestObserver::class)]
class TestAttributeModel extends TestModel
{
    use TransactionalAwareEvents;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;
}
