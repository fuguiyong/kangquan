<?php
namespace app\index\logic;


use think\Session;
use think\Db;
use app\index\model\UserInfo;//用户信息模型
use app\api\model\PrePay;//需要支付费用表模型
use app\index\model\Payed;//已经支付费用表模型

use myWeChatPay\weChatPay;//支付类


class User extends Base
{

    //注册表单
    public function register()
    {
        //取得用户提交数据
        $name = input('post.name');
        $tel = input('post.tel');
        $age = input('post.age');
        $token = input('__token__');

        //获取用户授权信息
        $user = Session::get('user');
        $nickname = $user['nickname'];
        $openid = $user['openid'];
        $sex1 = $user['sex'];
        $sex = $sex1 == '0' ? '女' : '男';
        $headimgurl = $user['headimgurl'];

        //验证token
        $valid = validate('Token');
        $res = $valid->check(['__token__' => $token]);
        if (!res) {
            $this->return_msg('3', $valid->getError());
        }

        //判断是否注册过
        $user1 = UserInfo::get(['openid' => $openid]);
        if (!empty($user1)) {
            $this->return_msg('1', '你已经注册过了(一个微信号只能注册一个账号)');
        }

        //=========掉门诊部接口==========
        //=======接口错误，直接返回信息，推出=======
        //=========接口成功，向服务器写一份数据==========
        //数据库注册一份
        $user2 = new UserInfo;
        $user2->tel = $tel;
        $user2->username = $name;
        $user2->nickname = $nickname;
        $user2->sex = $sex;
        $user2->openid = $openid;
        $user2->headimgurl = $headimgurl;
        $user2->age = $age;
        $user2->kangquanid = 'testid';
        $mysqlres = $user2->allowField(true)->save();
        if ($mysqlres !== false) {
            //x向用户微信发送消息
            $this->send_register_Msg($openid,$name);
        } else {
            $this->return_msg('2', '服务器错误，请你重试');
        }
        //============== end =============

    }//注册 end

    //注册成功发送消息
    public function send_register_Msg($openid,$name)
    {
        //实例化service类
        $template = model('TemplateMes', 'service');
        //组装信息
        $time = date("Y-m-d H:i:s");
        $info = [
            'openid' => $openid,
            'name' => $name,
            'time' => $time
        ];
        //发送
        $templateRes = $template->registerMes($info);
        if ($templateRes['errcode'] == 0) {//模板消息发送成功
            $this->return_msg('0','信息注册成功');
        } else {//模板消息失败
            $this->return_msg('2','注册成功，但是由于微信服务器出现错误，你不会在公众号收到注册成功的消息，但是没有影响。');
        }

    }

    //绑定表单
    public function binding()
    {
        //验证表单token
        //取得手机号
        //调用门诊接口，处理返回信息
        //正确=》在数据库写入信息（即openid与门诊部信息的联合）
        //错误信息，返回前端
        //-----test-----
        //验证token
        $tel = input('post.tel');
        $token = input('__token__');

        //获取用户授权信息
        $user = Session::get('user');
        $openid = $user['openid'];
        $nickname = $user['nickname'];

        //============ start =========
        //验证表单token
        $valid = validate('Token');
        $res = $valid->check(['__token__' => $token]);
        if(!$res){
            $this->return_msg('1',$valid->getError());
        }

        //判断注册了信息
        $user = UserInfo::get(['openid' => $openid]);
        if(!empty($user)){
            $this->return_msg('2','你已经注册过了(一个微信号只能注册一个账号)');
        }

        //=========掉门诊部接口==========
        //=======接口错误，直接返回信息，推出=======
        //=========接口成功，先获取接口返回数据，再向服务器写一份数据==========

        //都成功后先用户微信发送消息
        $this->send_bind_Msg($openid,$nickname);
        //============ end ===========

    }

    //绑定成功后发送消息
    public function send_bind_Msg($openid,$nickname)
    {
        //实例化service类
        $template = \think\Loader::model('TemplateMes', 'service');
        //组装信息
        $time = date("Y-m-d H:i:s");
        $info = [
            'openid' => $openid,
            'name' => $nickname,//name要接口成功才可以获取
            'time' => $time
        ];
        //发送
        $templateRes = $template->bindMes($info);

        //组装返回给前端的信息
        if ($templateRes['errcode'] == 0) {//模板消息发送成功
            $this->return_msg('0','信息绑定成功');
        } else {//模板消息失败
            $this->return_msg('3', '绑定成功，但是由于微信服务器出现错误，你不会在公众号收到注册成功的消息，但是没有影响。');
        }
    }

