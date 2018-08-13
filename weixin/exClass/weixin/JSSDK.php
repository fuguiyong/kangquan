<?php
/*
微信API配置类
*/
namespace app\weixin\exClass\weixin;

use app\weixin\model\Token;//导入数据库模型
use app\weixin\exClass\weixin\GetToken;

class JSSDK
{

  private $appId;
  private $appSecret;

//构造函数
  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
  }


//签名函数
public function getSignPackage(){

//通过接口获得jsapi_ticket
   $jsapiTicket = $this->getJsApiTicket();

//获取随机字符串
    $nonceStr = $this->createNonceStr();
//获取时间戳
    $timestamp = time();

	//url

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string,
	    "jsapi_ticket"=>$jsapiTicket
    );
    return $signPackage;


//noncestr（随机字符串）, 有效的jsapi_ticket, timestamp（时间戳）, url（当前网页的URL，不包含#及其后面部分）对所有待签名参数按照字段名的ASCII 码从小到大排序（字典序）后，使用URL键值对的格式（即key1=value1&key2=value2…）拼接成字符串string1。这里需要注意的是所有参数名均为小写字符。对string1作sha1加密，字段名和字段值都采用原始值，不进行URL 转义

}

//获取票据函数
private function getJsApiTicket(){
  //先判断数据库是否有数据
  //有-》判断时间
  //没有，直接调用接口
  $ticket = Token::get(['name'=>'jsapi_ticket']);
  if($ticket==null)
  {
    //获取票据
    $jsTicket1 = $this->saveTicket();
    //存入数据库
    $jsTicket = new Token();
    $jsTicket->name = 'jsapi_ticket';
    $jsTicket->value = $jsTicket1;
    $jsTicket->expire_time = time()+7000;
    $jsTicket->save();
    //返回票据
    return $jsTicket1;
  }else {
        $expTime = $ticket->expire_time;
        if($expTime>time())//token没过期
        {
          return $ticket->value;
        }else {
          //获取jsticket并且更新
          //获取票据
          $jsTicket2 = $this->saveTicket();
          //更新数据
          $ticket->value = $jsTicket2;
          $ticket->expire_time = time()+7000;
          $ticket->isUpdate()->save();
          //返回票据
          return $jsTicket2;
        }

  }
}//getjsapiticket end

//save
private function saveTicket()
{
  	//取得accessToken
		$getToken = new GetToken($this->appId,$this->appSecret);
		$accessToken = $getToken->getAccessToken();
    //调用接口获取票据
    $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$accessToken}&type=jsapi";
    $res1 = $getToken->http_url($url,'get');
    $jsapiticket = $res1['ticket'];
    //返回ticket
    return $jsapiticket;
}

  //随机字符串的函数
  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

}

?>
