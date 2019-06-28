<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 15/04/2019
 * Time: 22:29
 */

namespace App\Events\Exp;

use App\Events\Contract\Notification as NotificationContract;
use App\Events\Event;
use App\Models\Notification;
use App\Models\UserExpCmtLike;
use Carbon\Carbon;

/**
 * Class CommentWasLiked
 *
 * @package App\Events\Exp
 */
class CommentWasLiked extends Event implements NotificationContract
{
    protected $userExpCmtLike;
    protected $time;
    
    /**
     * CommentWasLiked constructor.
     *
     * @param UserExpCmtLike $userExpCmtLike
     */
    public function __construct(UserExpCmtLike $userExpCmtLike)
    {
        $this->userExpCmtLike = $userExpCmtLike;
        $this->time = Carbon::now();
    }
    
    /**
     * @inheritdoc
     */
    public function buildPayload()
    {
        
        $user = $this->userExpCmtLike->user;
        $comment = $this->userExpCmtLike->comment;
        $exp = $comment->exp;
        
        return [
            'user' => [
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'id'       => $user->id,
            ],
            'exp'  => [
                'title'   => $exp->title,
                'comment' => $comment->content,
                'media'   => $exp->mediaRlt()->first(['url', 'type', 'height', 'width']) ?? "",
            ],
        ];
    }
    
    /**
     * @inheritDoc
     */
    public function getActionUserId()
    {
        return $this->userExpCmtLike->user->id;
    }
    
    /**
     * @inheritDoc
     */
    public function getReceiverUserId()
    {
        return $this->userExpCmtLike->comment->user_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getAction()
    {
        return Notification::A_LIKE;
    }
    
    /**
     * @inheritDoc
     */
    public function getTarget()
    {
        return Notification::T_EXP_COMMENT;
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
    public function getReferId()
    {
        return $this->userExpCmtLike->comment->exp->id;
    }
    
    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->time;
    }
}
