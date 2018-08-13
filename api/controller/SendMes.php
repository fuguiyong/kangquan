<?php
// 发送付费模板请求api
// 接口说明
// json交互
// 需要提供openid 金额sum
namespace app\api\controller;

use think\Controller;
use app\api\model\User;//用户信息模型

class SendMes extends Controller
{
  //发送模板消息
  public function sendMsg()
  {
    //获取信息
    $jsonData = file_get_contents('php://input');
    //转换为array
    $Arrdata = json_decode($jsonData,true);
    //判断传入的数据
    if(array_key_exists('openid',$Arrdata)&&array_key_exists('sum',$Arrdata)){
      //判断openid注册信息没有
      $has_user = User::get(['openid'=>$Arrdata['openid']]);
      if($has_user==null){//没有注册信息
        $backInfo = [
          'errcode'=>'2',
          'errmsg'=>'该用户没有绑定微信'
        ];
      }else{//注册了
        //组装信息
        $Arrdata['costType'] = '药费';//费用类型
        //发送信息
        $sendmesFun = \think\Loader::model('TemplateMes','service');
        $res = $sendmesFun->payMes($Arrdata);
        //判断模板信息发送是否成功
        if($res['errcode']==0){//发送成功
          $backInfo = [
            'errcode'=>'0',
            'errmsg'=>'ok'
          ];
        }else{//发送失败
          $backInfo = [
            'errcode'=>'3',
            'errmsg'=>'模板消息发送失败'
          ];
        }
      }
    }else{
      $backInfo = [
        'errcode'=>'1',
        'errmsg'=>'数据字段错误'
      ];
    }
    //返回json结果
    return json($backInfo);

  }


}

?>
