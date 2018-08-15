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

use app\api\model\User;
use app\api\model\PrePay;

class Pay extends Base
{
    protected $user;//用户实例
    protected $transaction_id;//交易订单号

    //生成订单
    public function createPay()
    {
        //获取验证成功，过滤后的参数
        $paramArr = $this->filterParamArr;//base类的属性
        //判断用户是否绑定信息
        $this->is_bind($paramArr['kangquanid']);
        //绑定了=》写入费用表
        $this->write_payDb($paramArr);
        //发送模板消息
        $this->send_TemplateMsg($this->user->openid,$paramArr['pay'],$this->transaction_id);
        //以上全部成功，测返回成功消息
        $this->return_msg('0000','ok');
    }

    //发送模板消息函数
    public function send_TemplateMsg($openid,$payTotal,$payid)
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
        $res =  $msg->payMes($data);

        //判断发送结果
        if($res['errcode'] !== 0){
            $this->return_msg('5002','给用户微信发送模板消息失败');
        }
    }

    //判断是否绑定函数
    public function is_bind($kangquanid)
    {
        //判断是否绑定微信
        $this->user = User::get(['kangquanid' => $kangquanid]);
        if ($this->user == null) {
            $this->return_msg('4006', '该用户未绑定微信');
        }
    }

    //写入费用表函数
    public function write_payDb($paramArr)
    {
        //组装数据
        $this->transaction_id = $this->createPayId();//生成订单号
        $openid = $this->user->openid;
        //写入费用表
        $newUser = new PrePay;
        $userData = [
            'payid' => $this->transaction_id,
            'openid' => $openid,
            'kangquanid' => $paramArr['kangquanid'],
            'kangquanrandid' => $paramArr['kangquanrandid'],
            'pay' => (int)$paramArr['pay'],
            'time' => date('Y-m-d h:i:s')
        ];
        $res = $newUser->allowField(true)->save($userData);

        if ($res === false){//写入失败时
            $this->return_msg('5001','写入费用表失败，重试以下');
        }
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
