<?php
namespace app\index\validate;

use think\Validate;

class Token extends Validate
{
  protected $rule =   [
    '__token__'=>'token'
  ];
}
?>
