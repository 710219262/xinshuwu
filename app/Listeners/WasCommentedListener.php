<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17/04/2019
 * Time: 00:18
 */

namespace App\Listeners;

use App\Events\Exp\ArticleWasCommented;
use App\Listeners\Traits\CreateNotice;
use Illuminate\Contracts\Queue\ShouldQueue;

class WasCommentedListener implements ShouldQueue
{
    use CreateNotice;
    
    /**
     * @param $model ArticleWasCommented
     */
    public function handle($model)
    {
        $this->notify($model);
    }
}
