<?php

use App\Models\GoodsSpec;
use Illuminate\Database\Seeder;

class GoodsSpecSeeder extends Seeder
{
    public function run()
    {
        $specs = $this->parseJson(__DIR__ . '/../data/specs.json');
        
        foreach ($specs as $spec) {
            GoodsSpec::query()
                ->create([
                    'name' => $spec,
                ]);
        }
    }
    
    protected function parseJson($file)
    {
        $data = file_get_contents($file);
        return json_decode($data, true);
    }
}
