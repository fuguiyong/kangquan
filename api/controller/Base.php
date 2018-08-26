<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/15
 * Time: 16:55
 * Explain: pay api base class
 */

namespace app\api\controller;

use think\Controller;
use think\Request;

class Base extends Controller
{
    protected $filterParamArr;//过滤后的参数
    protected $request;

    protected function _initialize()
    {

        $this->request = Request::instance();
        //获取提交的数据
//        $jsonData = file_get_contents('php://input');
//        $this->paramArr = json_decode($jsonData, true);
        //验证时间错
        //$this->check_time($this->request->param('time'));
        //验证token
        $this->check_token($this->request->param());
        //返回过滤字段
        $this->filterParamArr = $this->filter_param($this->request->param());
    }

    /*验证时间function
    */
    public function check_time($time)
    {
        //先判断字段是否存在
        if (empty($time) || intval($time) <= 1) {

            $this->return_msg('4001', '时间戳字段缺少或错误');
        }
        //再判断是否请求超时
        if (time() - intval($time) >= 60) {
            $this->return_msg('4002', '请求超时');
        }

    }

    //验证token函数
    public function check_token($arr)
    {
        dump($arr);die;
        //先判断是否存在token
        if (!isset($arr['token']) || empty($arr['token'])) {
            $this->return_msg('4003', '缺少token参数');
        }
        //验证token
        $client_token = $arr['token'];
        $server_token = '';
        unset($arr['token']);
        foreach ($arr as $value) {
            if (is_array($value)) {
                $value = json_encode($value,JSON_UNESCAPED_UNICODE);
                echo $value;
                die;
            }
            $server_token .= md5($value);
        }
        $server_token = md5('kangquan' . $server_token . 'kangquan');

        if ($client_token !== $server_token) {
            $this->return_msg('4004', 'token不正确');
        }

    }

    //过滤参数
    public function filter_param($arr)
    {
        unset($arr['time'], $arr['token']);
        $validData = \think\Loader::validate('DataFilter');
        if (!$validData->check($arr)) {
            $this->return_msg('4005', $validData->getError());
        }
        //通过测返回参数
        return $arr;
    }

    //返回信息函数
    public function return_msg($errcode, $errmsg = '', $data = null)
    {
        $backInfo = [
            'errcode' => $errcode,
            'errmsg' => $errmsg,
            'data' => $data
        ];

        echo json_encode($backInfo);
        die;
    }

}