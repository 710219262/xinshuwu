<?php

use App\Models\GoodsCategory;
use Illuminate\Database\Seeder;

class GoodsCategorySeeder extends Seeder
{
    const CATEGORY = [
        '家居电器',
        '数码科技',
        '美妆护肤',
        '服饰箱包',
    ];
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::CATEGORY as $name) {
            GoodsCategory::query()
                ->create([
                    'name' => $name,
                ]);
        }
    }
}
