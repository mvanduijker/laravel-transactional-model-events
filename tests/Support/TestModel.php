<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Support;

use Illuminate\Database\Eloquent\Model;
use MVanDuijker\TransactionalModelEvents\TransactionalAwareEvents;

class TestModel extends Model
{
    use TransactionalAwareEvents;

    protected $table = 'test_models';
    protected $guarded = [];
    public $timestamps = false;
}
