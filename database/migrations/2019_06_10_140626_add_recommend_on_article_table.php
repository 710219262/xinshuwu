<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecommendOnArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('xsw_article', function (Blueprint $table) {
            $table->tinyInteger('recommend')->unsigned()
                ->after('label')
                ->default(0)
                ->comment('推荐状态');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('xsw_article', function (Blueprint $table) {
            $table->dropColumn('recommend');
        });
    }
}
