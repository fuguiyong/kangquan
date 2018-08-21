<?php
namespace app\index\controller;

use think\Session;
use app\index\model\UserInfo;//用户模型
use app\index\model\Docter;//医生表模型

class Order extends Base
{
    //挂号首页
    public function ordered()
    {
        //获取信息自动登录
        $url = 'http://www.kangquanpay.top/ordered';
        $this->login($url);

        //判断用户是否注册了，否则先请用户注册
        $openid = Session::get('user.openid');
        $user = UserInfo::get(['openid' => $openid]);
        if ($user == null) {
            return view('index/noBind');
        } else {
            //取得所有医生信息
            $allDocter = Docter::all();
            return view('orderIndex', ['allDocter' => $allDocter]);//全部科室
        }

    }

    //导航栏链接
    public function navLink()
    {
        //取得用户点击的菜单id
        $id = input('id');
        //根据id返回相应内容
        switch ($id) {
            case 1:
                $allDocter = Docter::all(['Department' => '理疗科']);
                return view('liliao', ['allDocter' => $allDocter]);//理疗科
                break;

            case 2:
                $allDocter = Docter::all(['Department' => '中医科']);
                return view('zhongyi', ['allDocter' => $allDocter]);//中医科
                break;

            case 3:
                $allDocter = Docter::all(['Department' => '口腔科']);
                return view('kouqiang', ['allDocter' => $allDocter]);//口腔科
                break;

            case 4:
                $allDocter = Docter::all(['Department' => '妇科']);
                return view('fuke', ['allDocter' => $allDocter]);//妇科
                break;

            case 5:
                $allDocter = Docter::all(['Department' => '内科']);
                return view('neike', ['allDocter' => $allDocter]);//内科
                break;

            case 6:
                $allDocter = Docter::all(['Department' => '外科']);
                return view('waike', ['allDocter' => $allDocter]);//外科
                break;

            default:
                $allDocter = Docter::all();
                return view('orderIndex', ['allDocter' => $allDocter]);//全部科室
                break;
        }

    }

    //预约界面控制器
    public function ordering()
    {
        //获取请求参数docter_id
        $docter_id = input('docter_id');
        //组装给前端的参数
        $data = [];
        //医生信息
        $docter = Docter::get(['docter_id' => $docter_id]);
        $data['docter_id'] = $docter_id;
        $data['headimgUrl'] = $docter->headimgUrl;
        $data['name'] = $docter->name;
        $data['Department'] = $docter->Department;
        //日期
        //预约后7天号数
        $data['day1'] = date("d");
        $data['day2'] = date("d", strtotime("+1 day"));
        $data['day3'] = date("d", strtotime("+2 day"));
        $data['day4'] = date("d", strtotime("+3 day"));
        $data['day5'] = date("d", strtotime("+4 day"));
        $data['day6'] = date("d", strtotime("+5 day"));
        $data['day7'] = date("d", strtotime("+6 day"));
        //获取年分
        $data['year'] = date('Y');
        //返回前端
        return view('orderLayout', ['data' => $data]);
    }

    //预约表单显示
    //需要返回前端，当天医生是否排班、如果排班了每个时间段是否还可预约
    public function formNav()
    {
        $firstForm = \think\Loader::model('OrderLogic', 'logic');
        return $firstForm->first_form();
    }

    //预约数据处理
    public function startOrder()
    {
        //获取用户数据
        $orderLogic = \think\Loader::model('OrderLogic', 'logic');
        return $orderLogic->orderLogic();

    }

}

?>
