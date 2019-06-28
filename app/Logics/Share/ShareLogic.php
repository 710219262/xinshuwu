<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 24/04/2019
 * Time: 23:29
 */

namespace App\Logics\Share;

use App\Models\GoodsInfo;
use App\Models\User;
use App\Models\UserShare;

class ShareLogic
{
    /**
     * @param User $shareUser
     * @param      $target
     * @param      $targetId
     * @param      $goodsId
     *
     * @return string
     */
    public function createShare(User $shareUser, $target, $targetId, $goodsId)
    {
        /** @var GoodsInfo $goods */
        $goods = GoodsInfo::query()->withTrashed()->find($goodsId);
        
        /** @var UserShare $share */
        $share = UserShare::query()->firstOrCreate([
            'user_id'   => $shareUser->id,
            'target_id' => $targetId,
            'target'    => $target,
        ], [
            'user_id'   => $shareUser->id,
            'goods_id'  => $goodsId,
            'store_id'  => $goods->store_id,
            'target_id' => $targetId,
            'target'    => $target,
            'aff'       => UserShare::newAff($target, $targetId, $shareUser->id),
        ]);
        
        return UserShare::buildLink($share->aff);
    }

    /**
     * @param User $shareUser
     * @param      $target
     * @param      $targetId
     * @param      $goodsId
     *
     * @return string
     */
    public function createShareWap(User $shareUser, $target, $targetId, $goodsId)
    {
        /** @var GoodsInfo $goods */
        $goods = GoodsInfo::query()->withTrashed()->find($goodsId);

        /** @var UserShare $share */
        $share = UserShare::query()->firstOrCreate([
            'user_id'   => $shareUser->id,
            'target_id' => $targetId,
            'target'    => $target,
        ], [
            'user_id'   => $shareUser->id,
            'goods_id'  => $goodsId,
            'store_id'  => $goods->store_id,
            'target_id' => $targetId,
            'target'    => $target,
            'aff'       => UserShare::newAff($target, $targetId, $shareUser->id),
        ]);
        switch ($share->target) {
            case UserShare::T_ARTICLE:
                $path = config('xsw.sharewap.wap_article_path');
                break;
            case UserShare::T_GOODS:
                $path = config('xsw.sharewap.wap_goods_path');
                break;
            case UserShare::T_EXP:
                $path = config('xsw.share.exp_path');
                break;
            default:
                $path = '';
                break;
        }
        $res = '';
        switch ($share->target) {
            case UserShare::T_ARTICLE:
                $res = sprintf(
                    "%s/%s/%s?aff=%s",
                    config('xsw.sharewap.wap_share_prefix'),
                    $path,
                    $share->target_id,
                    $share->aff
                );
                break;
            case UserShare::T_GOODS:
                $res = sprintf(
                    "%s/%s/%s?aff=%s",
                    config('xsw.sharewap.wap_share_prefix'),
                    $path,
                    $share->target_id,
                    $share->aff
                );
                break;
            case UserShare::T_EXP:
                $res = sprintf(
                    "%s/%s?id=%s&aff=%s",
                    config('xsw.share.h5_share_prefix'),
                    $path,
                    $share->target_id,
                    $share->aff
                );
                break;
            default:
                $res = '';
                break;
        }
        return $res;
    }
    /**
     * @param $aff
     *
     * @return \Illuminate\Http\RedirectResponse|\Laravel\Lumen\Http\Redirector
     */
    public function jack($aff)
    {
        /** @var UserShare $share */
        $share = UserShare::query()->where('aff', $aff)
            ->first();
        
        if (empty($share)) {
            return redirect(config('xsw.share.404'));
        }
        
        $share::query()->increment('view');
        
        switch ($share->target) {
            case UserShare::T_ARTICLE:
                $path = config('xsw.share.article_path');
                break;
            case UserShare::T_GOODS:
                $path = config('xsw.share.goods_path');
                break;
            case UserShare::T_EXP:
                $path = config('xsw.share.exp_path');
                break;
            default:
                return redirect(config('xsw.share.404'));
        }
        
        return redirect(
            sprintf(
                "%s/%s?id=%s&aff=%s",
                config('xsw.share.h5_share_prefix'),
                $path,
                $share->target_id,
                $share->aff
            )
        );
    }
}
