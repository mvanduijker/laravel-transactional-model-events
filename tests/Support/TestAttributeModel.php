<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Support;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use MVanDuijker\TransactionalModelEvents\TransactionalAwareEvents;

#[ObservedBy(TestObserver::class)]
class TestAttributeModel extends Model
{
    use TransactionalAwareEvents;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;
}
