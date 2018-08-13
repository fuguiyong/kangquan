<?php
/*
创建菜单
*/
namespace app\weixin\exClass\weixin;

use app\weixin\exClass\weixin\DefineItem;
use app\weixin\exClass\weixin\GetToken;

//康泉
//DEFINE("APPID","wx46df12a8b7baee14");
//DEFINE("APPSECRET","388b830c64e55ae8dfa7c180c2c6585d");

class CreateItems
{

  //之定义菜单方法
  public function definedItems(){

    //定义菜单数组
    $postArr = array(

      'button'=>array(

        //第一个一级菜单门诊部概况
        array(

          'name'=>urlencode('门诊部概况'),
          'sub_button'=>array(
            array(
              'name'=>urlencode('门诊部介绍'),
              'type'=>'view',
              'url'=>'https://mp.weixin.qq.com/mp/homepage?__biz=MzUxMzgyMDYxNw%3D%3D&hid=5&sn=8f91cecdd7f7f5834e2b9f9dd58f39ca'
            ),
            array(
              'name'=>urlencode('医生介绍'),
              'type'=>'view',
              'url'=>'http://mp.weixin.qq.com/mp/homepage?__biz=MzUxMzgyMDYxNw==&hid=4&sn=e102d1fd3600df001dc8c493098dad60#wechat_redirect'
            )

          )
        ),
        //2
        array(
          'name'=>urlencode('微商城'),
          'type'=>'view',
          'url'=>'https://weidian.com/s/1356077541'

        ),

        //第二个一级菜单诊疗服务
        array(

          'name'=>urlencode('个人中心'),

          'sub_button'=>array(

            array(
              'name'=>urlencode('注册档案'),
              'type'=>'click',
              'key'=>'register'
            ),//第一个二级菜单

            array(
              'name'=>urlencode('绑定档案'),
              'type'=>'click',
              'key'=>'bind'
            ),//第二个二级菜单

            array(
              'name'=>urlencode('预约挂号'),
              'type'=>'click',
              'key'=>'order'
            ),//第3个二级菜单

            array(
              'name'=>urlencode('缴费说明'),
              'type'=>'view',
              'url'=>'http://www.kangquanpay.top/payExplain'
            ),//第3个二级菜单

            array(
              'name'=>urlencode('我的信息'),
              'type'=>'click',
              'key'=>'myInfo'
            ),//第3个二级菜单
          )

        )

      )//‘button’ end

    );

    //取得accessToken
    $getToken = new GetToken(APPID,APPSECRET);
    $token = $getToken->getAccessToken();
    //调用自定义菜单方法
    $item = new DefineItem($token,$postArr);
    $res = $item->definedItem();
    dump($res);
  }

}
?>
