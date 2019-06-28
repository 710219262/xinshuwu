<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 21/03/2019
 * Time: 22:28
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class UserCollectionCategory
 *
 * @property integer $id
 * @property integer $user_id
 * @property string  $name
 * @property string  $desc
 *
 * @package App\Models
 */
class UserCollectionCategory extends Model
{
    protected $table = 'xsw_user_collection_category';
    
    protected $guarded = ['id'];
    
    public function collectionRlt()
    {
    
    }
}
