<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateXswMerchantInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xsw_merchant_info', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('account_id')->unsigned()->comment('店铺账号');
            
            $table->enum('trade_mode', ['MERCHANT', 'SINGLE'])->default('MERCHANT')->comment('贸易模式');
            $table->integer('ship_region_id')->unsigned()->comment('发货地区id');
            $table->json('ship_region_ids')->comment('发货地区ids');
            $table->string('ship_addr')->comment('发货详细地址');
            
            $table->integer('company_region_id')->unsigned()->comment('公司注册地区id');
            $table->json('company_region_ids')->comment('公司注册地区ids');
            
            $table->string('company_name', 128)->default('')->comment('公司名称');
            
            $table->string('license_url')->default('')->comment('营业执照地址');
            $table->string('credit_code', 32)->default('')->comment('统一社会信用代码');
            $table->string('license_register_addr', 128)->default('')->comment('营业执照注册地址');
            $table->dateTime('license_validate_before')->comment('营业执照有效期');
            
            $table->string('id_card_front')->default('')->comment('身份证正面');
            $table->string('id_card_back')->default('')->comment('身份证背面');
            
            $table->string('product_brand', 64)->default('')->comment('产品品牌');
            $table->integer('product_category_id')->comment('产品分类id');
            $table->string('product_link')->default('')->comment('产品链接');
            
            $table->string('contact', 32)->default('')->comment('店铺联系人');
            $table->string('phone', 16)->default('')->comment('手机号');
            $table->string('wechat', 64)->default('')->comment('微信');
            $table->string('emg_contact', 32)->default('')->comment('紧急联系人');
            $table->string('emg_phone', 16)->default('')->comment('紧急联系人电话');
            
            $table->integer('consignee_region_id')->unsigned()->comment('收件地址id');
            $table->json('consignee_region_ids')->comment('收件地址ids');
            
            $table->string('consignee_addr', 128)->default('')->comment('具体收件地址');
            
            
            $table->tinyInteger('status')->default(-1)->comment('审核状态');
            $table->string('reject_reason')->default('')->comment('审核被拒原因');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('account_id');
            $table->index('status');
            $table->index('trade_mode');
            $table->index('ship_region_id');
            $table->index('product_category_id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xsw_merchant_info');
    }
}
