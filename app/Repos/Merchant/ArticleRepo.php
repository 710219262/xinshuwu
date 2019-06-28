<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:52
 */

namespace App\Repos\Merchant;

use App\Models\Article;
use App\Models\MerchantAccount;
use Carbon\Carbon;

class ArticleRepo
{
    /**
     * @param MerchantAccount $merchantAccount
     * @param                 $data
     */
    public function create(MerchantAccount $merchantAccount, $data)
    {
        /** @var Article $article */
        $article = Article::query()->create(array_filter(array_only($data, [
            'category_id',
            'goods_id',
            'title',
            'status',
            'cover',
            'content',
            'label',
        ])));
        
        $data = [
            'publisher' => Article::P_MERCHANT,
            'author_id' => $merchantAccount->id,
        ];
        
        $article->update($data);
    }
    
    /**
     * @param                 $id
     * @param                 $data
     */
    public function update($id, $data)
    {
        /** @var Article $article */
        $article = Article::query()->find($id);
        
        $article->update(array_only($data, [
            'category_id',
            'goods_id',
            'title',
            'status',
            'cover',
            'content',
            'label',
        ]));
        
        if (Article::S_PUBLISHED == $article->status) {
            $data['published_at'] = Carbon::now();
        }
        
        $article->update($data);
    }
    
    /**
     * @param $id
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function info($id)
    {
        return Article::query()
            ->with(['category', 'goods'])
            ->where('id', $id)->first([
                'id',
                'goods_id',
                'category_id',
                'title',
                'cover',
                'sale',
                'like',
                'collect',
                'content',
                'status',
                'publisher',
                'published_at',
                'label',
            ]);
    }
    
    /**
     * @param $storeId
     * @param $query
     *
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list($storeId, $query, $offset = 0, $pageSize = 0)
    {
        $builder = Article::query()
            ->where('author_id', '=', $storeId)
            ->where('publisher', '=', Article::P_MERCHANT);
        
        $builder->with(['category', 'goods']);
        
        if ($categoryId = array_get($query, 'category_id')) {
            $builder->where('category_id', $categoryId);
        }
        
        if ($status = array_get($query, 'status')) {
            $builder->where('status', $status);
        }
        
        if ($title = array_get($query, 'title')) {
            $builder->where('title', 'LIKE', "%$title%");
        }
        
        if ($publishBegin = array_get($query, 'published_begin')) {
            $builder->where('published_at', '>=', $publishBegin);
        }
        
        if ($publishEnd = array_get($query, 'published_end')) {
            $builder->where('published_at', '<=', $publishEnd);
        }

        $Total = $builder->count();
        //暂时考虑兼容性
        if(!empty($pageSize)) {
            $builder->offset($offset)->limit($pageSize);
        }

        $articles = $builder->orderBy('published_at', 'desc')->select([
            'id',
            'title',
            'goods_id',
            'category_id',
            'cover',
            'sale',
            'like',
            'collect',
            'content',
            'status',
            'publisher',
            'published_at',
            'label',
        ])->get();

        //暂时考虑兼容性
        if(empty($pageSize)) {
            return $articles;
        }
        return ['total'=>$Total,'list'=>$articles];
    }
    
    /**
     * @param $ids
     */
    public function batchDelete($ids)
    {
        Article::query()
            ->whereIn('id', $ids)
            ->delete();
    }
    
    /**
     * @param $ids
     * @param $status
     */
    public function batchChangeStatus($ids, $status)
    {
        Article::query()
            ->whereIn('id', $ids)
            ->update([
                'status' => $status,
            ]);
    }
}
