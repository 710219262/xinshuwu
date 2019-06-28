<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 16:06
 */

namespace App\Http\Controllers\Api\Index;

use App\Events\Order\OrderWasReceived;
use App\Http\Controllers\Controller;
use App\Listeners\OrderWasReceivedListener;
use App\Models\Article as ArticleModel;
use App\Models\Order;
use App\Repos\Index\ArticleRepo;
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
    public function list(Request $request, ArticleRepo $articleRepo)
    {
        $this->validate($request, [
            'category_id' => 'int|exists:xsw_goods_category,id',
            'keywords'    => 'string',
        ]);

        //推荐
        if(empty($request->only([
            'id',
            'keywords',
            'category_id',
        ]))){
            $request['recommend'] = 1;
        }
        
        $list = $articleRepo->list($request->only([
            'id',
            'keywords',
            'category_id',
            'recommend',
        ]));
        
        return json_response($list);
    }
    
    /**
     * @param Request     $request
     *
     * @param ArticleRepo $articleRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request, ArticleRepo $articleRepo)
    {
        $this->validate($request, [
            'id' => [
                'required',
                'int',
                Rule::exists('xsw_article')
                    ->where('status', ArticleModel::S_PUBLISHED),
            ],
        ]);
        
        $info = $articleRepo->info($request->input('id'));
        
        return json_response($info);
    }
    
    /**
     * @param Request     $request
     * @param ArticleRepo $articleRepo
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function like(Request $request, ArticleRepo $articleRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_article,id',
        ]);
        
        return $articleRepo->like($request->user(), $request->input('id'));
    }
    
    /**
     * @param Request     $request
     * @param ArticleRepo $articleRepo
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function unlike(Request $request, ArticleRepo $articleRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_article,id',
        ]);
        
        return $articleRepo->unlike($request->user(), $request->input('id'));
    }
    
    /**
     * @param Request     $request
     * @param ArticleRepo $articleRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function collect(Request $request, ArticleRepo $articleRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_article,id',
        ]);
        
        return $articleRepo->collect($request->user(), $request->input('id'));
    }
}
