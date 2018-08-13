<?php
/*
token 票据模型
*/
namespace app\weixin\model;

use think\Model;

class Token extends Model
{
  //关联表
  protected $table = 'token';
  // 开启自动写入时间戳
  protected $autoWriteTimestamp = true;
}
 ?>
