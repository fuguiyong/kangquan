<?php
/*
api使用说明
$url = 'http://www.kangquanpay.top/createpaytb'
需要提供的json数据：
$data = ['kangquanid','kangquanrandid','pay']金额单位统一为分
返回码
0=》成功
1=》提交数据有误
2=》该病人没有绑定微信
3=》写入费用表失败
4=》发送付费消息失败
*/
namespace app\api\controller;

use think\Controller;
use app\api\model\User;
use app\api\model\PrePay;

class Pay extends Controller
{
    //生成订单
    public function createPay()
    {
        //获取提交的数据
        $jsonData = file_get_contents('php://input');
        //解码json为array
        $Arrdata = json_decode($jsonData, true);
        //判断数据正确性
        //正确=》判断用户是否绑定微信（userinfo表判断）
        //绑定了组装数据
        //写入数据库
        if (array_key_exists('kangquanid', $Arrdata) && array_key_exists('kangquanrandid', $Arrdata) && array_key_exists('pay', $Arrdata)) {
            //取得数据
            $kangquanid = $Arrdata['kangquanid'];
            $kangquanrandid = $Arrdata['kangquanrandid'];
            $pay = $Arrdata['pay'];
            //判断是否绑定微信
            $user = User::get(['kangquanid' => $kangquanid]);

            if ($user == null) {//未绑定
                $backInfo = [
                    'errcode' => '2',
                    'errmsg' => '该用户没有绑定微信'
                ];
            } else {//绑定了
                //组装数据
                $payid = $this->createPayId();//生成订单号
                $openid = $user->openid;
                //写入费用表
                $newUser = new PrePay;
                $userData = [
                    'payid' => $payid,
                    'openid' => $openid,
                    'kangquanid' => $kangquanid,
                    'kangquanrandid' => $kangquanrandid,
                    'pay' => (int)$pay,
                    'time' => date('Y-m-d h:i:s')
                ];
                $res = $newUser->allowField(true)->save($userData);
                if (false !== $res) {//写入成功

                    //给用户发送支付提醒
                    $result = $this->callUser($openid, $pay, $payid);
                    if ($result['errcode'] == 0) {//发送成功
                        $backInfo = [
                            'errcode' => '0',
                            'errmsg' => 'ok'
                        ];
                    } else {//发送失败
                        $backInfo = [
                            'errcode' => '4',
                            'errmsg' => '给用户发送支付提醒失败'
                        ];
                    }

                } else {//写入失败
                    $backInfo = [
                        'errcode' => '3',
                        'errmsg' => '写入费用表失败'
                    ];
                }
            }
        } else {
            $backInfo = [
                'errcode' => '1',
                'errmsg' => '提交数据有误，请仔细检查'
            ];
        }

        //返回信息
        return json($backInfo);
    }

    //给用户发消息函数 金额单位/分
    public function callUser($openid, $payTotal, $payid)
    {
        //组装数据
        $total = $payTotal / 100.0;
        $total = round($total, 2);//保留两位小数
        $data = [
            'openid' => $openid,
            'costType' => '药费',
            'sum' => (string)$total,
            'transaction_id' => $payid
        ];
        //发送
        $msg = \think\Loader::model('TemplateMes', 'service');
        return $msg->payMes($data);
    }

    //生成订单号
    private function createPayId()
    {
        //time()+6位随即随
        $nonce_str = $this->getNonceStr();
        return time() . 'kangquan' . $nonce_str;
    }

    //随机字符串函数
    private function getNonceStr($length = 6)
    {
        $chars = "0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

}

?>
