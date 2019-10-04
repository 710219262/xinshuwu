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
$router->group(['namespace' => 'MMS', 'prefix' => 'mms'], function () use ($router) {
    $router->get('test', 'Index@userList');//测试用户列表1

    $router->get('tests', 'Index@feedBack');//测试用户列表
    $router->post('auth', 'Common@auth');
    $router->post('authpass', 'Common@authpass');
    $router->post('auth/code', 'Common@getSmsCode');
    $router->post('upload', 'Common@upload');
    $router->get('regions', 'Common@getRegionList');
    $router->get('goods/category', 'Common@getGoodsCategory');
    $router->get('getosskey', 'Common@getGetkey');
    $router->post('logout', 'Common@logout');
    $router->group(['middleware' => 'auth'], function () use ($router) {
        $router->get('user/info', 'Common@getUserInfo');
        $router->get('company', 'Common@getExpressCompany');
        //***************************
        //* merchant related routers
        //***************************
        $router->group(['namespace' => 'Merchant', 'prefix' => 'merchant'], function () use ($router) {
            $router->post('/', 'Merchant@create');
            $router->put('/', 'Merchant@update');
            $router->get('ocr', 'Merchant@ocrLicense');
            $router->get('status', 'Merchant@getSignInStatus');
            $router->get('info', 'Merchant@getMerchantInfo');

            $router->get('store/info', 'Store@getStoreInfo');
            $router->post('store/info', 'Store@updateStoreInfo');

            $router->get('goods/spec', 'Goods@getGoodsSpecList');
            $router->get('goods/spec/{id}', 'Goods@getGoodsSpecInfo');
            $router->post('goods/spec', 'Goods@addGoodsSpec');

            $router->post('goods', 'Goods@createGoods');
            $router->put('goods', 'Goods@updateGoods');
            $router->delete('goods', 'Goods@deleteGoods');
            $router->post('goods/list', 'Goods@getGoodsList');
            $router->get('goods/info', 'Goods@getGoodsInfo');

            $router->post('orders', 'Order@list');
            $router->get('order', 'Order@info');
            $router->put('order/dispatch', 'Order@dispatchGoods');

            $router->get('article', 'Article@info');
            $router->put('article', 'Article@update');
            $router->post('articles', 'Article@list');
            $router->post('article', 'Article@create');
            $router->put('articles', 'Article@batchChangeStatus');
            $router->delete('articles', 'Article@batchDelete');

            // transaction
            $router->get('transaction', 'Transaction@list');
            $router->post('transaction/withdraw', 'Transaction@withDraw');
            $router->get('transaction/withdraw', 'Transaction@withdrawList');
            $router->get('transaction/account', 'Transaction@account');
            $router->get('transaction/account-today', 'Transaction@accountToday');
            $router->get('transaction/code', 'Transaction@code');

            // after sale
            $router->get('aftersale/list', 'AfterSale@list');
            $router->post('aftersale/audit', 'AfterSale@audit');
            $router->post('aftersale/receive', 'AfterSale@receive');

            $router->get('order/statistics', 'Order@statistics');
            $router->get('order/statistics-today', 'Order@statisticsToday');
        });
        //***************************
        //* admin related routers
        //***************************
        $router->group([
            'namespace'  => 'Admin',
            'prefix'     => 'admin',
            'middleware' => "auth:admin",
        ], function () use ($router) {
            $router->get('merchant', 'Merchant@getMerchantList');
            $router->post('merchant/list', 'Merchant@getStoreList');
            $router->get('merchant/info', 'Merchant@getMerchantInfo');
            $router->post('merchant/check', 'Merchant@checkMerchant');
            $router->get('merchant/transaction', 'Merchant@transactionList');
            $router->get('merchant/transaction2', 'Merchant@transactionList2');
            // transaction
            $router->get('merchant/withdraw', 'Merchant@getWithdrawList');
            $router->post('merchant/audit', 'Merchant@auditWithdraw');
            $router->get('user/withdraw', 'User@getWithdrawList');
            $router->post('user/audit', 'User@auditWithdraw');

            $router->post('goods', 'Goods@getList');
            $router->delete('goods', 'Goods@deleteGoods');
            $router->get('goods/info', 'Goods@getGoodsInfo');
            $router->get('goods/spec', 'Goods@getGoodsSpecList');
            $router->get('goods/spec/{id}', 'Goods@getGoodsSpecInfo');

            $router->get('article', 'Article@info');

            $router->post('articles', 'Article@list');
            $router->post('article', 'Article@create');
            $router->put('article', 'Article@update');
            $router->put('articles', 'Article@batchChangeStatus');
            $router->delete('articles', 'Article@batchDelete');

            $router->post('videos', 'Video@list');
            $router->post('video', 'Video@create');
            $router->get('mallhome', 'MallHome@list');
            $router->put('mallhome', 'MallHome@update');

            $router->post('orders', 'Order@list');
            $router->get('order', 'Order@info');
            $router->put('order/dispatch', 'Order@dispatchGoods');
            $router->put('order', 'Order@update');
            $router->get('order/aftersale', 'Order@aftersaleList');

            $router->post('exp', 'Exp@list');
            $router->get('exp/info', 'Exp@info');
            $router->put('exp', 'Exp@update');

            $router->get('guess/stage', 'Guess@stagelist');
            $router->get('guess/stageinfo', 'Guess@stageinfo');
            $router->post('guess/stage', 'Guess@stageupdate');
            $router->post('guess/tongji', 'Guess@tongji');
            $router->post('guess/users', 'Guess@users');

            $router->post('users', 'User@list');

            $router->post('transaction/user', 'Transaction@userlist');
            $router->post('transaction/merchant', 'Transaction@merchantlist');
            $router->post('transaction/platform', 'Transaction@platformlist');
        });
    });
});
