<?php
namespace app\index\logic;

use think\Model;
use think\Session;
use think\Db;
use app\index\model\UserInfo;//用户信息模型
use app\api\model\PrePay;//需要支付费用表模型
use app\index\model\Payed;//已经支付费用表模型
use app\weixin\exClass\weixin\WeAuthorize;//授权服务类
use myWeChatPay\weChatPay;//支付类


class User extends Model
{

    //用户自动登录
    public function login($url)
    {
        //先判断是否登录
        //如果没登陆，判断是否带参数code，是=》获取信息并且登录 ，否=》授权回调
        if (Session::has('user')) {
        } else {
            //实例化授权类
            $urlAuth = new WeAuthorize(APPID, APPSECRET);
            if (input('get.code') != null) {//用户已经回调了，直接获取信息
                $userInfo = $urlAuth->get_user_info();
                $user = [
                    'openid' => $userInfo['openid'],
                    'nickname' => $userInfo['nickname'],
                    'sex' => $userInfo['sex'],
                    'headimgurl' => $userInfo['headimgurl']
                ];
                //设置用户登录信息
                Session::set('user', $user);
            } else {//授权回调
                //动态获取当前的url
                // $url = 'http'.$_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                // $url = 'http://www.kangquanpay.top/pay';
                $redurl = $urlAuth->get_authorize_url($url);
                //构建跳转地址 跳转
                header("location:{$redurl}");
            }
        }
    }


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

        if ($res)//token验证成功
        {
            //先判断是否注册(条件还可以优化)
            $user1 = UserInfo::get(['openid' => $openid]);

            if ($user1 == null) { //未注册

                //-------调接口注册--------

                if (true)//注册接口成功
                {
                    try {

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

                        if ($mysqlres !== false) {//数据库成功
                            //如果都注册成功给用户发送模板消息
                            //实例化service类
                            $template = \think\Loader::model('TemplateMes', 'service');
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
                                //设置返回信息
                                $backInfo = [
                                    'errcode' => '0',
                                    'errmsg' => '请返回公众号继续其他操作，谢谢合作。'
                                ];
                            } else {//模板消息失败
                                //设置返回信息
                                $backInfo = [
                                    'errcode' => '0',
                                    'errmsg' => '但是由于微信服务器出现错误，你不会在公众号收到注册成功的消息，但是没有影响。'
                                ];
                            }

                        } else {//数据库写入失败
                            $backInfo = [
                                'errcode' => '4',
                                'errmsg' => '服务器打瞌睡了，请你重试。'
                            ];
                        }

                    } catch (\Exception $e) {
                        $date = date("Y-m-d h:i:s");//获取时间
                        file_put_contents('./log/err/bind.txt', $date . ' ' . $openid . '注册失败' . PHP_EOL, FILE_APPEND);
                    }

                } else {//注册接口失败
                    $backInfo = [
                        'errcode' => '2',
                        'errmsg' => '服务器打瞌睡了，请你重试。'
                    ];
                }

            } else {//注册过
                $backInfo = [
                    'errcode' => '1',
                    'errmsg' => '你已经注册过了(一个微信号只能注册一个账号)'
                ];
            }
        } else {//token 验证失败
            $backInfo = [
                'errcode' => '3',
                'errmsg' => $valid->getError() . '请你重试'
            ];
        }

        //返回注册结果
        return json($backInfo);
    }//注册 end

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

        $valid = validate('Token');
        $res = $valid->check(['__token__' => $token]);

        if ($res)//token验证成功
        {
            //先判断是否注册了信息
            $user = UserInfo::get(['openid' => $openid]);
            if ($user == null)//没有注册
            {
                //---------调接口----------
                try {

                    //处理接口返回信息
                    //如果成功写入数据库，
                    $newUser = new UserInfo;

                    //接口成功时发送模板消息
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
                        //设置返回信息
                        $backInfo = [
                            'errcode' => '0',
                            'errmsg' => '请返回公众号继续其他操作，谢谢合作。'
                        ];
                    } else {//模板消息失败
                        //设置返回信息
                        $backInfo = [
                            'errcode' => '0',
                            'errmsg' => '但是由于微信服务器出现错误，你不会在公众号收到注册成功的消息，但是没有影响。'
                        ];
                    }

                    //接口失败时

                } catch (\Exception $e) {
                    $date = date("Y-m-d h:i:s");//获取时间
                    file_put_contents('./log/err/bind.txt', $date . ' ' . $openid . '绑定失败' . PHP_EOL, FILE_APPEND);
                }
            } else {//注册了
                $backInfo = [
                    'errcode' => '2',
                    'errmsg' => '该微信已经绑定了信息，请不要重复绑定（目前一个微信号只能绑定一个账号）'
                ];
            }

        } else {
            $backInfo = [
                'errcode' => '1',
                'errmsg' => $valid->getError() . '请你重试'
            ];
        }
        //给前端返回信息
        return $backInfo;
    }
    //预约挂号表单

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
            return $weChatPay->getJsParam($id,$body,$total_fee,$openid);

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
