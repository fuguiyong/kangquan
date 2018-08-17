<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/16
 * Time: 12:15
 * api说明url：https://www.zybuluo.com/fuguiyong/note/1252443
 */

namespace app\api\controller;

use app\api\model\SchedulingMod;

class Scheduling extends Base
{

    //api 入口
    public function insert_scheduling()
    {

        //获取验证成功，过滤后的参数
        $paramArr = $this->filterParamArr;//base类的属性
        //想排班表插入数据
        $sche = new SchedulingMod;
        $res = $sche->allowField(true)->saveAll($paramArr);

        if($res !== false){
            $this->return_msg('0000','ok');
        }else{
            $this->return_msg('5001','排班表插入失败');
        }

    }

    //重新token验证规则
    public function check_token($arr)
    {
        //先判断是否存在token
        if (!isset($arr['token']) || empty($arr['token'])) {
            $this->return_msg('4003', '缺少token参数');
        }
        //验证token
        $client_token = $arr['token'];
        //验证规则
        $server_token = md5('kangquan' . md5($arr['time']) . 'kangquan');

        if ($client_token !== $server_token) {
            $this->return_msg('4004', 'token不正确');
        }
    }

    //重新过滤参数规则
    public function filter_param($arr)
    {
        unset($arr['time'], $arr['token']);
        //判断data是不是array
        if (!is_array($arr['data'])) {
            $this->return_msg('4005', 'data参数错误，不是数组类型');
        }
        //判断data里的内容是不是array
        foreach ($arr['data'] as $value) {
            if (is_array($value)) {
                //验证每条信息
                $this->check_info($value);
            } else {
                $this->return_msg('4006', 'data里面内容错误，内容必须全部是数组类型');
            }
        }
        //返回过滤后的数据
        return $arr['data'];

    }

    //验证盘班表的每条信息
    public function check_info($arr)
    {
        $valid_ScheInfo = \think\Loader::validate('DocterScheduling');
        if (!$valid_ScheInfo->check($arr)) {
            $this->return_msg('4007', $valid_ScheInfo->getError());
        }


    }

}