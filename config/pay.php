<?php

return [
    'alipay' => [
        // 支付宝分配的 APP ID
        'app_id'         => env('ALI_APP_ID', ''),
        
        // 支付宝异步通知地址
        'notify_url'     => env('ALI_ASYNC_NOTIFY_URL'),
        
        // 支付成功后同步通知地址
        'return_url'     => env('ALI_SYNC_NOTIFY_URL'),
        
        // 阿里公共密钥，验证签名时使用
        'ali_public_key' => env('ALI_PUBLIC_KEY', ''),
        
        // 自己的私钥，签名时使用
        'private_key'    => env('ALI_PRIVATE_KEY', ''),
        
        'log' => [
            'file'     => storage_path('logs/alipay.log'),
            'level'    => 'info',
            'type'     => 'daily',
            'max_file' => 30,
        ],
        
        // optional，设置此参数，将进入沙箱模式
        // 'mode' => env('PAY_SANDBOX', 'dev'),
    ],
    
    'wechat' => [
        // APP 引用的 appid
        'app_id'       => env('WECHAT_APPID_MP', ''),
        'appid'       => env('WECHAT_APPID', ''),

        // 微信支付分配的微信商户号
        'mch_id'      => env('WECHAT_MCH_ID', ''),
        
        // 微信支付异步通知地址
        'notify_url'  => env('WECHAT_NOTIFY'),
        
        // 微信支付签名秘钥
        'key'         => env('WECHAT_KEY', ''),
        
        // 客户端证书路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
        'cert_client' => storage_path('app/apiclient_cert.pem'),
        
        // 客户端秘钥路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
        'cert_key'    => storage_path('app/apiclient_key.pem'),
        // log
        'log'         => [
            'file'     => storage_path('logs/wechat-pay.log'),
            'level'    => 'info',
            'type'     => 'daily',
            'max_file' => 30,
        ],
        
        // optional
        // 'dev' 时为沙箱模式
        // 'hk' 时为东南亚节点
        // 'mode' => 'dev',
    ]
];
