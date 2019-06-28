<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 15/04/2019
 * Time: 22:32
 */

namespace App\Events\Exp;

use App\Events\Contract\Notification as NotificationContract;
use App\Events\Event;
use App\Models\Notification;
use App\Models\UserExpCmt;
use Carbon\Carbon;

class ExpWasCommented extends Event implements NotificationContract
{
    protected $userExpCmt;
    protected $time;
    
    public function __construct(UserExpCmt $userExpCmt)
    {
        $this->userExpCmt = $userExpCmt;
        $this->time = Carbon::now();
    }
    
    /**
     * @inheritDoc
     */
    public function buildPayload()
    {
        $user = $this->userExpCmt->user;
        $comment = $this->userExpCmt;
        $exp = $comment->exp;
        
        $payload = [
            'user' => [
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'id'       => $user->id,
            ],
            'exp'  => [
                'title' => $exp->title,
                'reply' => $comment->content,
                'media' => $exp->mediaRlt()->first(['url', 'type', 'height', 'width']) ?? "",
            ],
        ];
        if ($comment->pid > 0) {
            $payload['exp']['comment'] = $comment->parent->content;
        }
        
        return $payload;
    }
    
    /**
     * @inheritDoc
     */
    public function getActionUserId()
    {
        return $this->userExpCmt->user_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getReceiverUserId()
    {
        $comment = $this->userExpCmt;
        return $comment->pid > 0 ? $comment->parent->user_id : $comment->exp->user_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getAction()
    {
        $comment = $this->userExpCmt;
        return $comment->pid > 0 ? Notification::A_REPLY : Notification::A_COMMENT;
    }
    
    /**
     * @inheritDoc
     */
    public function getJump()
    {
        return Notification::J_EXP;
    }
    
    /**
     * @inheritDoc
     */
    public function getTarget()
    {
        return Notification::T_EXP;
    }
    
    /**
     * @inheritDoc
     */
    public function getReferId()
    {
        return $this->userExpCmt->exp_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->time;
    }
}
