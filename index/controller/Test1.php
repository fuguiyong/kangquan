<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use app\weixin\exClass\weixin\MesEvent;
use app\Weixin\model\LocalList as city;//数据库模型
use app\index\conmon\Test as tt;
use app\weixin\exClass\weixin\WeaApi;
use app\weixin\exClass\weixin\GetToken;
use curl\Curl;
use app\weixin\exClass\weixin\WeAuthorize;
use think\Session;
use service\XmlArray;
use app\index\model\Docter;//docter模型
use think\Cache;//缓存
use DateTime;
use app\index\model\Scheduling;//排班表模型
use aliyunSms\SignatureHelper;
use think\Validate;
use app\index\model\UserInfo;//用户模型
use app\index\model\Visit;//就诊模型
use app\index\model\Ordered;//预约模型
use app\index\logic\UpdateLogic;
use PHPMailer\PHPMailer;

class Test1 extends Controller
{
    public function formValid()
    {
        $data = [
            'name' => input('name'),
            'age' => input('age'),
            '__token__' => input('__token__')
        ];

        $valid = validate('Token');
        $res = $valid->check($data);
        dump($res);
        if ($res) {
            echo 'success';
        } else {
            echo $valid->getError();
        }
        //   $validate = new Validate([
        //     'name'  => 'require|chs|length:2,15',
        //     'age' => 'require|between:1,120'
        //   ]);
        //   $data = [
        //     'name'  => input('name'),
        //     'age' => input('age')
        //   ];
        //   $res = $validate->check($data);
        //
        //   dump($res);
        //   if (!$res) {
        //     dump($validate->getError());
        //   }else{
        //     $user = UserInfo::get(['openid'=>'oyaVw0dgioqRyElCUIS40CuZYiks']);
        //     $user->username = '付贵勇';
        //     $user->age = 20;
        //     $res1 = $user->save();
        //     dump($res1);
        //   }
    }


