<?php
/*
统一下单api JDK
*/
namespace app\index\service;

use think\Model;
use service\RandStr;//随机字符串
use service\XmlArray;//xml<=>array
use curl\Curl;

class UnifiedOrder extends Model
{
    //请求统一订单api
    public function unifiedOrder($needData = [])
    {
        //构建原始数据
        //数据加入签名
        //数据=》xml
        //请求接口

        //获取随机字符串
        $nonceStr = RandStr::numberStr();//默认长度32
        //获取商户订单号
        $out_trade_no = $needData['out_trade_no'];
        //获取opendid
        $openid = $needData['openid'];
        //金额(这里单位是分)
        $total_fee = $needData['total_fee'];
        //简单描述
        $body = $needData['body'];
        //回调地址
        $notify_url = 'http://www.kangquanpay.top/successPay';
        //组装数据
        $data = [
            'appid ' => APPID,
            'mch_id' => PAYID,
            'nonce_str' => $nonceStr,
            'body' => $body,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_fee,
            'spbill_create_ip' => $_SERVER['REMOTE_ADDR'],
            'notify_url' => $notify_url,
            'trade_type' => 'JSAPI',
            'openid' => $openid
        ];

        //获取签名数据(有签名的数组)
        $signFun = \think\Loader::model('Sign', 'service');
        $paramArr = $signFun->setSign($data);
        //数据=》xml
        $xmlData = XmlArray::ArrToXml($paramArr);
        //请求接口
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $xmlRes = Curl::postData($url, $xmlData);//返回的是原始数据，没有解码
        //'xml'=>array
        $arrRes = XmlArray::XmlToArr($xmlRes);
        //返回array结果
        return $arrRes;

    }

    //获取prepayid
    public function getPrepayId($oid)
    {
        $arr = $this->unifiedOrder($oid);
        return $arr['prepay_id'];
    }

    //返回统一订单jsParam
    public function getJsParam($prepay_id)
    {
        //获取随机字符串
        $nonceStr = RandStr::numberStr();//默认长度32
        $params = [
            'appId' => APPID,
            'timeStamp' => (string)time(),
            'nonceStr' => $nonceStr,
            'package' => 'prepay_id=' . $prepay_id,
            'signType' => 'MD5'
        ];
        //实例化签名
        $signFun = \think\Loader::model('Sign', 'service');
        $params['paySign'] = $signFun->getSign($params);
        //直接返回json编码的param
        return json_encode($params);
    }
}

?>
