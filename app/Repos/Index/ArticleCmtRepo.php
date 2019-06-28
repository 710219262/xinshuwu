<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 17:29
 */

namespace App\Repos\Index;

use App\Events\Article\ArticleWasCommented;
use App\Events\Article\CommentWasLiked;
use App\Models\Article;
use App\Models\ArticleCmt;
use App\Models\ArticleCmtLike;
use App\Models\User;

class ArticleCmtRepo
{
    /**
     * @param User $user
     * @param      $data
     */
    public function create(User $user, $data)
    {
        $pid = array_get($data, 'pid', 0);
        /** @var ArticleCmt $articleCmt */
        $articleCmt = ArticleCmt::query()->create(array_merge(array_only($data, [
            'article_id',
            'content',
        ]), [
            'user_id'   => $user->id,
            'pid'       => $pid,
            //todo author may have a app account in future
            'is_author' => false,
        ]));
        
        if ($pid > 0) {
            event(new ArticleWasCommented($articleCmt));
        }
    }
    
    /**
     * @param User $user
     * @param      $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function like(User $user, $id)
    {
        try {
            \DB::beginTransaction();
            /** @var ArticleCmt $comment */
            $comment = ArticleCmt::query()->find($id);
            if (ArticleCmtLike::query()
                    ->where('user_id', '=', $user->id)
                    ->where('comment_id', $id)
                    ->count() === 0) {
                $comment->increment('like');
                
                /** @var ArticleCmtLike $likeModel */
                $likeModel = ArticleCmtLike::query()->create([
                    'user_id'    => $user->id,
                    'comment_id' => $id,
                ]);
                
                event(new CommentWasLiked($likeModel));
                
                \DB::commit();
                return json_response([], '点赞成功');
            } else {
                return json_response([], '您已经点过赞了哦~', 400);
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return json_response([], '点赞失败了哦', 400);
        }
    }
    
    /**
     * @param User $user
     * @param      $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function unlike(User $user, $id)
    {
        try {
            \DB::beginTransaction();
            /** @var ArticleCmt $comment */
            $comment = ArticleCmt::query()->find($id);
            if (ArticleCmtLike::query()
                ->where('user_id', '=', $user->id)
                ->where('comment_id', $id)
                ->exists()) {
                $comment->decrement('like');
                ArticleCmtLike::query()->where('user_id', $user->id)
                    ->where('comment_id', $id)
                    ->delete();
                
                \DB::commit();
                return json_response([], '取消点赞成功');
            } else {
                return json_response([], '您已经取消点赞了哦~', 400);
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return json_response([], '取消点赞失败了哦', 400);
        }
    }
    
    /**
     * @param      $id
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function list($id)
    {
        /** @var Article $article */
        $article = Article::query()->find($id);
        
        $comments = $article->comments();
        
        $comments->transform(function ($c) {
            $getLikeStatus = function ($c) {
                $user = app('request')->user();
                if ($user) {
                    return ArticleCmtLike::query()
                        ->where('user_id', $user->id)
                        ->where('comment_id', $c->id)
                        ->exists();
                }
                return false;
            };
            /** @var ArticleCmt $c */
            $c['is_liked'] = $getLikeStatus($c);
            
            $c['children']->transform(function ($cld) use ($getLikeStatus) {
                /** @var ArticleCmt $cld */
                $cld['is_liked'] = $getLikeStatus($cld);
                return $cld;
            });
            
            return $c;
        });
        
        return $comments;
    }
}
