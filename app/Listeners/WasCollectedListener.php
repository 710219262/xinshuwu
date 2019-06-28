<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17/04/2019
 * Time: 00:16
 */

namespace App\Listeners;

use App\Events\Exp\ExpWasCollected;
use App\Listeners\Traits\CreateNotice;
use Illuminate\Contracts\Queue\ShouldQueue;

class WasCollectedListener implements ShouldQueue
{
    use CreateNotice;
    
    /**
     * @param $model ExpWasCollected
     */
    public function handle($model)
    {
        $this->notify($model);
    }
}
