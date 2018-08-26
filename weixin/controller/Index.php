<?php
namespace app\weixin\controller;

use think\Controller;
use app\weixin\exClass\weixin\MesEvent;
use app\weixin\model\LocalList as city;//数据库模型

class Index extends Controller
{

    public function index()
    {
        //获得参数 signature nonce token timestamp echostr
        $nonce = @input('get.nonce');
        $token = TOKEN;
        $timestamp = @input('get.timestamp');
        $echostr = @input('get.echostr');
        $signature = @input('get.signature');
        //形成数组，然后按字典序排序
        $array = [$nonce, $timestamp, $token];
        sort($array);
        //拼接成字符串,sha1加密 ，然后与signature进行校验
        $str = sha1(implode($array));
        if (!empty($echostr) && $str == $signature) {
            //第一次接入weixin api接口的时候
            echo $echostr;
        } else {
            $this->reponseMsg();
        }
    }

    //消息回复函数
    public function reponseMsg()
    {
        $mesEvent = new MesEvent();    //创建一个回复类实例
        //获取用户提交的数据
        $postStr = file_get_contents('php://input');
        //判断是否有数据
        if (!empty($postStr)) {
            //禁止xml应用实体，防止xxe攻击libxml_disable_entity_loader
            libxml_disable_entity_loader(true);
            //将xml对象化获取数据
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            //先取得用户提交的type
            $type = strtolower($postObj->MsgType);

            //对各种信息处理
            switch ($type) {
                //一共有image vioce video location text link event等
                //‘event’
                case 'event':
                    $this->handleEvent($postObj);
                    break;
                //text
                case 'text':
                    $this->replyText($postObj);
                    break;
                //location
                case 'location':
                    $mesEvent->handleLocation($postObj);
                    break;
                //default
                default:
                    $mesEvent->defaultReply($postObj);
                    break;

            }
        } else {
            echo '';
        }
    }//消息处理主函数end


    //event处理函数
    public function handleEvent($postObj)
    {
        $mesEvent = new MesEvent();    //创建一个回复类实例
        //先取得strtolower($postObj->Event)
        $event = strtolower($postObj->Event);

        //对各种event事件做出相应处理
        switch ($event) {
            //关注事件
            case "subscribe":
                $mesEvent->handleSub($postObj);
                break;
            //click事件
            case 'click':
                $this->handleClick($postObj);
                break;
            //默认事件回复
            default:
                $mesEvent->defaultReply($postObj);
                break;
        }
    }


    //click处理函数
    public function handleClick($postObj)
    {
        $mesEvent = new MesEvent();    //创建一个回复类实例
        //取得click_key
        $key = strtolower($postObj->EventKey);//转换小写
        //根据key向用户返回相应信息
        switch ($key) {
            //天气查询处理事件
            case 'weather':
                $mesEvent->handleWeather($postObj);
                break;
            case 'bind':
                $mesEvent->structure($postObj);
                break;
            case 'register':
                $mesEvent->structure($postObj);
                break;
            case 'order':
                $mesEvent->structure($postObj);
                break;
            case 'pay':
                $mesEvent->structure($postObj);
                break;
            case 'myinfo':
                $mesEvent->structure($postObj);
                break;
            default:
                $mesEvent->defaultReply($postObj);
                break;

        }
    }


    //文本回复函数
    public function replyText($postObj)
    {
        $mesEvent = new MesEvent();    //创建一个回复类实例
        //取得用户提交的内容
        $content = $postObj->Content;
        //判断用户输入的内容，在返回对应的内容
        if ((strlen($content) < 20) && (strncasecmp($content, "百度", 2) == 0 || stripos($content, "百度") != false)) {
            //百度函数
            $mesEvent->baidu($postObj);

        } else if ($content == '天气查询' || $content == '天气预报' || $content == '天气') {

            $mesEvent->handleWeather($postObj);
        } else {
            //判断用户输入的内容
            $city = city::get(function ($query) use ($content) {
                $query->where('Ccity|Ecity', '=', $content);
            });
            if ($city == null) {
                //调用默认回复函数
                $mesEvent->defaultReply($postObj);
            } else {
                //取得城市名字
                $cityName = $city->Ecity;
                //调用天气查询函数
                $mesEvent->cityWeather($postObj, $cityName);
            }

        }
    }

    //回复函数end
}

?>
