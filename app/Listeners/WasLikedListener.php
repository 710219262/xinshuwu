<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 16/04/2019
 * Time: 22:05
 */

namespace App\Listeners;

use App\Events\Exp\CommentWasLiked;
use App\Events\Exp\ExpWasLiked;
use App\Listeners\Traits\CreateNotice;
use Illuminate\Contracts\Queue\ShouldQueue;

class WasLikedListener implements ShouldQueue
{
    use CreateNotice;
    
    /**
     * @param $model CommentWasLiked|ExpWasLiked
     */
    public function handle($model)
    {
        $this->notify($model);
    }
}
