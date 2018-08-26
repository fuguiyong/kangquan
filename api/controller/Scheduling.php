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
        //判断每条插入信息是否再排班表有了,有就先删除
        foreach ($paramArr as $value) {
            $docter_id = $value['docter_id'];
            $date = $value['date'];
            $queryObj = SchedulingMod::get(['docter_id' => $docter_id, 'date' => $date]);
            if (!empty($queryObj)) {
                $delRes = $queryObj->delete();
                if ($delRes === false) {
                    $this->return_msg('5002', '数据库处理错误');
                }
            }
        }
        //开始插入
        $this->insert_info($paramArr);
    }

    //插入信息
    public function insert_info($arr)
    {
        //向排班表插入数据
        $sche = new SchedulingMod;
        $res = $sche->allowField(true)->saveAll($arr);

        if ($res !== false) {
            $this->return_msg('0000', 'ok');
        } else {
            $this->return_msg('5001', '排班表插入失败');
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