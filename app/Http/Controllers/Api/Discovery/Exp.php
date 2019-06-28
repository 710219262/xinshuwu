<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 22/03/2019
 * Time: 12:29
 */

namespace App\Http\Controllers\Api\Discovery;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserExp;
use App\Models\UserExpMedia;
use App\Repos\User\ExpRepo;
use App\Repos\User\GoodsRepo;
use Illuminate\Http\Request;

class Exp extends Controller
{
    /**
     * @param ExpRepo $expRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function list(ExpRepo $expRepo)
    {
        return json_response($expRepo->list());
    }
    
    /**
     * @param Request $request
     * @param ExpRepo $expRepo
     *
     * @return ExpRepo[]|\Illuminate\Database\Eloquent\Collection|mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request, ExpRepo $expRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_exp',
        ]);
        
        return json_response($expRepo->info($request->input('id')));
    }
    
    /**
     * @param Request   $request
     *
     * @param GoodsRepo $goodsRepo
     *
     * @param ExpRepo   $expRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function create(Request $request, GoodsRepo $goodsRepo, ExpRepo $expRepo)
    {
        /** @var User $user */
        $user = $request->user();
        
        $orderGoodsIds = $goodsRepo->getBoughtGoods($user)->pluck('id')->toArray();
        
        $orderGoodsIds = implode(",", $orderGoodsIds);
        
        $types = implode(",", [UserExpMedia::T_IMAGE, UserExpMedia::T_VIDEO]);
        
        $this->validate($request, [
            'id'             => "required|int|in:$orderGoodsIds",
            'media'          => 'required|array',
            'media.*.url'    => 'required|string',
            'media.*.type'   => "required|string|in:$types",
            'media.*.height' => 'required|int|between:1,60000',
            'media.*.width'  => 'required|int|between:1,60000',
            'title'          => 'required|string',
            'content'        => 'required|string',
        ]);
        
        return $expRepo->create($user, $request->only([
            'id',
            'media',
            'title',
            'content',
        ]));
    }
    
    /**
     * @param Request   $request
     * @param GoodsRepo $goodsRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function goods(Request $request, GoodsRepo $goodsRepo)
    {
        /** @var User $user */
        $user = $request->user();
        
        $goods = $goodsRepo->getBoughtGoods($user);
        
        return json_response($goods);
    }
    
    /**
     * @param Request $request
     *
     * @param ExpRepo $expRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function like(Request $request, ExpRepo $expRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_exp,id',
        ]);
        
        return $expRepo->like($request->user(), $request->input('id'));
    }
    
    /**
     * @param Request $request
     * @param ExpRepo $expRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function unlike(Request $request, ExpRepo $expRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_exp,id',
        ]);
        
        return $expRepo->unlike($request->user(), $request->input('id'));
    }
    
    /**
     * @param Request $request
     * @param ExpRepo $expRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function collect(Request $request, ExpRepo $expRepo)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_exp,id',
        ]);
        
        return $expRepo->collect($request->user(), $request->input('id'));
    }
}
