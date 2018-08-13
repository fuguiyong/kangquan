<?php
namespace app\api\model;

use think\Model;

class User extends Model
{
  //关联表
  protected $table = 'userinfo';
  // 开启自动写入时间戳
  protected $autoWriteTimestamp = true;
  //可更新字段
  //protected $field = ['name', 'nickname', 'sex', 'headimgurl',];
}

 ?>
