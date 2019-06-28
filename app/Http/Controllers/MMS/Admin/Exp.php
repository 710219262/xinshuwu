<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 22/03/2019
 * Time: 12:29
 */

namespace App\Http\Controllers\MMS\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserExp;
use App\Models\UserExpMedia;
use App\Repos\Admin\ExpRepo;
use App\Repos\GoodsRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Exp extends Controller
{
    /**
     * @param ExpRepo $expRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function list(Request $request,ExpRepo $expRepo)
    {
        $this->validate($request, [
            'query'               => 'array'
        ]);

        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $exps = $expRepo->list($request->input('query'), $offset, $pageSize);
        }else{
            $exps = $expRepo->list($request->input('query'));
        }
        return json_response($exps);
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
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, ExpRepo $expRepo)
    {
        $this->validate($request, [
            'id'          => [
                'required',
                'int',
                Rule::exists('xsw_user_exp', 'id')
            ],
            'status'       => 'string',
            'reject_reason'     => 'string',
            'deleted_at'     => 'string',
        ]);

        $expRepo->update($request->input('id'), $request->only([
            'status',
            'reject_reason',
            'deleted_at'
        ]));

        return json_response([], '操作成功');
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
}
