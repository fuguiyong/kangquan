<?php
/**
 * Created by PhpStorm.
 * User: fugui
 * Date: 2018/8/15
 * Time: 19:23
 */

namespace app\api\validate;

use think\Validate;

class DataFilter extends Validate
{
    protected $rule =   [
        'kangquanid'  => 'require',
        'kangquanrandid' => 'require',
        'pay'   => 'require|number',
    ];

    protected $message  =   [
        'kangquanid'  => 'kangquanid参数必须填写',
        'kangquanrandid' => 'kangquanrandid参数必须填写',
        'pay.require'   => 'pay参数必须填写',
        'pay.number' =>'pay参数必须是数字'
    ];

}