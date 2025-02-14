<?php

namespace MVanDuijker\TransactionalModelEvents\Tests\Support;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy(TestObserver::class)]
class TestAttributeModel extends TestModel
{
}
