<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
use app\index\model\UserInfo;//用户模型
use app\index\model\Ordered;//预约表模型
use app\index\model\Visit;//就诊历史模型
use aliyunSms\SendMsg;//阿里云

class Index extends Base
{
  //首页
  public function index()
  {
    return view();
  }
  //----------------------------

  //取消预约
  public function cancelOrdered()
  {
    $cancel = \think\Loader::model('OrderLogic','logic');
    return $cancel->cancelOrder();
  }

  //就诊历史页面访问
  public function showVisit()
  {
    //获取数据
    $openid = Session::get('user.openid');
    $data = Visit::all(['openid'=>$openid]);
    if(!empty($data)){
      return view('showVisit',['data'=>$data]);
    }else{
      return view('noVisit');
    }

  }

  //预约界面展示
  public function showOrdered()
  {
    //获取数据
    $openid = Session::get('user.openid');
    $data = Ordered::all(['openid'=>$openid]);
    if(!empty($data)){
      return view('showOrdered',['data'=>$data]);
    }else{
      return view('noOrdered');
    }

  }

  //------------修改手机页面返回-----------
  public function updateTel()
  {
    return view('updateTel');
  }

  //修改手机表单处理
  public function telForm()
  {
    $logic = \think\Loader::model('UpdateLogic','logic');
    return $logic->telForm();
  }

  //验证码获取ajax
  public function getValid()
  {
    //----------阿里云---------
    $sms = new SendMsg();
    return $sms->sendValid();
    //----------云之讯--------
    // $sms = new smsyzm();
    // //执行验证码的发送，并且返回前端json数据
    // return $sms->sendValid();//返回给前端json数据
  }

  //-------我的信息板块----------
  public function getDataView()
  {
    //自动登录
    $url = 'http://www.kangquanpay.top/getDataView';
    $this->login($url);

    //判断用户是否注册了，否则先请用户注册
    $openid = Session::get('user.openid');
    $user = UserInfo::get(['openid'=>$openid]);
    if($user==null){//未注册
      return view('noBind');
    }else{//注册了
      //获取数据
      $data['headimgUrl'] = Session::get('user.headimgurl');//获取用户微信头像
      //获取姓名
      $data['name'] = $user->username;
      //返回前端
      return view('userData',['data'=>$data]);
    }

  }

  //注销信息控制函数
  public function cancelData()
  {
    $logic = \think\Loader::model('UpdateLogic','logic');
    return $logic->cancel();
  }

  //修改信息控制页面函数
  public function updateData()
  {
    //获取数据
    $openid = Session::get('user.openid');
    $user = UserInfo::get(['openid'=>$openid]);
    $data['name'] = $user->username;
    $data['sex'] = $user->sex;
    $data['age'] = $user->age;
    $data['tel'] = $user->tel;

    return view('updateUserInfo',['data'=>$data]);
  }

  //开始修改信息函数
  public function startUpdate()
  {
    $logic = \think\Loader::model('UpdateLogic','logic');
    return $logic->updateInfo();
  }

}


?>