    //============缴费实现==========
    public function pay($openid)//进来就获取了
    {
        //获取用户带的transaction_id参数（订单号）
        $transaction_id = input('transaction_id');
        if ($transaction_id == null) {//订单可能已经支付，或者用户自己进入支付页面，没有订单参数
            //直接查询该用户是否有订单(该查询没有对应订单号)
            $userPrepay = PrePay::get(['openid' => $openid]);
        } else {
            //在费用表查询该用户有没有对应需要支付的订单号
            $userPrepay = PrePay::get(['openid' => $openid, 'payid' => $transaction_id]);
        }

        if ($userPrepay == null) {//没有账单的情况
            return null;
            //  return view('noPay');
        } else {//有需要支付的账单，直接组装号jsParam给控制器=》前端
            //获取订单号
            $id = $userPrepay->payid;
            //获取金额（单位/分）
            $total_fee = $userPrepay->pay;
            //提示信息
            $body = '康泉门诊缴费中心';
            //回调地址
            $notify_url = 'http://www.kangquanpay.top/successPay';
            //=======myWeChatPay======
            $weChatPay = new weChatPay();
            //配置参数
            $weChatPay->set_appid(APPID);
            $weChatPay->set_notify_url($notify_url);
            $weChatPay->set_mch_id(PAYID);
            $weChatPay->set_pay_key(PAYKEY);
            //开始请求获取jsParam
            return $weChatPay->getJsParam($id, $body, $total_fee, $openid);

//==============第一个方法========
//            //组装数据
//            $data = [
//                'out_trade_no' => $id,
//                'openid' => $openid,
//                'total_fee' => $total_fee,
//                'body' => $body
//            ];
//            //调统一接口api 获取prepay_id
//            $unified = \think\Loader::model('UnifiedOrder', 'service');
//            $prepay_id = $unified->getPrepayId($data);
//            //获取 JsParam(json编码后的字符串)
//            $jsParam = $unified->getJsParam($prepay_id);
//            //返回到前端执行
//            return $jsParam;

            //return view('pay',['jsParam'=>$jsParam]);

            //--------------other demo-------------------
            // //支付代码
            // $wechat = new WeChatPay();
            // $body = "康泉门诊缴费中心";
            // $out_trade_no= $userPrepay->payid;//未定义，我换成我的
            // $total_fee = 1;
            // $jsParam = $wechat->wechat_pubpay($body, $out_trade_no, $total_fee,$openid);
            // return  $jsParam;
        }

    }

    //用户支付成功处理
    //获取通知数据
    //验证签名
    //验证业务结果
    //验证订单号、金额

    //成功执行以下
    //修改redis表
    //修改费用表的状态
    //向用户返回成功消息
    //向门诊返回成功消息successPay
    //向微信返回成功消息
    public function successPay($arrData)
    {
        //验证签名
        $signFun = \think\Loader::model('Sign', 'service');
        $checkRes = $signFun->checkSign($arrData);
        if ($checkRes) {//签名验证成功
            //验证业务结果
            if ($arrData['return_code'] == 'SUCCESS' && $arrData['result_code'] == 'SUCCESS') {
                //业务正确，开始修改状态
                return $this->updateStatus($arrData);

            } else {//业务结果验证失败,写错误日志
                $date = date("Y-m-d h:i:s");//获取时间
                file_put_contents('./log/err/payerr.txt', $date . '-订单号-' . $arrData['transaction_id'] . '-业务结果验证失败' . PHP_EOL, FILE_APPEND);
                die;
            }
        } else {//签名验证失败，写错误日志
            $date = date("Y-m-d h:i:s");//获取时间
            file_put_contents('./log/err/payerr.txt', $date . '-订单号-' . $arrData['transaction_id'] . '-签名验证失败' . PHP_EOL, FILE_APPEND);
            die;
        }

    }

    public function updateStatus($arrData)
    {
        //获取订单号
        $out_trade_no = $arrData['out_trade_no'];
        //验证金额
        $payInfo = PrePay::get(['payid' => $out_trade_no]);//获取数据库该订单信息
        $total_fee = $payInfo->pay;//获取金额
        if ($total_fee == $arrData['total_fee']) {//金额验证成功
            //防止重复请求的错误,在此判断一哈缴费状态
            if ($payInfo != null) {//只有该订单第一成功请求才修改状态
                //-----------修改支付状态(事务操作)(即把该订单移到payed表)-----------
                Db::transaction(function () use ($payInfo) {//注意闭包函数的参数传递方式
                    //在payed表添加
                    $userArr = json_decode(json_encode($payInfo), true);
                    $payed_order = new Payed;
                    $payed_order->allowField(true)->save($userArr);
                    //在pay表删除
                    $payInfo->delete();
                });
                //----------修改redis表-----------

                //--------------向用户发送信息--------------
                $this->template($arrData['openid'], $total_fee);

                //---------------向门诊返回成功消息----------

            }

            //向微信返回成功消息
            return 'success';

        } else {//金额验证失败
            $date = date("Y-m-d h:i:s");//获取时间
            file_put_contents('./log/err/payerr.txt', $date . '-订单号-' . $arrData['transaction_id'] . '-金额验证失败' . PHP_EOL, FILE_APPEND);
            die;
        }

    }

    //向用户发送模板消息
    public function template($openid, $total_fee)
    {
        $feeStr = (string)round(($total_fee / 100.0), 2);//分=》元string
        //获取name
        $user = UserInfo::get(['openid' => $openid]);
        $name = $user->username;
        //组装信息
        $info = [
            'openid' => $openid,
            'costType' => '药费',
            'total_fee' => $feeStr . '元',
            'name' => $name
        ];
        //实例化service类
        $paySuccess = \think\Loader::model('TemplateMes', 'service');
        //发送
        $paySuccess->sucPayMes($info);
    }
}

?>
