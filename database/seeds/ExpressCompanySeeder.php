<?php

use App\Models\ExpressCompany;
use Illuminate\Database\Seeder;

/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 20/04/2019
 * Time: 20:40
 */
class ExpressCompanySeeder extends Seeder
{
    const LINE_COUNT = 294;
    
    public function run()
    {
        $content = file_get_contents(__DIR__ . '/../data/express_company.txt');
        $items = explode(PHP_EOL, $content);
        
        if (self::LINE_COUNT !== count($items)) {
            echo "FILE CORRUPTED, PLEASE REPAIR AND TRY LATER !!" . PHP_EOL;
            return;
        }
        
        for ($i = 0; $i < count($items);) {
            ExpressCompany::query()->create([
                'name' => $items[$i++],
                'abbr' => $items[$i++],
            ]);
        }
    }
}
