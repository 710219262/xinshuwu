<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RegionSeeder::class);
        $this->call(GoodsCategorySeeder::class);
        $this->call(GoodsSpecSeeder::class);
        $this->call(ExpressCompanySeeder::class);
        $this->call(MallHomeSeeder::class);
    }
}
