<?php
namespace app\index\model;

use think\Model;

class UserInfo extends Model
{
  //关联表
  protected $table = 'userinfo';
  // 开启自动写入时间戳
  protected $autoWriteTimestamp = 'datetime';
  //可更新字段
  //protected $field = ['name', 'nickname', 'sex', 'headimgurl',];

  //关联visit
  public function visit()
  {
    return $this->hasMany('Visit','openid','openid');
  }

  //关联ordered
  public function ordered()
  {
    return $this->hasMany('Ordered','openid','openid');
  }

}

?>
