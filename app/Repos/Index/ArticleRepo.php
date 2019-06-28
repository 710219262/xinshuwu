<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 16:08
 */

namespace App\Repos\Index;

use App\Models\Article;
use App\Models\ArticleLike;
use App\Models\User;
use App\Models\UserCollection;
use Illuminate\Database\Query\Builder;

class ArticleRepo
{
    /**
     * @param array $data
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list($data)
    {
        $builder = Article::query()->with([
            'category' => function ($q) {
                /** @var Builder $q */
                $q->select([
                    'id',
                    'name',
                ]);
            },
        ]);
        
        if ($categoryId = array_get($data, 'category_id')) {
            $builder->where('category_id', $categoryId);
        }
        
        if ($keywords = array_get($data, 'keywords')) {
            $builder->where('title', 'LIKE', "%$keywords%");
        }

        if ($recommend = array_get($data, 'recommend')) {
            $builder->where('recommend', $recommend);
        }

        $articles = $builder->where('status', '=', Article::S_PUBLISHED)
            ->orderBy(\DB::raw('RAND()'))
            ->select([
                'id',
                'category_id',
                'title',
                'cover',
                'view',
                'like',
                'collect',
                'label',
            ])->get();
        
        return $articles;
    }
    
    /**
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function info($id)
    {
        $article = Article::query()
            ->with([
                'category' => function ($q) {
                    /** @var Builder $q */
                    $q->select([
                        'id',
                        'name',
                    ]);
                },
                'goods'    => function ($q) {
                    /** @var Builder $q */
                    $q->select([
                        'id',
                        'name',
                        'status',
                        'price',
                        'market_price',
                    ]);
                },
            ])->where('id', $id)
            ->first([
                'id',
                'category_id',
                'goods_id',
                'title',
                'cover',
                'view',
                'collect',
                'like',
                'publisher',
                'author_id',
                'content',
                'label',
            ])->append(['author']);
        
        $article->increment('view');
        
        return $article;
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
            /** @var Article $article */
            $article = Article::query()->find($id);
            if (ArticleLike::query()
                    ->where('user_id', '=', $user->id)
                    ->where('article_id', $id)
                    ->count() === 0) {
                $article->increment('like');
                
                ArticleLike::query()->create([
                    'user_id'    => $user->id,
                    'article_id' => $id,
                ]);
                
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
            /** @var Article $article */
            $article = Article::query()->find($id);
            if (ArticleLike::query()
                ->where('user_id', '=', $user->id)
                ->where('article_id', $id)
                ->exists()) {
                $article->decrement('like');
                
                ArticleLike::query()->where('user_id', $user->id)
                    ->where('article_id', $id)
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
     * @param User $user
     * @param      $id
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function collect(User $user, $id)
    {
        if (UserCollection::query()->where('type', UserCollection::T_ARTICLE)
                ->where('collect_id', $id)
                ->where('user_id', $user->id)
                ->count() === 0) {
            UserCollection::query()->create([
                'type'       => UserCollection::T_ARTICLE,
                'collect_id' => $id,
                'user_id'    => $user->id,
            ]);
            
            Article::query()
                ->where('id', $id)
                ->increment('collect');
        }
        
        
        return json_response([], '收藏成功');
    }
}
