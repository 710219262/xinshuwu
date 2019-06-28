<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/04/2019
 * Time: 14:41
 */

namespace App\Http\Controllers\MMS\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Repos\Admin\GuessRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Guess extends Controller
{
    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function stageinfo(Request $request, GuessRepo $guessRepo)
    {
        $this->validate($request, [
            'id' => 'required|int',
        ]);

        return json_response($guessRepo->info($request->input('id')));
    }
    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function stageupdate(Request $request, GuessRepo $guessRepo)
    {
        $this->validate($request, [
            'id'          => [
                'required',
                'int',
                Rule::exists('xsw_mall_home', 'id')
            ],
            'goods_img'       => 'required|string',
            'goods_name'     => 'required|string',
            'goods_orderby'     => 'required|int',
            'number_level'     => 'required|int',
            'number_prefix'     => 'required|int'
        ]);

        $guessRepo->update($request->input('id'), $request->only([
            'goods_img',
            'goods_name',
            'goods_orderby',
            'number_level',
            'number_prefix',
        ]));

        return json_response([], '操作成功');
    }
    
    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function stagelist(Request $request, GuessRepo $guessRepo)
    {
        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $videos = $guessRepo->list($request->input('query'), $offset, $pageSize);
        }else{
            $videos = $guessRepo->list($request->input('query'));
        }
        
        return json_response($videos);
    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function tongji(Request $request, GuessRepo $guessRepo)
    {
        $videos = $guessRepo->tongji($request->input('id'));
        return json_response($videos);
    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function users(Request $request, GuessRepo $guessRepo)
    {
        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $videos = $guessRepo->users($request->input('query'), $offset, $pageSize);
        }else{
            $videos = $guessRepo->users($request->input('query'));
        }

        return json_response($videos);
    }
}
