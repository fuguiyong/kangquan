<?php
/*
预约挂号模板消息发送
*/
namespace app\index\conmon\sendMes;

use curl\Curl;
use app\weixin\exClass\weixin\GetToken;

//康泉的id和secret
DEFINE("APPID","wx46df12a8b7baee14");
DEFINE("APPSECRET","388b830c64e55ae8dfa7c180c2c6585d");

class OrderMes{

public static function sendMes($data)
{
  //先判断数据是否正确

  //正确，获取access_token
  $token = new GetToken(APPID,APPSECRET);
  $access_token = $token->getAccessToken();
  $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=.$access_token";
  //请求接口
  $data = json($data);
  $res = Curl::curl($url,$data,'post');
  return $res;
}
}
 ?>
