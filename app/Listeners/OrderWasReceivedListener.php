<?php
/**
 * Created by PhpStorm.
 * User: JS
 * Date: 2019/5/8
 * Time: 16:30
 */

namespace App\Listeners;

use App\Events\Order\OrderWasReceived;
use App\Models\Article;
use App\Models\MerchantTransaction;
use App\Models\Order;
use App\Models\OrderGoods;
use App\Models\PlatformTransaction;
use App\Models\UserExp;
use App\Models\UserShare;
use App\Models\UserTransaction;
use Illuminate\Contracts\Queue\ShouldQueue;

class OrderWasReceivedListener implements ShouldQueue
{
    /**
     * @var Order $order
     */
    protected $order;

    /**
     * @var OrderGoods $orderGoods
     */
    protected $orderGoods;
    /**
     * @var UserShare $share
     */
    protected $share;
    /**
     * @var UserExp $exp
     */
    protected $exp;

    protected $totalGain;

    protected $expCreatorGain = 0; // 体验创作者收益
    protected $articleCreatorGain = 0; // 文章创作者收益
    protected $sharerGain = 0; // 分享者收益
    protected $platformGain = 0; // 平台分成
    protected $merchantGain = 0; // 商户入账

    protected $expCreator; // 体验创作者
    protected $articleCreator; // 文章创作者
    protected $sharer; // 分享者
    protected $merchant; // 商户


    const RATE_EXP_CREATOR = 0.05; // 体验创作者分成率
    const RATE_EXP_SHARER = 0.05; // 体验转发者分成率
    const RATE_ARTICLE_CREATOR = 0.05; // 文章创作者分成率
    const RATE_SHARER = 0.1; // 分享者分成率
    const RATE_PLATFORM = 0.05; // 平台分成率

    /**
     * @param OrderWasReceived $event
     * @throws \Exception
     */
    public function handle(OrderWasReceived $event)
    {
        try {
            \DB::beginTransaction();
            $this->order = Order::query()
                ->where('id', $event->order->id)
//                ->where('status', Order::S_RECEIVED)
                ->lockForUpdate()
                ->first();

            if ($this->order) {
                foreach ($this->order->goods as $orderGoods) {
                    // 初始化涉及分成相关主体
                    $this->initGainHolder($orderGoods);
                    // 计算主体收益金额
                    $this->calGain();
                    // 结算
                    $this->settlement();
                    // 修改分享累计收益
                    $this->incShareIncome();
                }

                // 修改分享状态
                $this->changeOrderStatus();
            }

            \DB::commit();
        } catch (\Exception $e) {
            \Log::error('资金分流异常', [
                'err_msg' => $e->getMessage(),
                'trace'   => $e->getTrace()
            ]);
            \DB::rollBack();
        }
    }

    protected function initGainHolder($orderGoods)
    {
        $this->clear();

        $this->orderGoods = $orderGoods;
        $this->totalGain  = $this->orderGoods->pay_amount;

        /** @var UserShare $share */
        $share       = $this->orderGoods->share()->first();
        $this->share = $share;

        if ($share) {
            switch ($share->target) {
                case UserShare::T_EXP:
                    /** @var UserExp $exp */
                    $exp              = $share->targetExp()->first();
                    $this->exp        = $exp;
                    $this->expCreator = $exp->user()->first();
                    break;
                case UserShare::T_ARTICLE:
                    /** @var Article $article */
                    $article              = $share->targetArticle()->first();
                    $this->articleCreator = $article->publisher;
                    break;
                default:
                    break;
            }

            $this->sharer = $share->user()->first();
        }
        $this->merchant = $this->merchant ?: $this->order->store()->first();
    }

    protected function clear()
    {
        $this->share = null;
        $this->exp   = null;

        $this->sharer         = null;
        $this->expCreator     = null;
        $this->articleCreator = null;

        $this->sharerGain         = 0;
        $this->expCreatorGain     = 0;
        $this->articleCreatorGain = 0;
        $this->platformGain       = 0;
        $this->merchantGain       = 0;
    }

    protected function calGain()
    {
        $this->calSharerGain();
        $this->calExpCreatorGain();
        $this->calArticleCreatorGain();
        $this->calPlatformGain();
        $this->calMerchantGain();
    }

    /**
     * 分享者收益
     */
    protected function calSharerGain()
    {
        if ($this->sharer && !$this->sharerIsExpCreator()) {
            $rate = self::RATE_SHARER;
            // 如果转发分享的是体验 按转发体验的佣金算
            if ($this->expCreator) {
                $rate = self::RATE_EXP_SHARER;
            }
            $this->sharerGain = my_mul($this->totalGain, $rate);
        }
    }

    /**
     * 体验创作者收益
     */
    protected function calExpCreatorGain()
    {
        if ($this->expCreator) {
            $rate = self::RATE_EXP_CREATOR;
            // 如果分享者是体验创作者 创作佣金应加上分享佣金
            if ($this->sharerIsExpCreator()) {
                $rate = $rate + self::RATE_EXP_SHARER;
            }
            $this->expCreatorGain = my_mul($this->totalGain, $rate);
        }
    }

    /**
     * 文章创造者收益
     * ---只有平台创出才有创作分层，商家创作不计入分层
     */
    protected function calArticleCreatorGain()
    {
        if ($this->articleCreator === Article::P_PLATFORM) {
            $this->articleCreatorGain = my_mul($this->totalGain, self::RATE_ARTICLE_CREATOR);
        }
    }

    /**
     * 平台收益
     */
    protected function calPlatformGain()
    {
        $this->platformGain = my_mul($this->totalGain, self::RATE_PLATFORM);
    }

