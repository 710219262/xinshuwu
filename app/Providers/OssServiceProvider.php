<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 23/12/2018
 * Time: 16:14
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use OSS\OssClient;

class OssServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('aliyun-oss', function () {
            return new OssClient(
                config('aliyun.access_key'),
                config('aliyun.access_secret'),
                config('aliyun.oss.endpoint')
            );
        });
        
        $this->app->alias('aliyun-oss', 'OSS\OssClient');
    }
    
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['aliyun-oss', 'OSS\OssClient'];
    }
}
