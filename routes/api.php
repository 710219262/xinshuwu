<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/** @var \Laravel\Lumen\Routing\Router $router */
$router->group(['namespace' => 'Api', 'prefix' => 'v1'], function () use ($router) {
    $router->post('pay/wechat/notify', 'Pay\PayController@wechatNotify');
    $router->post('pay/alipay/notify', 'Pay\PayController@alipayNotify');

    $router->post('vip-pay/wechat/notify', 'Pay\VipCardPay@wechatNotify');
    $router->post('vip-pay/alipay/notify', 'Pay\VipCardPay@alipayNotify');

    $router->post('withdraw/alipay/notify', 'Pay\Transfer@alipayNotify');

    $router->post('refund/wechat/notify', 'Pay\Refund@wechatNotify');

    $router->group(['prefix' => 'user', 'namespace' => 'User'], function () use ($router) {
        $router->post('auth', 'User@auth');
        $router->post('register', 'User@register');
        $router->post('auth/code', 'User@getCode');
        $router->post('wechat/openid', 'Wechat@getOpenId');
        $router->get('wechat/share', 'Wechat@getShare');
        $router->get('wechat/config', 'Wechat@getConfig');
        $router->post('wechat/userinfo', 'Wechat@getUserInfo');
        $router->post('wechat/bindphone', 'Wechat@bindPhone');
        $router->get('appguess/info', 'Guess@app_info');
        $router->post('appguess', 'Guess@app_create');
        $router->get('appguess/guessno', 'Guess@guessno2');
        $router->group(['middleware' => 'tk'], function () use ($router) {
            // user
            $router->get('share', 'Share@list');
            $router->get('share/link', 'Share@linkList');
            $router->get('share/income', 'Share@income');
            $router->get('share/withdraw', 'Share@withdrawInfo');
            $router->post('share', 'Share@create');

            $router->get('info', 'User@info');
            $router->put('tag', 'User@updateTag');
            $router->put('info', 'User@updateInfo');
            $router->get('followed', 'User@getFollowedList');
            $router->get('follower', 'User@getFollowerList');
            $router->post('followed', 'User@followUser');
            $router->delete('followed', 'User@unfollowUser');
            // collection
            $router->post('collection', 'Collection@create');
            $router->get('collection', 'Collection@list');
            $router->delete('collection', 'Collection@delete');
            // address
            $router->get('address', 'Address@getList');
            $router->get('address/info', 'Address@info');
            $router->post('address', 'Address@create');
            $router->put('address', 'Address@update');
            $router->put('address/default', 'Address@setDefault');
            $router->delete('address', 'Address@delete');
            // shopping cart
            $router->get('shopping-cart', 'ShoppingCart@list');
            $router->post('shopping-cart', 'ShoppingCart@add');
            $router->put('shopping-cart', 'ShoppingCart@update');
            $router->delete('shopping-cart', 'ShoppingCart@delete');
            $router->get('shopping-cart/calc', 'ShoppingCart@calc');
            $router->get('shopping-cart/instantcalc', 'ShoppingCart@instantcalc');
            $router->get('shopping-cart/recommend', 'ShoppingCart@recommend');
            // order
            $router->get('orders', 'Order@list');
            $router->get('order', 'Order@info');
            $router->post('order', 'Order@createViaCart');
            $router->delete('order', 'Order@hide');
            $router->put('order', 'Order@cancel');
            $router->post('order/instant', 'Order@createInstant');
            $router->post('order/prepay/alipay', 'Order@prepayViaAli');
            $router->post('order/prepay/wechat', 'Order@prepayViaWechat');
            $router->post('order/prepay/wechatjsapi', 'Order@prepayViaWechatJsapi');
            $router->post('order/refund', 'Order@refund');
            $router->post('order/receive', 'Order@receive');
            $router->get('order/logistic', 'Order@updateLogistic');
            $router->get('order/goodsinfo', 'Order@goodsinfo');
            // vip card order
            $router->post('vip-order', 'VipOrder@create');
            $router->post('vip-order/prepay', 'VipOrder@prepay');

            // transaction
            $router->post('transaction/withdraw', 'Transaction@withDraw');
            $router->get('transaction/balance', 'Transaction@balance');
            $router->get('transaction/code', 'Transaction@code');

            // app
            $router->post('feedback', 'Feedback@create');
            // notification
            $router->get('notification', 'Notification@list');
            $router->put('notification/read', 'Notification@read');

            // after sale
            $router->get('aftersale', 'AfterSale@list');
            $router->post('aftersale', 'AfterSale@create');
            $router->post('aftersale/cancel', 'AfterSale@cancel');
            $router->get('aftersale/info', 'AfterSale@info');
            $router->get('aftersale/reasons', 'AfterSale@reasons');
            $router->post('aftersale/dispatch', 'AfterSale@dispatchGoods');
            $router->get('aftersale/status', 'AfterSale@status');

            //guess
            $router->get('guess/stage', 'Guess@detail');
            $router->get('guess/info', 'Guess@info');
            $router->post('guess', 'Guess@create');
            $router->get('guess/guessno', 'Guess@guessno');
        });
        // tourists
        $router->group(['middleware' => 't', 'prefix' => 'other'], function () use ($router) {
            $router->get('share', 'Other@list');
            $router->get('share/link', 'Other@linkList');
            $router->get('info', 'Other@info');
        });
    });

    $router->group(['prefix' => 'discovery', 'namespace' => 'Discovery'], function () use ($router) {
        // tourists
        $router->group(['middleware' => 't'], function () use ($router) {
            $router->get('/', 'Exp@list');
            $router->get('exp', 'Exp@info');
            $router->get('exp/comments', 'ExpCmt@list');
        });

        $router->group(['middleware' => 'tk'], function () use ($router) {
            // experience
            $router->post('exp', 'Exp@create');
            $router->get('exp/goods', 'Exp@goods');
            $router->put('exp/like', 'Exp@like');
            $router->put('exp/unlike', 'Exp@unlike');
            $router->post('exp/collect', 'Exp@collect');
            // experience comment
            $router->post('exp/comments', 'ExpCmt@create');
            $router->put('exp/comments/like', 'ExpCmt@like');
            $router->put('exp/comments/unlike', 'ExpCmt@unlike');
        });
    });

    $router->group(['prefix' => 'index', 'namespace' => 'Index'], function () use ($router) {
        // tourists
        $router->group(['middleware' => 't'], function () use ($router) {
            $router->get('/', 'Article@list');
            $router->get('article', 'Article@info');
            $router->get('article/comments', 'ArticleCmt@list');
            $router->get('mallhome', 'MallHome@list');
            $router->get('mallhome/slider', 'MallHome@slider');
            $router->post('x-search', 'XSearch@update');
        });
        $router->group(['middleware' => 'tk'], function () use ($router) {
            // article
            $router->put('article/like', 'Article@like');
            $router->put('article/unlike', 'Article@unlike');
            $router->post('article/collect', 'Article@collect');
            // article comments
            $router->post('article/comments', 'ArticleCmt@create');
            $router->put('article/comments/like', 'ArticleCmt@like');
            $router->put('article/comments/unlike', 'ArticleCmt@unlike');
        });
    });

    $router->group(['prefix' => 'mall', 'namespace' => 'Mall'], function () use ($router) {
        // tourists
        $router->group(['middleware' => 't'], function () use ($router) {
            $router->get('goods', 'Goods@list');
            $router->get('goods/info', 'Goods@info');
            $router->get('goods/recommend', 'Goods@recommend');
        });
    });

    $router->group(['prefix' => 'store', 'namespace' => 'Store'], function () use ($router) {
        // tourists
        $router->group(['middleware' => 't'], function () use ($router) {
            $router->get('info', 'Store@info');
            $router->get('goods', 'Store@goodsList');
        });
    });

    $router->group(['prefix' => 'common', 'namespace' => 'Common'], function () use ($router) {
        $router->post('upload', 'Common@upload');
        $router->get('categories', 'Common@getCategories');
        $router->get('region', 'Common@getRegionList');
        $router->get('config', 'Common@getServerConf');
        $router->get('logistic/company', 'Common@getLogisticCompany');
    });

    $router->group(['prefix' => 'share', 'namespace' => 'Share'], function () use ($router) {
        $router->group(['middleware' => 't'], function () use ($router) {
            $router->get('/', 'Share@jump');
            $router->get('rank', 'Share@getRank');
        });
    });
});
