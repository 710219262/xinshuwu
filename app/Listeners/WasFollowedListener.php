<?php


namespace App\Listeners;

use App\Events\User\UserWasFollowed;
use App\Listeners\Traits\CreateNotice;
use Illuminate\Contracts\Queue\ShouldQueue;

class WasFollowedListener implements ShouldQueue
{
    use CreateNotice;
    
    /**
     * @param $model UserWasFollowed
     */
    public function handle($model)
    {
        $this->notify($model);
    }
}
