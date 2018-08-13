<?php
namespace app\index\model;

use think\Model;

class Docter extends Model
{
  protected $table = 'docter';

//定义一对多关联
 public function scheduling()
 {
   return $this->hasMany('Scheduling','docter_id','docter_id');
 }

}

 ?>
