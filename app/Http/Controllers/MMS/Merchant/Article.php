<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:41
 */

namespace App\Http\Controllers\MMS\Merchant;

use App\Http\Controllers\Controller;
use App\Models\Article as ArticleModel;
use App\Models\MerchantAccount;
use App\Repos\Merchant\ArticleRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Article extends Controller
{
    /**
     * @param Request     $request
     *
     * @param ArticleRepo $articleRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request, ArticleRepo $articleRepo)
    {
        /** @var MerchantAccount $merchant */
        $merchant = $request->user();
        
        $this->validate($request, [
            'category_id' => 'required|int|exists:xsw_goods_category,id',
            'goods_id'    => [
                'required',
                'int',
                Rule::exists('xsw_goods_info', 'id')
//                    ->where('store_id', $merchant->id),
            ],
            'title'       => 'required|string',
            'cover'       => 'required|string',
            'label'       => 'required|string',
            'content'     => 'required|string',
            'status'      => [
                'required',
                'string',
                Rule::in(
                    ArticleModel::S_DRAFT,
                    ArticleModel::S_AUDIT_PENDING
                ),
            ],
        ]);
        
        $articleRepo->create($request->user(), $request->only([
            'category_id',
            'goods_id',
            'title',
            'status',
            'cover',
            'content',
            'label',
        ]));
        
        return json_response([], '操作成功');
    }
    
    /**
     * @param Request     $request
     * @param ArticleRepo $articleRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, ArticleRepo $articleRepo)
    {
        /** @var MerchantAccount $merchant */
        $merchant = $request->user();
        
        $this->validate($request, [
            'query'                 => 'array',
            'query.title'           => 'string',
            'query.category_id'     => 'int|exists:xsw_goods_category,id',
            'query.published_begin' => 'date',
            'query.published_end'   => 'date',
            'query.publisher'       => [
                'string',
                Rule::in(
                    ArticleModel::P_MERCHANT,
                    ArticleModel::P_PLATFORM,
                    ArticleModel::P_USER
                ),
            ],
            'query.status'          => [
                'string',
                Rule::in(
                    ArticleModel::S_DRAFT,
                    ArticleModel::S_AUDIT_PENDING,
                    ArticleModel::S_REJECTED,
                    ArticleModel::S_PUBLISHED,
                    ArticleModel::S_OFFLINE
                ),
            ],
        ]);
        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $articles = $articleRepo->list($merchant->id, $request->input('query'), $offset, $pageSize);
        }else{
            $articles = $articleRepo->list($merchant->id, $request->input('query'));
        }

        return json_response($articles);
    }
    
    /**
     * @param Request     $request
     *
     * @param ArticleRepo $articleRepo
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|null|object
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request, ArticleRepo $articleRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_article',
        ]);
        
        return json_response($articleRepo->info($request->input('id')));
    }
    
    /**
     * @param Request     $request
     * @param ArticleRepo $articleRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, ArticleRepo $articleRepo)
    {
        /** @var MerchantAccount $merchant */
        $merchant = $request->user();
        
        $this->validate($request, [
            'id'          => [
                'required',
                'int',
                Rule::exists('xsw_article', 'id')
                    ->where('author_id', $merchant->id),
            ],
            'category_id' => 'required|int|exists:xsw_goods_category,id',
            'goods_id'    => [
                'required',
                'int',
                Rule::exists('xsw_goods_info', 'id')
//                    ->where('store_id', $merchant->id),
            ],
            'title'       => 'required|string',
            'cover'       => 'required|string',
            'content'     => 'required|string',
            'label'     => 'required|string',
            'status'      => [
                'required',
                'string',
                Rule::in(
                    ArticleModel::S_DRAFT,
                    ArticleModel::S_AUDIT_PENDING,
                    ArticleModel::S_OFFLINE
                ),
            ],
        ]);
        
        $articleRepo->update($request->input('id'), $request->only([
            'category_id',
            'goods_id',
            'title',
            'status',
            'cover',
            'content',
            'label',
        ]));
        
        return json_response([], '操作成功');
    }
    
    /**
     * @param Request     $request
     *
     * @param ArticleRepo $articleRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function batchDelete(Request $request, ArticleRepo $articleRepo)
    {
        $this->validate($request, [
            'ids'   => 'required|array',
            'ids.*' => 'required|int|exists:xsw_article,id',
        ]);
        
        $articleRepo->batchDelete($request->input('ids'));
        
        return json_response([], '操作成功');
    }
    
    /**
     * @param Request     $request
     *
     * @param ArticleRepo $articleRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function batchChangeStatus(Request $request, ArticleRepo $articleRepo)
    {
        $this->validate($request, [
            'ids'    => 'required|array',
            'ids.*'  => 'required|int|exists:xsw_article,id',
            'status' => [
                'required',
                'string',
                Rule::in(
                    ArticleModel::S_DRAFT,
                    ArticleModel::S_AUDIT_PENDING,
                    ArticleModel::S_OFFLINE
                ),
            ],
        ]);
        
        $articleRepo->batchChangeStatus(
            $request->input('ids'),
            $request->input('status')
        );
        
        return json_response([], '操作成功');
    }
}