    public function dayTest()
    {
        vendor('phpqrcode.phpqrcode');
        $data = [
            'out_trade_no' => md5(time()),
            'total_fee' => 1,
            'body' => '扫码支付测试',
            'product_id' => md5(time()) . '520'

        ];
        $weChatPay = \think\Loader::model('UnifiedOrder', 'service');
        $resArr = $weChatPay->native($data);

        $url = $resArr['code_url'];

        \QRcode::png($url, false, 'H', 6, 2, false);
        die;


        /*        $mail = new PHPMailer();
                $mail->isSMTP();// 使用SMTP服务
                $mail->CharSet = "utf8";// 编码格式为utf8，不设置编码的话，中文会出现乱码
                $mail->Host = "smtp.163.com";// 发送方的SMTP服务器地址
                $mail->SMTPAuth = true;// 是否使用身份验证
                $mail->Username = "fuguiyong666@163.com";// 发送方的163邮箱用户名
                $mail->Password = "fu760720981";// 发送方的邮箱密码，注意用163邮箱这里填写的是“客户端授权密码”而不是邮箱的登录密码！
                $mail->SMTPSecure = "ssl";// 使用ssl协议方式
                $mail->Port = 994;// 163邮箱的ssl协议方式端口号是465/994
                $mail->From = "fuguiyong666@163.com";
                $mail->setFrom("fuguiyong666@163.com", "付贵勇");// 设置发件人信息，如邮件格式说明中的发件人，这里会显示为Mailer(xxxx@163.com），Mailer是当做名字显示
                $mail->addAddress('760720981@qq.com', '付贵勇');// 设置收件人信息，如邮件格式说明中的收件人，这里会显示为Liang(yyyy@163.com)
                $mail->IsHTML(true);
                $mail->Subject = 'HELLO PHP';// 邮件标题
                $mail->Body = 'PHP IS THE BEST LANGUAGE';// 邮件正文
                if (!$mail->send()) {// 发送邮件
                    echo "Message could not be sent.";
                    echo "Mailer Error: " . $mail->ErrorInfo;// 输出错误信息
                } else {
                    echo 'Message has been sent.';
                }*/


        //-------------------------
//        header('content-type:application/json;charset=utf-8');
//        $data = [
//            'aa'=>'bbb',
//            'tt'=>'ttrrt'
//        ];
//        die(json_encode($data,JSON_UNESCAPED_UNICODE));

        // return json($data);
//        echo "hello";
//      //  echo phpinfo();
//    $list = Ordered::paginate(3);
//
//    return view('index/test',['list'=>$list]);

        // $arr = ['aaa','bbbb','cccc'];
        // $res = array_diff($arr,['bbb']);
        // dump($res);
        // dump($arr);

        // $oo = Ordered::get(8);
        // $date = $oo->create_time;
        // $date=date_create($date);
        // echo date_format($date,"Y-m-d");

        // echo Cache::clear();


// try {
//   $user = UserInfo::get(['username'=>'aaaa']);
//   $user->together('visit')->delete();
// } catch (\Exception $e) {
//   echo $e->getMessage();
// }


        // $user = Docter::get(['docter_id'=>'d001']);
        // $visit = $user->scheduling;
        //
        // dump(json_decode(json_encode($visit,JSON_UNESCAPED_UNICODE),true));


        // $visit = new Visit;
        // $order = new Ordered;
        // $data = [
        //   ['openid'=>'dhakjycgiuabdbj','department'=>'牙科','date'=>'2018-7-31 21:20:00','docter'=>'安端'],
        //   ['openid'=>'oyaVw0dgioqRyElCUIS40CuZYiks','department'=>'牙科','date'=>'2018-7-31 21:20:00','docter'=>'方法'],
        // ];
        // $res = $visit->allowField(true)->saveAll($data);
        // $res1 = $order->allowField(true)->saveAll($data);
        //
        // if($res !==false && $res1 !==false){
        //   echo 'success';
        // }else{
        //   echo 'error';
        // }


        // //数据库注册一份
        // $user2 = new UserInfo;
        // $user2->tel = '13551150358';
        // $user2->username = 'aaaa';
        // $user2->nickname = 'bbbbbb';
        // $user2->sex = '男';
        // $user2->openid = 'dhakjycgiuabdbj';
        // $user2->headimgurl = 'dahkdjajkbcjbaa';
        // $user2->age = 15;
        // $user2->kangquanid = 'testid5';
        // $mysqlres = $user2->allowField(true)->save();
        // dump($mysqlres);
        // if($mysqlres !==false){
        //   echo 'success';
        // }else{
        //   echo 'failed';
        // }

        // return view('index/test');

        //     try {
        //
        // throw new \Exception("Value must be 1 or below");
        //     } catch( \Exception $e) {
        //         file_put_contents('./log/err/testLog.txt',$e->getMessage().PHP_EOL,FILE_APPEND);
        //         echo 'error';
        //     }
        //------------缓存test---------

    }

    public function hello()
    {
        //redis Test
        //连接本地的 Redis 服务
        $redis = new \Redis();
        $redis->connect('120.77.210.81', 6379);
        $auth = $redis->auth('760720981'); //设置验证密码返回值bool
        if ($auth) {
            //存储数据到列表中
            $redis->lpush("test-list", "hello");
            //设置key过期时间
            $redis->expire('test-list', 30);
            // 获取存储的数据并输出
            $arList = $redis->lrange("test-list", 0, 1);
            print_r($arList);
        } else {
            echo 'pwd error';
        }
    }

    //发送模板消息apitest
    public function sendMsgApiTest()
    {
        //组装数据
        $data = [
            'openid' => 'oyaVw0dgioqRyElCUIS40CuZYiks',
            'sum' => '100'
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);//不转义中文
        //调用接口
        $url = 'http://www.kangquanpay.top/sendmsgApi';
        $res = Curl::postData($url, $data);
        return $res;

    }

