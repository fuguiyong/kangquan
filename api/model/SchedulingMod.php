<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/16
 * Time: 14:30
 */

namespace app\api\model;

use think\Model;

class SchedulingMod extends Model
{
    protected $table = 'docter_scheduling';
    //  protected $autoWriteTimestamp = true;
    //类型转换
    protected $type = [
        'date' => 'datetime:Y-m-d',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];
}