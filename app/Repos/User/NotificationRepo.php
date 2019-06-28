<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17/04/2019
 * Time: 23:28
 */

namespace App\Repos\User;

use App\Models\Notification;
use App\Models\User;

class NotificationRepo
{
    public function list(User $user, $type)
    {
        $actions = Notification::LIST_TO_ACTION[$type];
        return Notification::query()->where('r_user_id', $user->id)
            ->whereIn('action', $actions)
            ->orderBy('created_at', 'desc')
            ->select([
                'id',
                'a_user_id',
                'r_user_id',
                'refer_id',
                'jump',
                'action',
                'target',
                'is_read',
                'payload',
                'created_at',
            ])->get();
    }
    
    public function read($id)
    {
        Notification::query()->find($id)->update(['is_read' => true]);
    }
}