    public function loginTest()
    {

        //组装数据
        $data = [
            'school' => '科城',
            'major' => '非英语专业',
            'name' => '怪兽',
            'mobile' => '13551150356',
            'schoolIdcode' => '1641321234',
            'vrifyCode' => 'w2cy'
        ];
        $url = 'http://schoopia.com:90/wap/match/user/reg';
        $data = json_encode($data);
        //开始请求
        $res = Curl::postData($url, $data);
        //$res = file_get_contents($url);
        //观察返回数据
        // $arrData = json_encode($res,true);
        // $errmsg = $arrData['type'];
        // echo "<script>alert({$errmsg})</script>";
        return $res;
    }

    public function getIp()
    {
        dump($_SERVER['REMOTE_ADDR']);
    }

    public function payBackTest()
    {
        $url = 'http://www.kangquanpay.top/successPay';
        //组装数据
        $XmlData = "<xml><appid><![CDATA[wx46df12a8b7baee14]]></appid>
    <bank_type><![CDATA[CFT]]></bank_type>
    <cash_fee><![CDATA[1]]></cash_fee>
    <fee_type><![CDATA[CNY]]></fee_type>
    <is_subscribe><![CDATA[Y]]></is_subscribe>
    <mch_id><![CDATA[1507104451]]></mch_id>
    <nonce_str><![CDATA[iyehhd79l12sikh9kanmi4io6tt02ne8]]></nonce_str>
    <openid><![CDATA[oyaVw0dgioqRyElCUIS40CuZYiks]]></openid>
    <out_trade_no><![CDATA[1529886613kangquan698745]]></out_trade_no>
    <result_code><![CDATA[SUCCESS]]></result_code>
    <return_code><![CDATA[SUCCESS]]></return_code>
    <sign><![CDATA[8C0032A947AE9497BDCEF967FE7B084C]]></sign>
    <time_end><![CDATA[20180625083026]]></time_end>
    <total_fee>1</total_fee>
    <trade_type><![CDATA[JSAPI]]></trade_type>
    <transaction_id><![CDATA[4200000132201806254705167730]]></transaction_id>
    </xml>";
        //请求接口
        return Curl::postData($url, $XmlData);
        //return xml($back);
    }

    //费用表api Test
    public function payTbTest()
    {
        $url = 'http://www.kangquanpay.top/createpaytb';
        $data = [
            'kangquanid' => 'testid',
            'kangquanrandid' => 'testkangquanrandid1',
            'pay' => '1'//单位/分
        ];
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = Curl::curl($url, $body, 'post');
        return json($res);
        //判断费用表是否添加成功，成功了就给用户发消息
    }

    //模板消息服务类test
    public function template()
    {
        //实例化service类
        $register = \think\Loader::model('TemplateMes', 'service');
        //组装信息
        $time = date("Y-m-d H:i:s");
        $info = [
            'openid' => 'oyaVw0dgioqRyElCUIS40CuZYiks',
            'costType' => '药费',
            'total_fee' => '100元',
            'name' => '付贵勇'
        ];
        //发送
        $res = $register->sucPayMes($info);
        dump($res);
    }

    //客服消息接口测试
    public function service()
    {
        //获取access_token
        //设置数据，调用接口
        $token = new GetToken(APPID, APPSECRET);
        $access_token = $token->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
        $href = "<a href='http://www.kangquanpay.top/tobinding'>绑定信息</a>";
        $data = [
            "touser" => "oyaVw0dgioqRyElCUIS40CuZYiks",
            "msgtype" => "text",
            "text" => [
                "content" => $href
            ]
        ];
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = Curl::curl($url, $body, 'post');
        dump($res);
    }

    //api Test
    public function apiTest()
    {
        $url = 'http://www.kangquanpay.top/infotest';
        $data = ['pwd' => 'kangquanuserinfo'];
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = Curl::curl($url, $body, 'post');
        // $res = Curl::curl($url);
        dump($res);
        //  return json($res);
    }

