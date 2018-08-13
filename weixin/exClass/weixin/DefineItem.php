<?php
/*
自定义菜单类
*/
namespace app\weixin\exClass\weixin;

class DefineItem
{
	private $itemArr;
	private $accessToken;

//构造函数
  public function __construct($token,$arr)
  {
	  $this->accessToken = $token;
    $this->itemArr = $arr;
  }

  //之定义菜单方法
	 public function definedItem()
	    {
			//完成自定义菜单接口
	        $url ="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$this->accessToken;
			//调用接口
	        $postJson = urldecode(json_encode($this->itemArr ));
	        $res = $this->http_url($url,'post','json',$postJson);
			//返回结果
			return $res;

	    }

		//cUR执行L函数
	    public function http_url($url,$type='get',$res='json',$arr='')

	    {
	        //1.初始化curl

	        $ch =curl_init();

	        //2.设置curl参数
			//避免curl60 error
			if(stripos($url,"https://")!==FALSE){

               // curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
              } else {
                  curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
                  curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
                   }

			//设置需求curl参数
	        curl_setopt($ch,CURLOPT_URL,$url);//设置接口URl

	        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);

	        if($type == 'post'){

	            curl_setopt($ch,CURLOPT_POST,1);

	            curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);//以post传输数据

	        }

	        //3.采集

	        $output = curl_exec($ch);

	        if($res =='json'){

	            if(curl_errno($ch)){

	                //请求失败，返回错误信息

	                return curl_errno($ch);

	            }else{

	                //请求成功,json解码返回

	                return json_decode($output,true);

	            }

	        }
			        //4.关闭

	        curl_close($ch);

	    }//http_url函数end

}
?>
