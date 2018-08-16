<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/16
 * Time: 12:43
 */

namespace app\api\validate;

use think\Validate;


class DocterScheduling extends Validate
{
    protected $rule = [
        'docter_id' => 'require',
        'date' => 'require|dateFormat:Y-m-d',
        'start_time' => 'require|dateFormat:H:i',
        'end_time' => 'require|dateFormat:H:i',
        'number' => 'require|number'
    ];

    protected $message = [
        'docter_id' => 'docter_id参数必须填写',
        'date.require' => 'date参数必须填写',
        'date.dateFormat' => 'date参数格式错误，例：2018-01-01',
        'start_time.require' => 'strat_time参数必须填写',
        'start_time.dateFormat' => 'start_time参数格式错误，例：09:00',
        'end_time.require' => 'end_time参数必须',
        'end_time.dateFormat' => 'end_time参数格式错误，例：09:00',
        'number.require' => 'number参数必须填写',
        'number.number' => 'number参数必须是数字类型'

    ];
}