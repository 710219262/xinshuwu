<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 07/03/2019
 * Time: 23:17
 */

namespace App\Http\Controllers\MMS\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MerchantAccount;
use Illuminate\Http\Request;

class Store extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getStoreInfo(Request $request)
    {
        /** @var MerchantAccount $merchant */
        $merchant = $request->user();
        
        return json_response($merchant->only([
            'id',
            'name',
            'category_id',
            'logo',
            'desc',
        ]));
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateStoreInfo(Request $request)
    {
        $this->validate($request, [
            'name'        => 'required|string',
            'category_id' => 'required|int|exists:xsw_goods_category,id',
            'logo'        => 'string',
            'desc'        => 'string',
        ]);
        
        $data = array_filter($request->only(['name', 'category_id', 'logo', 'desc']));

        if(!empty($request->input('password'))){
            $data['password'] = md5($request->input('password'));
        }
        $request->user()->update($data);
        
        return json_response([], '保存成功');
    }
}