    //网页授权test
    public function seturl()
    {
        //网页授权引导
        $geturl = new WeAuthorize(APPID, APPSECRET);
        $url = "http://www.kangquanpay.top/authtest";
        $url = $geturl->get_authorize_url($url);
        dump($url);
        //  $this->redirect($url);
    }

    public function getUserInfo()
    {
        if (Session::has('user')) {
            $user1 = Session::get('user');
            dump($user1['openid']);
        } else {
            //获取用户信息
            $auth = new  WeAuthorize(APPID, APPSECRET);
            $info = $auth->get_user_info();
            $openid = @$info['openid'];
            $nickname = @$info['nickname'];
            $sex = @$info['sex'];
            $headimgurl = @$info['headimgurl'];
            $user = [
                'openid' => $openid,
                'nickname' => $nickname,
                'sex' => $sex,
                'headimgurl' => $headimgurl
            ];
            Session::set('user', $user);
            dump($info);
        }
        //    $openid = @$info['openid'];
        //    $nickname = @$info['nickname'];
        //    $sex = @$info['sex'];
        //    $headimgurl = @$info['headimgurl'];
    }


    //属性
    //mestest
    public function mestest()
    {
        //data
        /*
        $data = [
        "touser"=>"oyaVw0dgioqRyElCUIS40CuZYiks",
        "template_id"=>"IUTvbFkKC6VygQuWMY1ab-AMq_iVbehwSPP9epRn2bA",
        'url'=>'http://www.kangquanpay.top/tobinding',
        "data"=>[
        "first"=>['value'=>"预约挂号成功"],
        "patientName"=>['value'=>"付贵勇"],
        "patientSex"=>['value'=>"男"],
        "hospitalName"=>['value'=>"康泉综合门诊"],
        "department"=>['value'=>"口腔科"],
        "doctor"=>['value'=>"张三"],
        "seq"=>['value'=>"1234567"],
        "remark"=>['value'=>"康泉门诊欢迎你"]
      ]
    ];
    */
        $data = [
            "touser" => "oyaVw0dgioqRyElCUIS40CuZYiks",
            "template_id" => "Gz7AwKg2K1kkGn2pxMTArSaikDyVqfoo12bdFn0-iG0",
            'url' => 'http://www.kangquanpay.top/tobinding',
            'data' => [
                'first' => ['value' => '你好，以下是你本次的费用清单，点击详情进行缴费', "color" => "#173177"],
                'keyword1' => ['value' => '药费'],
                'keyword2' => ['value' => '666元'],
                'keyword3' => ['value' => '付贵勇'],
                'remark' => ['value' => '感谢你的使用'],
            ]
        ];
//调接口
        $res = $this->sendMes($data);
        dump($res);

    }

    public function sendMes($data)
    {
        //先判断数据是否正确

        //正确，获取access_token
        $token = new GetToken(APPID, APPSECRET);
        $access_token = $token->getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        //请求接口
        $body = json_encode($data, JSON_UNESCAPED_UNICODE);
        $res = Curl::curl($url, $body, 'post');
        return $res;
    }

    public function test(Request $request)
    {
        //  dump($request->param());
        //echo $request->method();
        $tt = new tt();
        $tt->tt($request);
    }

    public function test1()
    {
        $con = 'chengdu';
        //  $ems = new MesEvent();
        $city = city::get(function ($query) use ($con) {

            $query->where('Ecity|Ccity', '=', $con);
        });
        $cityName = $city->Ecity;
        echo $cityName;
        $weaApi = new WeaApi();
        $res = $weaApi->weatherApi($cityName);
        dump($res);
        //  echo THINK_VERSION;
        //  $this->ems->hello();
        //$GLOBALS['emss']->hello();
        //$ems->hello();
    }

}

?>
