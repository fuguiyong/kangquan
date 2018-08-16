<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
//动态注册方法
use think\Route;

//---------test---------
Route::any('test/[:id]$', 'index/test/test');
Route::any('test1$', 'index/test/test1');
Route::any('weixin/test/:name$', 'weixin/index/test');
Route::any('token$', 'index/index/tokenTest');
Route::any('auth1$', 'index/Test/seturl');
Route::any('authtest$', 'index/Test/getUserInfo');
Route::any('jsapitest$', 'index/index/jsapitest');
Route::any('valid$', 'index/index/getValid');
//Route::any('geturl$', 'index/index/getUrl');
Route::any('indextest$', 'index/Order/formNav');
Route::any('test3$', 'index/Test/payBackTest');
Route::any('test2$', 'index/Test1/dayTest');
Route::any('infotest$', 'api/Test/sendUserInfo');//接口路由
Route::any('api$', 'index/Test1/payTbTest');//测试接口
Route::any('service$', 'index/Test/template');//客服接口接口
Route::any('shop$','shop/index/index');//shop首页

//--------start-----------
//--------------微信公众号配置------------
Route::any('weixin/config$', 'weixin/index/index');//微信配置
Route::any('CreateItems/:pwd$', 'weixin/CreateItems/index');//创建菜单
//--------------用户逻辑模块----------------
Route::any('register$', 'index/Register/register');//注册首页
Route::any('ordered$', 'index/Order/ordered');//预约挂号引导路由
Route::any('tobinding$','index/Bind/toBind');//绑定信息引导路由
Route::any('pay/[:transaction_id]$','index/Pay/toPay');//支付引导路由
Route::any('successPay$','index/Pay/payBack');//支付回调地址
Route::get('payExplain$','index/Pay/payExplain');//支付回调地址
Route::get('getDataView$','index/index/getDataView');//支付回调地址
//--------api---------
Route::post('api/createpaytb$','api/Pay/createPay');//创建费用表api
Route::post('sendmsgApi$','api/SendMes/sendMsg');//发送模板消息api
Route::post('api/scheduling','api/Scheduling/insert_scheduling');//发送模板消息api


return [
  '__pattern__' => [
    'name' => '\w+',
  ],
  '[hello]'     => [
    ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
    ':name' => ['index/hello', ['method' => 'post']],
  ],

];
