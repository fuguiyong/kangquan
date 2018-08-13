<?php
namespace app\index\model;

use think\Model;

class Visit extends Model
{
  //关联表
  protected $table = 'visit';
  // 指定自动写入时间戳的类型为dateTime类型
  protected $autoWriteTimestamp = 'datetime';
  //可更新字段
  //protected $field = ['name', 'nickname', 'sex', 'headimgurl',];
}

 ?>
