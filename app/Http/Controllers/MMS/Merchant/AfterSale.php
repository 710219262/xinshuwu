<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/15
 * Time: 16:34
 */

namespace App\Http\Controllers\MMS\Merchant;

use App\Http\Controllers\Controller;
use App\Models\AfterSaleOrder;
use App\Models\MerchantAccount;
use App\Repos\Merchant\AfterSaleRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AfterSale extends Controller
{
    /**
     * 用户申请售后列表
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function list(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var MerchantAccount $merchant */
        $merchant = $request->user();
        $this->validate($request, [
            'status'     => 'array',
            'type'       => [
                'string',
                Rule::in([
                    AfterSaleOrder::T_RETURN_REFUND,
                    AfterSaleOrder::T_REFUND
                ])
            ],
            'time_start' => 'string|date',
            'time_end'   => 'string|date',
            'time'       => 'string|in:MONTH'
        ]);

        $data             = $request->only([
            'goods_name',
            'order_no',
            'aftersale_no',
            'logistic_no',
            'time_start',
            'time_end',
            'time',
            'type',
            'status',
        ]);
        $data['store_id'] = $merchant->id;

        return $afterSaleRepo->list($data);
    }

    /**
     * 审核
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function audit(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var MerchantAccount $merchant */
        $merchant = $request->user();
        $this->validate($request, [
            'aftersale_no'  => [
                'required',
                'string',
                Rule::exists('xsw_aftersale_order', 'aftersale_no')
                    ->where('store_id', $merchant->id)
                    ->where('status', AfterSaleOrder::S_REQUEST)
            ],
            'status'        => [
                'required',
                'string',
                Rule::in([
                    AfterSaleOrder::S_REJECTED,
                    AfterSaleOrder::S_AGREED
                ])
            ],
            'merchant_note' => 'string'
        ]);

        $data = [
            'store_id'      => $merchant->id,
            'aftersale_no'  => $request->input('aftersale_no'),
            'status'        => $request->input('status'),
            'merchant_note' => $request->input('note', '')
        ];

        return $afterSaleRepo->audit($data);
    }

    /**
     * 确认收货
     * @param Request $request
     * @param AfterSaleRepo $afterSaleRepo
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     */
    public function receive(Request $request, AfterSaleRepo $afterSaleRepo)
    {
        /** @var MerchantAccount $merchant */
        $merchant = $request->user();
        $this->validate($request, [
            'aftersale_no' => [
                'required',
                'string',
                Rule::exists('xsw_aftersale_order', 'aftersale_no')
                    ->where('store_id', $merchant->id)
                    ->where('status', AfterSaleOrder::S_SHIPPING)
            ],
        ]);

        $data = [
            'store_id'     => $merchant->id,
            'aftersale_no' => $request->input('aftersale_no'),
        ];

        return $afterSaleRepo->receive($data);
    }
}
