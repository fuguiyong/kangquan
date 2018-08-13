<?php
namespace app\api\model;

use think\Model;

class PrePay extends model
{
  //关联表
  protected $table = 'pay';
  // 开启自动写入时间戳
  //protected $autoWriteTimestamp = true;
  //可更新字段
  //protected $field = ['name', 'nickname', 'sex', 'headimgurl',];
}

 ?>
