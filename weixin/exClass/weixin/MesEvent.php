<?php
/*
消息事件处理类
*/
namespace app\weixin\exClass\weixin;

//依赖注入格式化消息文件
use app\weixin\exClass\weixin\Format;
use app\weixin\exClass\weixin\WeaApi;

class MesEvent
{

	//test1
	public function hello()
	{
		echo 'HELLO THINKPHP';
	}

	//回复正在构建的函数
	public function structure($postObj){
		//创建实例
		$format = new Format();
		//设置回复内容
		$con = "该功能正在构建，"."敬请期待!";
		//格式化内容
		$res = $format->formatText($postObj,$con);
		//向用户返回内容
		echo $res;
	}

	//defau函数
	public function defaultReply($postObj){
		/*
		//创建实例
		$format = new Format();
			$arr = [
				[
					'Title'=>'康泉综合门诊部',
					'Description'=>"目前平台功能如下："."\n"."【1】门诊部概况介绍"."\n"."【2】就诊服务"."\n"."【3】生活小助手"."\n"."更多内容，敬请期待...",
					'PicUrl'=>'http://ww2.sinaimg.cn/large/87c01ec7gy1fs1shqbwgqj21kw119x6w.jpg',
					'Url'=>'',
				]
			];
		//格式化图文
		$resultStr = $format->resNews($postObj,$arr);
		//向用户输出内容
		echo $resultStr;
		*/
		//创建实例
		$format = new Format();
		//设置回复内容
		$con = '康泉综合门诊欢迎你';
		//格式化内容
		$res = $format->formatText($postObj,$con);
		//向用户返回内容
		echo $res;
	}

	//关注函数
	public function handleSub($postObj){
		//创建实例
		$format = new Format();
		//设置图文内容
		$arr = [
			[
				'Title'=>'康泉综合门诊部',
				'Description'=>"感谢你的关注！"."\n"."目前平台功能如下："."\n"."【1】门诊部概况介绍"."\n"."【2】微商城"."\n"."【3】个人中心"."\n"."更多内容，敬请期待...",
				'PicUrl'=>'http://ww2.sinaimg.cn/large/87c01ec7gy1fs1shqbwgqj21kw119x6w.jpg',
				'Url'=>'',
			]
		];
		//格式化图文
		$resultStr = $format->resNews($postObj,$arr);
		//向用户输出内容
		echo $resultStr;

	}

	//click_key = weather函数
	public function handleWeather($postObj)
	{
		//创建实例
		$format = new Format();
		$contentStr = "通过下面两种方式获取天气"."\n"."【1】发送地理位置获取当地天气"."\n"."【2】发送城市名字关键字，例：北京/beijing/北京市";
		////调用文本回复函数即可
		$resultStr = $format->formatText($postObj,$contentStr);
        echo $resultStr;
	}

	//用户输入百度函数
	public function baidu($postObj){
		//创建实例
		$format = new Format();
			//设置内容
			$replyCon = "<a href='https://baidu.com'>跳转百度</a>";
			$resultStr = $format->formatText($postObj,$replyCon);
            echo $resultStr;
	}

	//location处理函数
	public function handleLocation($postObj)
	{
		//取得维度经度
		$x = $postObj->Location_X;
		$y = $postObj->Location_Y;
		//调用接口并且取得数据
		$weaApi = new WeaApi();
		$res = $weaApi->weatherApi($x,$y);
		//处理返回的数组直接返回给用户
	    $this->formatWeather($postObj,$res);
	}

	//城市关键字查询天气函数
	public function cityWeather($postObj,$cityName){

		//调用接口并且取得数据
		$weaApi = new WeaApi();
		$res = $weaApi->weatherApi($cityName);
		//处理返回的数组直接返回给用户
	  $this->formatWeather($postObj,$res);

	}

	//formatWeather函数
	public function formatWeather($postObj,$res)
	{
		//创建实例
		$format = new Format();
		//取得城市
		$cityName = $res['results'][0]['location']['name'];
		//取得数组里面的数组（即3天的具体天气预报）
		$weaArr = $res['results'][0]['daily'];
		//取得最近三天的天气预报具体信息
		//day1
		$day1_date = $weaArr[0]['date'];
		$day1_day = $weaArr[0]['text_day'];
		$day1_night = $weaArr[0]['text_night'];
		$day1_high = $weaArr[0]['high'];
		$day1_low = $weaArr[0]['low'];
		$day1_windSpeed = $weaArr[0]['wind_speed'];
		$day1_windScale = $weaArr[0]['wind_scale'];
		//day2
		$day2_date = $weaArr[1]['date'];
		$day2_day = $weaArr[1]['text_day'];
		$day2_night = $weaArr[1]['text_night'];
		$day2_high = $weaArr[1]['high'];
		$day2_low = $weaArr[1]['low'];
		$day2_windSpeed = $weaArr[1]['wind_speed'];
		$day2_windScale = $weaArr[1]['wind_scale'];
		//day3
		$day3_date = $weaArr[2]['date'];
		$day3_day = $weaArr[2]['text_day'];
		$day3_night = $weaArr[2]['text_night'];
		$day3_high = $weaArr[2]['high'];
		$day3_low = $weaArr[2]['low'];
		$day3_windSpeed = $weaArr[2]['wind_speed'];
		$day3_windScale = $weaArr[2]['wind_scale'];

		//设置内容
		$con_day1 = $cityName."天气"."\n"."今日："."\n"."日期：".$day1_date."\n"."白天天气：".$day1_day."\n"."晚上天气：".$day1_night."\n"."温度：".$day1_low."~".$day1_high."℃";
		$con_day2 = "\n\n"."明天："."\n"."日期：".$day2_date."\n"."白天天气：".$day2_day."\n"."晚上天气：".$day2_night."\n"."温度：".$day2_low."~".$day2_high."℃";
	//	$con_day3 = "\n\n"."后天："."\n"."日期：".$day3_date."\n"."白天天气：".$day3_day."\n"."晚上天气：".$day3_night."\n"."温度：".$day3_low."~".$day1_high."℃"."\n"."风速：".$day3_windSpeed."\n"."风力等级：".$day3_windScale;
		$content = $con_day1.$con_day2;
		//调用设置文本格式函数
		//$res1 = $this->responseText($postObj,$con_day1);
		//$res2 = $this->responseText($postObj,$con_day2);
		//$res3 = $this->responseText($postObj,$con_day3);
		$res =  $format->formatText($postObj,$content);
		echo $res;
	}



}//class end


?>
