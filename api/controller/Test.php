<?php
namespace app\api\controller;

use app\api\model\User;
use think\Controller;
use think\Request;

//定义签名密码
define('PWD','kangquanuserinfo');

class Test extends Controller
{

  public function sendUserInfo()
  {
  //获取数据
  $jsonData = file_get_contents('php://input');
  //解码json为array
  $data = json_decode($jsonData,true);
  //获取签名
  $pwd = $data['pwd'];

  if($pwd==PWD){
    //取得信息
    $allUserInfo = User::all();
    //组装信息
    $backInfo = [
      'status'=>'0',
      'userInfo'=>$allUserInfo
    ];
  }else{
    //组装信息
    $backInfo = [
      'status'=>'1',
      'errMes'=>'签名错误'
    ];

  }
    return json($backInfo);
  }


}

 ?>
