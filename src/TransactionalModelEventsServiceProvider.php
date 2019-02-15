<?php

namespace MVanDuijker\TransactionalModelEvents;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class TransactionalModelEventsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::subscribe(TransactionalModelEventSubscriber::class);
    }
}
