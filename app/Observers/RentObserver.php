<?php

namespace App\Observers;

use App\Models\Rent;

class RentObserver
{
    /**
     * Handle the Rent "created" event.
     *
     * @param  \App\Models\Rent  $rent
     * @return void
     */
    public function created(Rent $rent)
    {
        Cache::flush();
    }

    /**
     * Handle the Rent "updated" event.
     *
     * @param  \App\Models\Rent  $rent
     * @return void
     */
    public function updated(Rent $rent)
    {
        Cache::flush();
    }

    /**
     * Handle the Rent "deleted" event.
     *
     * @param  \App\Models\Rent  $rent
     * @return void
     */
    public function deleted(Rent $rent)
    {
        Cache::flush();
    }

    /**
     * Handle the Rent "restored" event.
     *
     * @param  \App\Models\Rent  $rent
     * @return void
     */
    public function restored(Rent $rent)
    {
        Cache::flush();
    }

    /**
     * Handle the Rent "force deleted" event.
     *
     * @param  \App\Models\Rent  $rent
     * @return void
     */
    public function forceDeleted(Rent $rent)
    {
        Cache::flush();
    }
}
