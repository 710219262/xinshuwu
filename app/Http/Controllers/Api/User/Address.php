<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 23:02
 */

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Address extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function create(Request $request)
    {
        $this->validate($request, [
            'pid'     => 'required|int|exists:xsw_region,region_id',
            'cid'     => 'required|int|exists:xsw_region,region_id',
            'aid'     => 'required|int|exists:xsw_region,region_id',
            'contact' => 'required|string',
            'phone'   => 'required|string',
            'address' => 'required|string',
            'number'  => 'required|string',
            'tag'     => [
                'required',
                'string',
                Rule::in([
                    UserAddress::T_HOME,
                    UserAddress::T_COMPANY,
                    UserAddress::T_OTHER,
                ]),
            ],
        ]);
        
        $userAddress = UserAddress::query()->create([
            'user_id'     => $request->user()->id,
            'contact'     => $request->input('contact'),
            'phone'       => $request->input('phone'),
            'address'     => $request->input('address'),
            'number'      => $request->input('number'),
            'tag'         => $request->input('tag'),
            'province_id' => $request->input('pid'),
            'city_id'     => $request->input('cid'),
            'area_id'     => $request->input('aid'),
            'region'      => UserAddress::getRegionText(
                $request->input('pid'),
                $request->input('cid'),
                $request->input('aid')
            ),
        ]);
        
        return json_response($userAddress);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id'      => [
                'required',
                'int',
                Rule::exists('xsw_user_address')->where('user_id', $request->user()->id),
            ],
            'pid'     => 'required|int|exists:xsw_region,region_id',
            'cid'     => 'required|int|exists:xsw_region,region_id',
            'aid'     => 'int|exists:xsw_region,region_id',
            'contact' => 'required|string',
            'phone'   => 'required|string',
            'address' => 'required|string',
            'number'  => 'required|string',
            'tag'     => [
                'required',
                'string',
                Rule::in([
                    UserAddress::T_HOME,
                    UserAddress::T_COMPANY,
                    UserAddress::T_OTHER,
                ]),
            ],
        ]);
        
        $data = [
            'contact'     => $request->input('contact'),
            'phone'       => $request->input('phone'),
            'address'     => $request->input('address'),
            'number'      => $request->input('number'),
            'tag'         => $request->input('tag'),
            'province_id' => $request->input('pid'),
            'city_id'     => $request->input('cid'),
            'area_id'     => $request->input('aid'),
            'region'      => UserAddress::getRegionText(
                $request->input('pid'),
                $request->input('cid'),
                $request->input('aid')
            ),
        ];
        
        //filter null area_id
        $data = array_filter($data);
        
        $userAddress = UserAddress::query()->find($request->input('id'));
        
        $userAddress->update($data);
        
        return json_response($userAddress);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function setDefault(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        $this->validate($request, [
            'id' => [
                'required',
                'int',
                Rule::exists('xsw_user_address')->where('user_id', $user->id),
            ],
        ]);
        
        UserAddress::query()
            ->where('id', $request->input('id'))
            ->update([
                'is_default' => 1,
            ]);
        
        
        UserAddress::query()
            ->where('id', '<>', $request->input('id'))
            ->where('user_id', $user->id)
            ->update([
                'is_default' => 0,
            ]);
        
        return json_response();
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getList(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        
        $address = $user->address()->get();
        
        return json_response($address);
    }

    /**
     * @param Request   $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_address',
        ]);

        $addressInfo = UserAddress::query()->select([
        'id',
        'user_id',
        'contact',
        'phone',
        'address',
        'number',
        'region',
        'tag',
        'is_default',
        'province_id as pid',
        'city_id as cid',
        'area_id as aid',
    ])->find($request->input('id'));

        return json_response($addressInfo);
    }
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delete(Request $request)
    {
        $this->validate($request, [
            'id' => 'required|int|exists:xsw_user_address',
        ]);
        
        UserAddress::query()->find($request->input('id'))->delete();
        
        return json_response();
    }
}
