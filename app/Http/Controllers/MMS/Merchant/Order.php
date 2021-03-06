<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 18/04/2019
 * Time: 21:59
 */

namespace App\Http\Controllers\MMS\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MerchantAccount;
use App\Models\Order as OrderModel;
use App\Repos\Merchant\OrderRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class Order extends Controller
{
    /**
     * @param Request   $request
     *
     * @param OrderRepo $orderRepo
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, OrderRepo $orderRepo)
    {
        $this->validate($request, [
            'query'               => 'array',
            'query.begin'         => 'date',
            'query.end'           => 'date',
            'query.order_no'      => 'string',
            'query.pay_method'    => [Rule::in(OrderModel::P_WECHAT, OrderModel::P_ALI)],
            'query.logistic_abbr' => 'string|exists:xsw_express_company,abbr',
            'query.status'        => [Rule::in(array_keys(OrderModel::STATUS_MAPPING))],
        ]);
        
        /** @var MerchantAccount $user */
        $user = $request->user();

        if(!empty($request->input('ispage'))) {
            $pageSize = empty($request->input('pageSize')) ? 10 : $request->input('pageSize');
            $pageIndex = empty($request->input('pageIndex')) ? 1 : $request->input('pageIndex');
            $offset = ceil($pageSize * ($pageIndex - 1));
            $orders = $orderRepo->list($user, $request->input('query'), $offset, $pageSize);
        }else{
            $orders = $orderRepo->list($user, $request->input('query'));
        }

        return json_response($orders);
    }
    
    /**
     * @param Request   $request
     * @param OrderRepo $orderRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function info(Request $request, OrderRepo $orderRepo)
    {
        /** @var MerchantAccount $user */
        $user = $request->user();
        
        $this->validate($request, [
            'order_no' => [
                'required',
                'string',
                Rule::exists('xsw_user_order')
                    ->where('store_id', $user->id),
            ],
        ], [
            'order_no.exists' => '订单号不存在哦',
        ]);
        
        return json_response($orderRepo->info($request->input('order_no')));
    }
    
    /**
     * @param Request   $request
     * @param OrderRepo $orderRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function dispatchGoods(Request $request, OrderRepo $orderRepo)
    {
        /** @var MerchantAccount $user */
        $user = $request->user();
        
        $this->validate($request, [
            'order_no'    => [
                'required',
                'string',
                Rule::exists('xsw_user_order')
                    ->where('store_id', $user->id)
                    ->where('status', OrderModel::S_PAYED),
            ],
            'logistic_no' => 'required|string|min:5',
            'company_id'  => 'required|int|exists:xsw_express_company,id',
        ], [
            'logistic_no.required' => '订单号不能为空哦',
            'logistic_no.string'   => '订单号不能为空哦',
            'logistic_no.min'      => '订单号太短了',
            'order_no.exists'      => '订单不存在或者已发货',
            'company_id.exists'    => '物流公司不存在哦',
        ]);
        
        $orderRepo->dispatchGoods(
            $request->input('order_no'),
            $request->input('logistic_no'),
            $request->input('company_id')
        );
        
        return json_response([], '操作成功');
    }

    /**
     * @param Request $request
     *
     * @param TransactionRepo $transactionRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Exception
     */
    public function statistics(Request $request, OrderRepo $orderRepo)
    {
        return $orderRepo->statistics($request->user());
    }

    public function statisticsToday(Request $request, OrderRepo $orderRepo)
    {
        return $orderRepo->statisticsToday($request->user());
    }
}
