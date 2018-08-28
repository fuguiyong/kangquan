<?php
namespace app\shop\controller;

use think\Controller;
use app\shop\model\Drug1;

class index extends controller
{
  public function index()
  {
    echo "hello world";
    //获取数据库信息
    $data1= Drug1::all();
    //向页面返回展示
    return view('',['data1'=>$data1]);
  }
}

 ?>
