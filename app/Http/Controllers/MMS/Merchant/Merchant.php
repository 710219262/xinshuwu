<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/02/2019
 * Time: 15:01
 */

namespace App\Http\Controllers\MMS\Merchant;

use App\Http\Controllers\Controller;
use App\Models\MerchantAccount;
use App\Models\MerchantImg;
use App\Models\MerchantInfo;
use App\Repos\Merchant\MerchantInfoRepo;
use App\Repos\Merchant\MerchantRepo;
use App\Services\OcrService;
use App\Services\OssService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Merchant extends Controller
{
    /**
     * 商户入驻 [商家，个人买手]
     *
     * @param Request      $request
     * @param MerchantRepo $repo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function create(Request $request, MerchantRepo $repo)
    {
        $this->validate($request, [
            'account_id'              => 'required|exists:xsw_merchant_account,id',
            'trade_mode'              => 'required|string|in:MERCHANT,SINGLE',
            'ship_region_id'          => 'required|int|exists:xsw_region,region_id',
            'ship_addr'               => 'required|string',
            'company_name'            => 'required_if:trade_mode,MERCHANT|string',
            'company_region_id'       => 'required_if:trade_mode,MERCHANT|int|exists:xsw_region,region_id',
            'license_url'             => 'required_if:trade_mode,MERCHANT|string',
            'credit_code'             => 'required_if:trade_mode,MERCHANT|string',
            'license_register_addr'   => 'required_if:trade_mode,MERCHANT|string',
            'license_validate_before' => 'required_if:trade_mode,MERCHANT|date',
            'id_card_front'           => 'required_if:trade_mode,SINGLE|string',
            'id_card_back'            => 'required_if:trade_mode,SINGLE|string',
            'product_brand'           => 'required|string',
            'product_category_id'     => 'required|int|exists:xsw_goods_category,id',
            'product_link'            => 'string',
            'contact'                 => 'required|string',
            'phone'                   => 'required|string',
            'wechat'                  => 'required|string',
            'emg_contact'             => 'required|string',
            'emg_phone'               => 'required|string',
            'consignee_region_id'     => 'required|int|exists:xsw_region,region_id',
            'consignee_addr'          => 'string',
            'certs_img'               => 'array',
            'certs_img.*'             => 'string',
            'product_img'             => 'array',
            'product_img.*'           => 'string',
            'merchant_name'                   => 'required|string'
        ]);
        
        
        \DB::transaction(function () use ($request, $repo) {
            $tradeMode = $request->input('trade_mode');
            
            /** @var MerchantInfo $merchant */
            if ($tradeMode === MerchantInfo::MERCHANT) {
                $merchant = $repo->createMerchant($request->all());
            } else {
                $merchant = $repo->createSingle($request->all());
            }
            
            if ($request->has('product_img')) {
                $repo->createMerchantImg(
                    $merchant->id,
                    MerchantImg::IMG_PRODUCT,
                    $request->input('product_img')
                );
            }
            
            if ($request->has('certs_img')) {
                $repo->createMerchantImg(
                    $merchant->id,
                    MerchantImg::IMG_CERT,
                    $request->input('certs_img')
                );
            }
            
            /** @var MerchantAccount $merchant */
            $merchant = $request->user();
            $merchant->update([
                'name' => $request->input('merchant_name'),
                'status' => MerchantAccount::S_CHECKING,
            ]);
        });
        
        
        return json_response([], '提交成功，请耐心等待审核');
    }
    
    /**
     * @param Request      $request
     * @param MerchantRepo $repo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Throwable
     */
    public function update(Request $request, MerchantRepo $repo)
    {
        $this->validate($request, [
            'account_id'              => 'required|exists:xsw_merchant_account,id',
            'trade_mode'              => 'required|string|in:MERCHANT,SINGLE',
            'ship_region_id'          => 'required|int|exists:xsw_region,region_id',
            'ship_addr'               => 'required|string',
            'company_name'            => 'required_if:trade_mode,MERCHANT|string',
            'company_region_id'       => 'required_if:trade_mode,MERCHANT|int|exists:xsw_region,region_id',
            'license_url'             => 'required_if:trade_mode,MERCHANT|string',
            'credit_code'             => 'required_if:trade_mode,MERCHANT|string',
            'license_register_addr'   => 'required_if:trade_mode,MERCHANT|string',
            'license_validate_before' => 'required_if:trade_mode,MERCHANT|date',
            'id_card_front'           => 'required_if:trade_mode,SINGLE|string',
            'id_card_back'            => 'required_if:trade_mode,SINGLE|string',
            'product_brand'           => 'required|string',
            'product_category_id'     => 'required|int|exists:xsw_goods_category,id',
            'product_link'            => 'string',
            'contact'                 => 'required|string',
            'phone'                   => 'required|string',
            'wechat'                  => 'required|string',
            'emg_contact'             => 'required|string',
            'emg_phone'               => 'required|string',
            'consignee_region_id'     => 'required|int|exists:xsw_region,region_id',
            'consignee_addr'          => 'string',
            'certs_img'               => 'array',
            'certs_img.*'             => 'string',
            'product_img'             => 'array',
            'product_img.*'           => 'string',
        ]);
        
        \DB::transaction(function () use ($request, $repo) {
            /** @var MerchantInfo $merchantInfo */
            $merchantInfo = $request->user()->merchantInfo;
            $merchantInfo->forceDelete();
            
            $tradeMode = $request->input('trade_mode');
            
            /** @var MerchantInfo $merchant */
            if ($tradeMode === MerchantInfo::MERCHANT) {
                $merchant = $repo->createMerchant($request->all());
            } else {
                $merchant = $repo->createSingle($request->all());
            }
            
            if ($request->has('product_img')) {
                $repo->createMerchantImg(
                    $merchant->id,
                    MerchantImg::IMG_PRODUCT,
                    $request->input('product_img')
                );
            }
            
            if ($request->has('certs_img')) {
                $repo->createMerchantImg(
                    $merchant->id,
                    MerchantImg::IMG_CERT,
                    $request->input('certs_img')
                );
            }
            
            /** @var MerchantAccount $merchant */
            $merchant = $request->user();
            $merchant->update([
                'status' => MerchantAccount::S_CHECKING,
            ]);
        });
        
        
        return json_response([], '提交成功，请耐心等待审核');
    }
    
    /**
     * 识别营业执照
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function ocrLicense(Request $request)
    {
        $this->validate($request, [
            'img' => 'required|string',
        ]);
        
        $img = $request->input('img');
        
        if (!OssService::isExist($img)) {
            throw new BadRequestHttpException("营业执照文件无效，请重新上传");
        }
        
        $result = OcrService::request($img);
        
        return json_response($result);
    }
    
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getSignInStatus(Request $request)
    {
        /** @var MerchantAccount $merchant */
        $merchant = $request->user();
        $status = intval($merchant->status);
        
        return json_response([
            'status'        => $status,
            'reject_reason' => MerchantAccount::S_REJECTED === $status ?
                $merchant->merchantInfo->reject_reason : '',
        ]);
    }
    
    /**
     * @param Request          $request
     * @param MerchantInfoRepo $merchantInfoRepo
     *
     * @return \Illuminate\Http\Response|\Laravel\Lumen\Http\ResponseFactory
     */
    public function getMerchantInfo(Request $request, MerchantInfoRepo $merchantInfoRepo)
    {
        /** @var MerchantInfo $merchantInfo */
        $merchantInfo = $request->user()->merchantInfo;
        if ($merchantInfo) {
            $info = $merchantInfoRepo->getMerchantInfo($merchantInfo->id);
        } else {
            $info = [];
        }
        return json_response($info);
    }
}
