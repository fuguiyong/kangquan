<?php
//微信初始类
namespace app\weixin\model;

use app\weixin\exClass\weixin\MesEvent;
use app\weixin\model\LocalList as city;//数据库模型

//定义常量
define("TOKEN", "kangquan");
//定义事件类实例
$mesEvent = new MesEvent();	//创建一个回复类实例

class wechatCallbackapi
{

	//验证函数
	public function valid()
    {
        $echoStr = $_GET["echostr"];
			//	$echoStr = $request->get('echoStr');
		  	//$echoStr = input('echoStr');
        //valid signature , option
        if($this->checkSignature())
		    {
        	echo $echoStr;
        }
    }

	//消息回复函数
    public function responseMsg()
    {
		//获取用户提交的数据
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
    //$postStr = isset($GLOBALS["HTTP_RAW_POST_DATA"]) && !empty($GLOBALS["HTTP_RAW_POST_DATA"]) ? $GLOBALS["HTTP_RAW_POST_DATA"] : "";
      	//判断是否有数据
		if (!empty($postStr))
		{
      //将xml对象化获取数据
      $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			//先取得用户提交的type
			$type = strtolower($postObj->MsgType);

			//对各种信息处理
			switch($type)
			{
				//一共有image vioce video location text link event等
				//‘event’
			    case 'event':$this->handleEvent($postObj);break;
				//text
			    case 'text':$this->replyText($postObj);break;
				//location
			   	case 'location':$GLOBALS['mesEvent']->handleLocation($postObj);break;
				//default
			    default:$GLOBALS['mesEvent']->defaultReply($postObj);break;

			}
        }else
		{
        	echo '';
		}
    }//消息处理主函数end


	//event处理函数
	public function handleEvent($postObj)
	{
		//先取得strtolower($postObj->Event)
		$event = strtolower($postObj->Event);

		//对各种event事件做出相应处理
		switch($event){
		//关注事件
		case "subscribe":$GLOBALS['mesEvent']->handleSub($postObj);break;
        //click事件
		case 'click':$this->handleClick($postObj);break;
		//默认事件回复
		default:$GLOBALS['mesEvent']->defaultReply($postObj);break;
		}
	}


	//click处理函数
	public function handleClick($postObj)
	{
		//取得click_key
		$key = strtolower($postObj->EventKey);//转换小写
		//根据key向用户返回相应信息
		switch($key)
		{
			//天气查询处理事件
			case 'weather':$GLOBALS['mesEvent']->handleWeather($postObj);break;
			case 'key1':$GLOBALS['mesEvent']->structure($postObj);break;
		//	case 'key2':$GLOBALS['mesEvent']->structure($postObj);break;
			case 'key3':$GLOBALS['mesEvent']->structure($postObj);break;
			case 'key4':$GLOBALS['mesEvent']->structure($postObj);break;
			case 'key5':$GLOBALS['mesEvent']->structure($postObj);break;
			case 'key6':$GLOBALS['mesEvent']->structure($postObj);break;
     		default:$GLOBALS['mesEvent']->defaultReply($postObj);break;

		}
	}


	//文本回复函数
	public function replyText($postObj)
	{
		//取得用户提交的内容
		$content = $postObj->Content;
		//判断用户输入的内容，在返回对应的内容
		if((strlen($content)<20)&&(strncasecmp($content,"百度",2)==0  || stripos($content,"百度")!=false ))
		{
			//百度函数
			$GLOBALS['mesEvent']->baidu($postObj);

		}else if($content=='图文'){
			//调用图文回复函数
			$GLOBALS['mesEvent']->replyImagetext($postObj);
		}else if($content=='天气查询' || $content=='天气预报' || $content=='天气'){

			$GLOBALS['mesEvent']->handleWeather($postObj);
		}
		else
		{
      //判断用户输入的内容
      $city = city::get(function($query){
        $query->where('Ccity|Ecity','=',$content);
      });

      if($city==null)
      {
        //调用默认回复函数
  		   $GLOBALS['mesEvent']->defaultReply($postObj);
      }else {
        //取得城市名字
        $cityName = $city->Ecity;
        //调用天气查询函数
        $GLOBALS['mesEvent']->cityWeather($postObj,$cityName);
      }

		}
	}

	//回复函数end

	//验证子函数
	private function checkSignature()
	{
       $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
      $nonce = $_GET["nonce"];
		//$signature = $request->get('signature');
	//	$timestamp = $request->get('timestamp');
	//	$nonce = $request->get('nonce');
////	$signature = input('get.signature');
//	$timestamp = input('get.timestamp');
//	$nonce = input('get.nonce');


		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );

		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

}

 ?>
