<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 15/04/2019
 * Time: 22:32
 */

namespace App\Events\Article;

use App\Events\Contract\Notification as NotificationContract;
use App\Events\Event;
use App\Models\ArticleCmt;
use App\Models\Notification;
use Carbon\Carbon;

class ArticleWasCommented extends Event implements NotificationContract
{
    protected $articleCmt;
    protected $time;
    
    public function __construct(ArticleCmt $articleCmt)
    {
        $this->articleCmt = $articleCmt;
        $this->time = Carbon::now();
    }
    
    /**
     * @inheritDoc
     */
    public function buildPayload()
    {
        $user = $this->articleCmt->user;
        $comment = $this->articleCmt;
        $article = $comment->article;
        
        $payload = [
            'user'    => [
                'nickname' => $user->nickname,
                'avatar'   => $user->avatar,
                'id'       => $user->id,
            ],
            'article' => [
                'title' => $article->title,
                'reply' => $comment->content,
                'cover' => $article->cover,
            ],
        ];
        if ($comment->pid > 0) {
            $payload['article']['comment'] = $comment->parent->content;
        }
        
        return $payload;
    }
    
    /**
     * @inheritDoc
     */
    public function getActionUserId()
    {
        return $this->articleCmt->user_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getReceiverUserId()
    {
        // only parent comment user can receive this notification
        return $this->articleCmt->parent->user_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getAction()
    {
        $comment = $this->articleCmt;
        return $comment->pid > 0 ? Notification::A_REPLY : Notification::A_COMMENT;
    }
    
    /**
     * @inheritDoc
     */
    public function getJump()
    {
        return Notification::J_ARTICLE;
    }
    
    /**
     * @inheritDoc
     */
    public function getTarget()
    {
        return Notification::T_ARTICLE;
    }
    
    /**
     * @inheritDoc
     */
    public function getReferId()
    {
        return $this->articleCmt->article_id;
    }
    
    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->time;
    }
}
