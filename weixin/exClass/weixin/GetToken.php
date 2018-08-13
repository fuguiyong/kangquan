<?php
/*
get_token http——curl类
*/
namespace app\weixin\exClass\weixin;

use app\weixin\model\Token;//导入数据库模型

class GetToken
{
	 private $appId;
   private $appSecret;

//构造函数
  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
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



	//getAccessToken函数

	    public function getAccessToken()
	    {
				//先判断数据库是否有数据
				//有-》判断时间
				//没有，直接调用接口
				$token = Token::get(['name'=>'access_token']);
				if($token==null)
				{
					return $this->saveToken();//获取token函数
				}else {
							$expTime = $token->expire_time;
							if($expTime>time())//token没过期
							{
								return $token->value;
							}else {//过期
								//获取access_token并且更新
											$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=". $this->appSecret;
											$res = $this->http_url($url,'get');
											$access_token = $res['access_token'];//接受返回的数据
											$time = time()+7000;
											$token->value = $access_token;
											$token->expire_time = $time;
											$token->isUpdate()->save();
											return $access_token;
							}
				}
	    }

			//save access_token
			public function saveToken()
			{
				//获取access_token
							$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$this->appId."&secret=". $this->appSecret;
							$res = $this->http_url($url,'get');
							$access_token = $res['access_token'];//接受返回的数据
							$time = time()+7000;
							//存入数据库
							$accessToken = new Token();
							$accessToken->name = 'access_token';
							$accessToken->value = $access_token;
							$accessToken->expire_time = $time;
							$accessToken->save();
							//返回token
							return $access_token;
			}

}


?>
