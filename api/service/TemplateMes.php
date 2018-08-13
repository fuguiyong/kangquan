<?php
namespace app\api\service;

use think\Model;
use curl\Curl;
use app\weixin\exClass\weixin\GetToken;


class TemplateMes extends Model
{
  //注册
  public function registerMes($info=[])
  {
    //组装信息
    $data = [
      "touser"=>$info['openid'],
      "template_id"=>"uSxTz3DUBN_qD6Uph18ALx3hQ_tW_ZGkYp60TNggfRM",
      'data'=>[
        'first'=>['value'=>'你好，你已经成功注册',"color"=>"#173177"],
        'keyword1'=>['value'=>$info['name']],
        'keyword2'=>['value'=>$info['time']],
        'remark'=>['value'=>'感谢你的使用'],
      ]
    ];
    //发送模板消息
    $res = $this->sendMes($data);
    //返回信息
    return $res;
  }

  //绑定
  public function bindMes($info=[])
  {
    //组装信息
    $data = [
      "touser"=>$info['openid'],
      "template_id"=>"fQt7PMcFE6QLOV08cc4Rp8fPEf0LXQ49t7R4_fltWgU",
      'data'=>[
        'first'=>['value'=>'你好，你已经成功绑定',"color"=>"#173177"],
        'keyword1'=>['value'=>$info['name']],
        'keyword2'=>['value'=>$info['time']],
        'remark'=>['value'=>'感谢你的使用'],
      ]
    ];
    //发送模板信息
    $res = $this->sendMes($data);
    //返回信息
    return $res;
  }
  //预约挂号

  //付费
  public function payMes($info=[])
  {
    //获取订单号transaction_id
    $transaction_id = $info['transaction_id'];
    //url填写参数（订单号）
    $url = "http://www.kangquanpay.top/pay/{$transaction_id}";
    //组装信息
    $data = [
      "touser"=>$info['openid'],
      "template_id"=>"7hLWCbims0IP8iSRvKC7HdVOxhELubzyWoFHnHMembw",
      'url'=>$url,
      'data'=>[
        'first'=>['value'=>'您有一笔就诊费等待付款,请您点击支付',"color"=>"#173177"],
        'keyword1'=>['value'=>$info['sum'].'元'],
        'keyword2'=>['value'=>$info['costType']],
        'remark'=>['value'=>'请完成支付后到对应科室排队等候,谢谢'],
      ]
    ];
    //发送模板信息
    $res = $this->sendMes($data);
    //返回信息
    return $res;
  }

  //缴费成功通知
  public function sucPayMes($info=[])
  {
    //组装信息
    $data = [
      "touser"=>$info['openid'],
      "template_id"=>"Gz7AwKg2K1kkGn2pxMTArSaikDyVqfoo12bdFn0-iG0",
      'data'=>[
        'first'=>['value'=>'缴费成功',"color"=>"#173177"],
        'keyword1'=>['value'=>$info['costType']],
        'keyword2'=>['value'=>$info['total_fee']],
        'keyword3'=>['value'=>$info['name']],
        'remark'=>['value'=>'请到医药室排队等候,谢谢'],
      ]
    ];
    //发送模板信息
    $res = $this->sendMes($data);
    //返回信息
    return $res;
  }

  //模板消息服务接口函数
  private function sendMes($data)
  {
    //先判断数据是否正确
    //正确，获取access_token
    $token = new GetToken(APPID,APPSECRET);
    $access_token = $token->getAccessToken();
    $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
    //请求接口
    $body = json_encode($data, JSON_UNESCAPED_UNICODE);
    $res = Curl::curl($url,$body,'post');
    return $res;

  }
}

?>
