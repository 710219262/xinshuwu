<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 16/04/2019
 * Time: 22:07
 */

namespace App\Listeners\Traits;

use App\Models\Notification;

trait CreateNotice
{
    /**
     * @param $model \App\Events\Contract\Notification
     */
    public function notify($model)
    {
        Notification::query()->create([
            'r_user_id'  => $model->getReceiverUserId(),
            'a_user_id'  => $model->getActionUserId(),
            'refer_id'   => $model->getReferId(),
            'jump'       => $model->getJump(),
            'action'     => $model->getAction(),
            'target'     => $model->getTarget(),
            'payload'    => $model->buildPayload(),
            'created_at' => $model->getCreatedAt(),
        ]);
        //todo umeng notification
    }
}
