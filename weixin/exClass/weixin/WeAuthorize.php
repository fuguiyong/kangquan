<?php
/*
网页授权类
 */

namespace app\weixin\exClass\weixin;

class WeAuthorize
{
  private $app_id ;
  private $app_secret;


//构造函数
  public function __construct($appId, $appSecret) {
    $this->app_id = $appId;
    $this->app_secret = $appSecret;
  }

  /**
   * 获取微信授权链接
   *
   * @param string $redirect_uri 跳转地址
   * @param mixed $state 参数
   */

  public function get_authorize_url($redirect_url = '', $state = '520')
  {
    $redirect_url = urlencode($redirect_url);
    return "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->app_id}&redirect_uri={$redirect_url}&response_type=code&scope=snsapi_userinfo&state={$state}#wechat_redirect";
  }

  /**
   * 获取授权token
   *
   * @param string $code 通过get_authorize_url获取到的code
   */
  public function get_access_token()
  {
    $code = input('get.code');
    $token_url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
    $token_data = $this->http_url($token_url);
	  return $token_data;
  }

  /**
   * 获取授权后的微信用户信息
   *
   * @param string $access_token
   * @param string $open_id
   */
  public function get_user_info()
  {
    //获取网页授权的access_token和openid
      $token_data = $this->get_access_token();
      $access_token = @$token_data['access_token'];
      $open_id = @$token_data['openid'];
      $info_url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
      $info_data = $this->http_url($info_url);
	    return $info_data;

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
