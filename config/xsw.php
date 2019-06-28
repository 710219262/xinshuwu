<?php

return [
    'vip_discount'    => env('VIP_DISCOUNT', 0.95),
    'share'           => [
        '404'             => env('404'),
        'jump_prefix'     => env('JUMP_PREFIX'),
        'h5_share_prefix' => env('H5_SHARE_PREFIX'),
        'goods_path'      => env('GOODS_PATH'),
        'exp_path'        => env('EXP_PATH'),
        'article_path'    => env('ARTICLE_PATH'),
    ],
    'sharewap'           => [
        'wap_share_prefix' => env('WAP_SHARE_PREFIX'),
        'wap_goods_path'      => env('WAP_GOODS_PATH'),
        'wap_article_path'    => env('WAP_ARTICLE_PATH'),
    ],
    'order_version'   => 1.0,
    'vip_member'      => [
        'month_card_price'  => env('VIP_MONTH_CARD_PRICE', 38),
        'season_card_price' => env('VIP_SEASON_CARD_PRICE', 88),
        'year_card_price'   => env('VIP_YEAR_CARD_PRICE', 268),
        'ali_notify_url'    => env('VIP_ALI_NOTIFY_URL'),
        'wechat_notify_url' => env('VIP_WECHAT_NOTIFY_URL'),
    ],
    'withdraw'        => [
        'ali_notify_url' => env('WITHDRAW_ALI_NOTIFY_URL'),
    ],
    'aftersale'       => [
        'wechat_notify_url' => env('REFUND_WECHAT_NOTIFY_URL'),
    ],
    'whitelist'       => explode(',', env('WHITE_LIST', "")),
    'express_company' => [
        '京东',
        '顺丰',
        '百世快递',
        '圆通',
        '申通',
        '韵达',
        '百世快运',
        '中邮物流',
        '德邦',
        'EMS',
        '中通',
        '邮政包裹',
        '天天',
    ],
];