    /**
     * 商家收益
     */
    protected function calMerchantGain()
    {
        $this->merchantGain = array_reduce([
            $this->expCreatorGain,
            $this->articleCreatorGain,
            $this->sharerGain,
            $this->platformGain
        ], function ($gain, $reduceGain) {
            return my_sub($gain, $reduceGain);
        }, $this->totalGain);
    }

    /**
     * 入账
     * @throws \Exception
     */
    protected function settlement()
    {
        $this->settleSharer();
        $this->settleExpCreator();
        $this->settleArticleCreator();
        $this->settleMerchant();
        $this->settlePlatform();
    }

    /**
     * 分享者入账
     */
    protected function settleSharer()
    {
        if ($this->sharer && !$this->sharerIsExpCreator()) {
            UserTransaction::payIn([
                'type'     => UserTransaction::T_SHARE,
                'note'     => UserTransaction::N_USER_SHARE,
                'user_id'  => $this->sharer->id,
                'share_id' => $this->share->id,
                'order_id' => $this->orderGoods->id,
                'amount'   => $this->sharerGain,
            ]);
            PlatformTransaction::payOut([
                'type'      => PlatformTransaction::TYPE_SHARE,
                'target'    => PlatformTransaction::T_USER,
                'note'      => PlatformTransaction::N_USER_SHARE,
                'target_id' => $this->sharer->id,
                'refer_id'  => $this->orderGoods->id,
                'amount'    => $this->sharerGain,
            ]);
        }
    }

    /**
     * 体验创作者入账
     */
    protected function settleExpCreator()
    {
        if ($this->expCreator) {
            UserTransaction::payIn([
                'type'     => UserTransaction::T_CREAT,
                'note'     => UserTransaction::N_USER_CREAT,
                'user_id'  => $this->expCreator->id,
                'share_id' => $this->share->id,
                'order_id' => $this->orderGoods->id,
                'amount'   => $this->expCreatorGain,
            ]);
            PlatformTransaction::payOut([
                'type'      => PlatformTransaction::TYPE_SHARE,
                'target'    => PlatformTransaction::T_USER,
                'note'      => PlatformTransaction::N_USER_CREAT,
                'target_id' => $this->expCreator->id,
                'refer_id'  => $this->orderGoods->id,
                'amount'    => $this->expCreatorGain,
            ]);
        }
    }

    /**
     * 文章创作者入账
     * ---只有平台创作有分成
     */
    protected function settleArticleCreator()
    {
        if ($this->articleCreator === Article::P_PLATFORM) {
            PlatformTransaction::payIn([
                'type'      => PlatformTransaction::TYPE_CREAT_ARTICLE,
                'target'    => PlatformTransaction::T_PLATFORM,
                'note'      => PlatformTransaction::N_PLATFORM_CREAT,
                'target_id' => '',
                'refer_id'  => $this->orderGoods->id,
                'amount'    => $this->articleCreatorGain,
            ]);
            PlatformTransaction::payOut([
                'type'      => PlatformTransaction::TYPE_CREAT_ARTICLE,
                'target'    => PlatformTransaction::T_PLATFORM,
                'note'      => PlatformTransaction::N_PLATFORM_CREAT,
                'target_id' => '',
                'refer_id'  => $this->orderGoods->id,
                'amount'    => $this->articleCreatorGain,
            ]);
        }
    }

    /**
     * 平台入账
     */
    protected function settlePlatform()
    {
        PlatformTransaction::payIn([
            'type'      => PlatformTransaction::TYPE_GOODS_SELL,
            'target'    => PlatformTransaction::T_PLATFORM,
            'note'      => PlatformTransaction::N_MERCHANT_SELL,
            'target_id' => '',
            'refer_id'  => $this->orderGoods->id,
            'amount'    => $this->platformGain,
        ]);
        PlatformTransaction::payOut([
            'type'      => PlatformTransaction::TYPE_GOODS_SELL,
            'target'    => PlatformTransaction::T_PLATFORM,
            'note'      => PlatformTransaction::N_MERCHANT_SELL,
            'target_id' => '',
            'refer_id'  => $this->orderGoods->id,
            'amount'    => $this->platformGain,
        ]);
    }

    /**
     * 商家入账
     */
    protected function settleMerchant()
    {
        MerchantTransaction::payIn([
            'note'     => MerchantTransaction::N_GOODS_SELL,
            'type'     => MerchantTransaction::T_SALE,
            'store_id' => $this->merchant->id,
            'refer_id' => $this->orderGoods->id,
            'amount'   => $this->merchantGain,
        ]);
        PlatformTransaction::payOut([
            'type'      => PlatformTransaction::TYPE_GOODS_SELL,
            'target'    => PlatformTransaction::T_MERCHANT,
            'note'      => PlatformTransaction::N_MERCHANT_SELL,
            'target_id' => $this->merchant->id,
            'refer_id'  => $this->orderGoods->id,
            'amount'    => $this->merchantGain,
        ]);
    }

    protected function incShareIncome()
    {
        /**
         * 累计分享链接收益
         */
        if ($this->share && $this->sharerGain > 0) {
            UserShare::query()
                ->where('id', $this->share->id)
                ->increment('income', $this->sharerGain);
        }

        /**
         * 累计体验分享收益
         */
        if ($this->exp && $this->expCreatorGain > 0) {
            UserExp::query()
                ->where('id', $this->exp->id)
                ->increment('income', $this->expCreatorGain);
        }
    }

    protected function changeOrderStatus()
    {
        /**
         * 修改订单状态为已完成
         */
//        $this->order->update([
//            'status' => Order::S_COMPLETED
//        ]);
    }

    protected function sharerIsExpCreator()
    {
        $is = false;
        if ($this->expCreator) {
            $is = $this->sharer->id === $this->expCreator->id;
        }
        return $is;
    }
}
