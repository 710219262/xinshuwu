<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 27/04/2019
 * Time: 18:35
 */

namespace App\Http\Controllers\Api\Index;

use App\Http\Controllers\Controller;
use App\Models\XSearch as XSearchModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class XSearch extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'type' => [
                'required',
                'string',
                Rule::in([
                    XSearchModel::T_ARTICLE,
                    XSearchModel::T_COMMUNICATION,
                    XSearchModel::T_NOVEL,
                    XSearchModel::T_FRIEND,
                    XSearchModel::T_SNACK,
                ]),
            ],
        ]);
        
        $type = $request->input('type');
        
        /** @var XSearchModel $x */
        $x = XSearchModel::query()->firstOrCreate([
            'type' => $type,
        ], [
            'type' => $type,
        ]);
        
        $x->increment('count');
        
        return json_response([], '感谢您的反馈~');
    }
}
