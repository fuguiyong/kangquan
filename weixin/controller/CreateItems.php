<?php
namespace app\weixin\controller;

use app\weixin\exClass\weixin\CreateItems as item;
use think\Controller;

class CreateItems extends Controller
{
  public function index()
  {
    //取得密码
    $pwd = input('pwd');
    if($pwd=='760720981')
    {
      $items = new item();
      $items->definedItems();
    }else{
      echo "密码错误，不可以修改菜单";
    }

  }
}

 ?>
