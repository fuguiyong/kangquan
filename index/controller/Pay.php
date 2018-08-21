<?php
namespace app\index\controller;

use think\Controller;
use think\Session;
use app\index\model\UserInfo;//用户模型
use service\XmlArray;
use app\api\model\PrePay;//费用表模型

class Pay extends Controller
{
    //支付说明
    public function payExplain()
    {
        return view('payExplain');
    }

    //支付向导
    public function toPay()
    {
        //用户自动登录
        $url = 'http://www.kangquanpay.top/pay';
        $userLogin = \think\Loader::model('User', 'logic');
        $userLogin->login($url);//因为动态地址获取不行，所以为了通用就传入redirect_url
        //获取openid
        $openid = Session::get('user.openid');
        //先判断用户是否绑定微信
        $is_bind = UserInfo::get(['openid' => $openid]);
        if ($is_bind) {//绑定了微信
            //开始付款逻辑
            $prePay = \think\Loader::model('User', 'logic');
            $jsParam = $prePay->pay($openid);
            if ($jsParam == null) {//有需要支付的订单
                return view('noPay');
            } else {//没有需要支付的订单
                return view('pay', ['jsParam' => $jsParam]);
            }
        } else {//没有绑定微信
            return view('toBind');
        }

    }

    //支付成功的回调函数
    public function payBack()
    {
        //禁用xml外部实体注入 防止xxe漏洞
        libxml_disable_entity_loader(true);
        //获取数据
        $xmlData = file_get_contents('php://input');
        $arrData = XmlArray::XmlToArr($xmlData);
        //开始校验数据并且修改订单状态
        $user = \think\Loader::model('User', 'logic');
        $res = $user->successPay($arrData);
        if ($res == 'success') {
            //返回成功
            echo 'SUCCESS';
            die;
        } else {//写错误日志
            $date = date("Y-m-d h:i:s");//获取时间
            file_put_contents('./log//err/payerr.txt', $date . '-订单号-' . $arrData['transaction_id'] . '-总验证失败' . PHP_EOL, FILE_APPEND);
        }

    }

    //扫码支付回调地址
    public function successNative()
    {
        echo 'SUCCESS';
        die;

    }


}

?>
