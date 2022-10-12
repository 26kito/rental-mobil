<?php

namespace App\Observers;

use App\Models\Car;
use Illuminate\Support\Facades\Cache;

class CarObserver
{
    /**
     * Handle the Car "created" event.
     *
     * @param  \App\Models\Car  $car
     * @return void
     */
    public function created(Car $car)
    {
        Cache::flush();
    }

    /**
     * Handle the Car "updated" event.
     *
     * @param  \App\Models\Car  $car
     * @return void
     */
    public function updated(Car $car)
    {
        Cache::flush();
    }

    /**
     * Handle the Car "deleted" event.
     *
     * @param  \App\Models\Car  $car
     * @return void
     */
    public function deleted(Car $car)
    {
        Cache::flush();
    }

    /**
     * Handle the Car "restored" event.
     *
     * @param  \App\Models\Car  $car
     * @return void
     */
    public function restored(Car $car)
    {
        //
    }

    /**
     * Handle the Car "force deleted" event.
     *
     * @param  \App\Models\Car  $car
     * @return void
     */
    public function forceDeleted(Car $car)
    {
        //
    }
}
