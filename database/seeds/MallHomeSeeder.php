<?php

use Illuminate\Database\Seeder;
use App\Models\MallHome;

class MallHomeSeeder extends Seeder
{
    const MallHomeData= [
        ['id'=>1,'pid'=>0,'level'=>1,'title'=>'楼层1：轮播图'],
        ['id'=>2,'pid'=>0,'level'=>1,'title'=>'楼层2：分类模块'],
        ['id'=>3,'pid'=>0,'level'=>1,'title'=>'楼层3：会员专区'],
        ['id'=>4,'pid'=>0,'level'=>1,'title'=>'楼层4：家具电器'],
        ['id'=>5,'pid'=>0,'level'=>1,'title'=>'楼层5：美妆护肤'],
        ['id'=>6,'pid'=>0,'level'=>1,'title'=>'楼层6：数码科技'],
        ['id'=>7,'pid'=>0,'level'=>1,'title'=>'楼层7：服饰箱包'],
        ['id'=>8,'pid'=>1,'level'=>2,'title'=>'楼层1：轮播图1'],
        ['id'=>9,'pid'=>1,'level'=>2,'title'=>'楼层1：轮播图2'],
        ['id'=>10,'pid'=>1,'level'=>2,'title'=>'楼层1：轮播图3'],
        ['id'=>11,'pid'=>2,'level'=>2,'title'=>'楼层2：分类1'],
        ['id'=>12,'pid'=>2,'level'=>2,'title'=>'楼层2：分类2'],
        ['id'=>13,'pid'=>2,'level'=>2,'title'=>'楼层2：分类3'],
        ['id'=>14,'pid'=>2,'level'=>2,'title'=>'楼层2：分类4'],
        ['id'=>15,'pid'=>3,'level'=>2,'title'=>'楼层3：会员专区'],
        ['id'=>16,'pid'=>4,'level'=>2,'title'=>'楼层4：家具电器大图'],
        ['id'=>17,'pid'=>4,'level'=>2,'title'=>'楼层4：家具电器小图1'],
        ['id'=>18,'pid'=>4,'level'=>2,'title'=>'楼层4：家具电器小图2'],
        ['id'=>19,'pid'=>4,'level'=>2,'title'=>'楼层4：家具电器小图3'],
        ['id'=>20,'pid'=>5,'level'=>2,'title'=>'楼层5：美妆护肤大图'],
        ['id'=>21,'pid'=>5,'level'=>2,'title'=>'楼层5：美妆护肤小图1'],
        ['id'=>22,'pid'=>5,'level'=>2,'title'=>'楼层5：美妆护肤小图2'],
        ['id'=>23,'pid'=>5,'level'=>2,'title'=>'楼层5：美妆护肤小图3'],
        ['id'=>24,'pid'=>6,'level'=>2,'title'=>'楼层6：数码科技大图'],
        ['id'=>25,'pid'=>6,'level'=>2,'title'=>'楼层6：数码科技小图1'],
        ['id'=>26,'pid'=>6,'level'=>2,'title'=>'楼层6：数码科技小图2'],
        ['id'=>27,'pid'=>6,'level'=>2,'title'=>'楼层6：数码科技小图3'],
        ['id'=>28,'pid'=>7,'level'=>2,'title'=>'楼层7：服饰箱包大图'],
        ['id'=>29,'pid'=>7,'level'=>2,'title'=>'楼层7：服饰箱包小图1'],
        ['id'=>30,'pid'=>7,'level'=>2,'title'=>'楼层7：服饰箱包小图2'],
        ['id'=>31,'pid'=>7,'level'=>2,'title'=>'楼层7：服饰箱包小图3'],
    ];
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::MallHomeData as $list) {
            MallHome::query()
                ->create([
                    'id'        => $list['id'],
                    'pid' => $list['pid'],
                    'title'     => $list['title'],
                    'level'  => $list['level']
                ]);
        }
    }
}
