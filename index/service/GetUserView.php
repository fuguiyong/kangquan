<?php
namespace app\index\service;

use think\Model;
use think\Session;
use app\weixin\exClass\weixin\WeAuthorize;

class GetUserView extends Model
{
  public function getInfoLogin()
  {
    $this->LoginAuth();
    //user是否授权，存在就不用授权了,,判断Session是否含有openid
    // if(Session::has('user'))
    // {
    //   //获取user看是否openid存在
    //   $user = Session::get('user');
    //   $openid = $user['openid'];
    //   if($openid==null)
    //   {
    //     $userData = json_encode($user);
    //     file_put_contents('has.txt','has user openid null'.$userData);
    //     //授权
    //     $this->LoginAuth();
    //   }else{
    //     file_put_contents('has.txt','has openid');
    //   }
    //
    //   //如果有用户授权信息，直接返回视图
    // }else{
    //   file_put_contents('has.txt','no user');
    //   //授权登录
    //   $this->LoginAuth();
    // }
  }

  //登录函数（授权函数）
  public function LoginAuth()
  {
    //Session::delete('user');
    //获取用户信息
    $auth = new  WeAuthorize(APPID,APPSECRET);
    $infoArr = $auth->get_user_info();
    file_put_contents('userInfo.txt',json($infoArr));
    $openid = @$infoArr['openid'];
    $nickname = @$infoArr['nickname'];
    $sex = @$infoArr['sex'];
    $headimgurl = @$infoArr['headimgurl'];
    $user = [
      'openid'=>$openid,
      'nickname'=>$nickname,
      'sex'=>$sex,
      'headimgurl'=>$headimgurl
    ];
    Session::set('user',$user);

  }
}
