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
use App\Models\MallHome as MallHomeModel;
use App\Repos\Admin\MallHomeRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MallHome extends Controller
{
    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, MallHomeRepo $mallhomeRepo)
    {

        $pid = 0;
        $mallhome = $mallhomeRepo->list($pid);

        foreach ($mallhome as $k => $v){
            $mallhome[$k]['child'] = $mallhomeRepo->list($v['id']);
        }
        return json_response($mallhome);
    }

    /**
     * @param Request     $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request, MallHomeRepo $mallhomeRepo)
    {
        $this->validate($request, [
            'id'          => [
                'required',
                'int',
                Rule::exists('xsw_mall_home', 'id')
            ],
            'cover'       => 'required|string',
            'content'     => 'string',
            'appurl'     => 'string',
            'name'     => 'string',
            'price'     => 'numeric',
        ]);

        $mallhomeRepo->update($request->input('id'), $request->only([
            'cover',
            'content',
            'appurl',
            'name',
            'price',
        ]));

        return json_response([], '操作成功');
    }
}
