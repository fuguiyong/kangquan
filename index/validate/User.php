<?php
namespace app\index\validate;

use think\Validate;

class User extends Validate
  {

    protected $rule =   [
      'name'  => 'require|chs|length:2,15',
      'age' => 'require|between:1,120',
      'tel'   => 'require|number|max:11|min:11',
      'card' => 'require',
    ];

    protected $message  =   [
      'name.require' => '名称必须',
      'name.chs'=>'名字只可以是中文',
      'name.length'=>'名字长度2-15字符',
      'tel' =>'电话格式错误',
      'card'=> '就诊卡号必须',
    ];

    //验证场景
    protected $scene = [
      'tel'  =>  ['tel'],
      'name' => ['name'],
      'age'  =>  ['age'],
    ];


}
?>
