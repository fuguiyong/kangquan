<?php
/*
天气查询类
*/

namespace app\weixin\exClass\weixin;

use think\Controller;

class WeaApi extends Controller
{
    public function weatherApi($x, $y = '')
    {
        //$location = "Beijing"; // 除拼音外，还可以使用 v3 id、汉语等形式
        //判断用户传入的是城市名，还是经纬度，取得实参个数即可
        $num = func_num_args();
        if ($num == 2) {
            $location = $x . ":" . $y;
        } elseif ($num == 1) {
            $location = $x;
        } else {
            $location = 'beijing';
        }

        $key = "7rzdssbpet2gi4wm"; // 测试用 key，请更换成您自己的 Key
        $uid = "U805C16821"; // 测试用 用户 ID，请更换成您自己的用户 ID
        // 获取当前时间戳，并构造验证参数字符串
        $keyname = "ts=" . time() . "&ttl=300&uid=" . $uid;
        // 使用 HMAC-SHA1 方式，以 API 密钥（key）对上一步生成的参数字符串（raw）进行加密
        $sig = base64_encode(hash_hmac('sha1', $keyname, $key, true));
        // 将上一步生成的加密结果用 base64 编码，并做一个 urlencode，得到签名 sig
        $signedkeyname = $keyname . "&sig=" . urlencode($sig);
        // 最终构造出可由前端或服务端进行调用的 url
        $url = "https://api.seniverse.com/v3/weather/daily.json?location=" . $location . "&" . $signedkeyname . "&start=0&days=3";
        //$url = "https://api.seniverse.com/v3/weather/now.json?location=".$location."&".$signedkeyname;
        //开始调用接口
        $res = $this->http_url($url, 'get');
        //返回结果
        return $res;
        // var_dump($res);
    }

    //cUR执行L函数
    public function http_url($url, $type = 'get', $res = 'json', $arr = '')
    {

        //1.初始化curl

        $ch = curl_init();

        //2.设置curl参数
        //避免curl60 error
        if (stripos($url, "https://") !== false) {

            // curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        }

        //设置需求curl参数
        curl_setopt($ch, CURLOPT_URL, $url);//设置接口URl

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, 1);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);//以post传输数据
        }

        //3.采集

        $output = curl_exec($ch);

        if ($res == 'json') {
            if (curl_errno($ch)) {

                //请求失败，返回错误信息

                return curl_errno($ch);
            } else {

                //请求成功,json解码返回

                return json_decode($output, true);
            }
        }
        //4.关闭

        curl_close($ch);
    }//http_url函数end
}//class end
