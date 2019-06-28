<?php

namespace App\Providers;

use App\Events\Article\ArticleWasCommented;
use App\Events\Article\CommentWasLiked as ArticleCmtWasLiked;
use App\Events\Exp\CommentWasLiked;
use App\Events\Exp\ExpWasCollected;
use App\Events\Exp\ExpWasCommented;
use App\Events\Exp\ExpWasLiked;
use App\Events\Order\AfterSaleRefund;
use App\Events\Order\AfterSaleWasUpdated;
use App\Events\Order\OrderWasReceived;
use App\Events\Order\OrderWasUpdated;
use App\Events\Order\VipOrderWasPaid;
use App\Events\Transaction\MerchantWithdrawAudited;
use App\Events\Transaction\UserWithdrawAudited;
use App\Events\User\UserWasFollowed;
use App\Listeners\AfterSaleRefundListener;
use App\Listeners\AfterSaleWasUpdatedListener;
use App\Listeners\MerchantWithdrawAuditedListener;
use App\Listeners\OrderWasReceivedListener;
use App\Listeners\OrderWasUpdatedListener;
use App\Listeners\UserWithdrawAuditedListener;
use App\Listeners\VipOrderWasPaidListener;
use App\Listeners\WasCollectedListener;
use App\Listeners\WasCommentedListener;
use App\Listeners\WasFollowedListener;
use App\Listeners\WasLikedListener;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        CommentWasLiked::class         => [
            WasLikedListener::class,
        ],
        ExpWasLiked::class             => [
            WasLikedListener::class,
        ],
        ExpWasCollected::class         => [
            WasCollectedListener::class,
        ],
        ExpWasCommented::class         => [
            WasCommentedListener::class,
        ],
        ArticleWasCommented::class     => [
            WasCommentedListener::class,
        ],
        ArticleCmtWasLiked::class      => [
            WasLikedListener::class,
        ],
        OrderWasUpdated::class         => [
            OrderWasUpdatedListener::class,
        ],
        OrderWasReceived::class        => [
            OrderWasReceivedListener::class,
        ],
        VipOrderWasPaid::class         => [
            VipOrderWasPaidListener::class,
        ],
        MerchantWithdrawAudited::class => [
            MerchantWithdrawAuditedListener::class,
        ],
        UserWithdrawAudited::class     => [
            UserWithdrawAuditedListener::class,
        ],
        UserWasFollowed::class         => [
            WasFollowedListener::class,
        ],
        AfterSaleWasUpdated::class         => [
            AfterSaleWasUpdatedListener::class,
        ],
        AfterSaleRefund::class         => [
            AfterSaleRefundListener::class,
        ],
    ];
}
