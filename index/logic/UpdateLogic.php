<?php
namespace app\index\logic;

use think\Session;
use app\index\model\UserInfo;//用户模型
use think\Validate;

class UpdateLogic extends Base
{
    //telForm logic
    public function telForm()
    {

            $tel = input('post.tel');
            $token = input('__token__');
            //验证token
            $valid = validate('Token');
            $telvalid = validate('User');

            //===========strat============
            //验证数据
            $tokenRes = $valid->check(['__token__' => $token]);
            $dataRes =  $telvalid->scene('tel')->check(['tel' => $tel]);

            if(!$tokenRes){
                $this->return_msg('1',$valid->getError());
            }

            if(!$dataRes){
                $this->return_msg('2',$valid->getError());
            }

            //修改数据
            //获取用户的openid
            $openid = Session::get('user.openid');
            $user = UserInfo::get(['openid' => $openid]);
            $user->tel = $tel;
            $resmysql = $user->isUpdate(true)->save();

            if($resmysql !== false){
                $this->return_msg('0','手机号码修改成功');
            }else{
                $this->$this->return_msg('3','服务器错误，请你重试');
            }

            //============end==============

    }

    //注销逻辑
    public function cancel()
    {
        //获取用户的openid
        $openid = Session::get('user.openid');
        //开始注销
        //删除用户信息表数据
        $user = UserInfo::get(['openid' => $openid]);
        $res1 = $user->delete();

        //----删除用户的预约表、就诊表-----

        //返回注销结果
        if (false === $res1) {//注销成功
            $backInfo = [
                'errcode' => '1',
                'errmsg' => '服务器处理错误，请你重试'
            ];
        } else {
            $backInfo = [
                'errcode' => '0',
                'errmsg' => '你已经成功注销信息。'
            ];


        }

        return json($backInfo);

    }

    //修改信息逻辑
    public function updateInfo()
    {
        $backInfo = [];
        //获取数据
        $param = input('param');
        $value = input('value');
        //开始修改
        $openid = Session::get('user.openid');
        $user = UserInfo::get(['openid' => $openid]);

        switch ($param) {
            case 'sex':
                $backInfo = $this->updateSex($user, $value);
                break;

            case 'username':
                $backInfo = $this->updateName($user, $value);
                break;

            case 'age':
                $backInfo = $this->updateAge($user, $value);
                break;

        }

        return json($backInfo);

    }

    private function updateSex($user, $value)
    {
        $user->sex = $value;
        $res = $user->save();

        if($res !== false){
            $this->return_msg('0','性别修改成功',$value);
        }else{
            $this->return_msg('1','服务器错误，请你重试');
        }

    }

    private function updateName($user, $value)
    {
        $rule = ['username' => 'require|chs|length:2,15'];
        $msg = [
            'username.require' => '名字必须',
            'username.chs' => '名字必须是汉字',
            'username.length' => '名字长度2-15'
        ];
        $data = ['username' => $value];
        $validate = new Validate($rule,$msg);

        //验证数据
        if (!$validate->check($data)) {
            $this->return_msg('1', $validate->getError());
        }
        //修改数据
        $user->username = $value;
        $res = $user->save();
        if ($res !== false) {
            $this->return_msg('0', '名字修改成功', $value);
        } else {
            $this->return_msg('2', '服务器错误，请你重试');
        }


    }

    private function updateAge($user, $value)
    {

        $rule = ['age' => 'require|between:1,120'];
        $msg = [
            'age.require' => '必须输入年龄',
            'age.between' => '年龄只能是1-120'
        ];
        $data = ['age' => $value];
        $validate = new Validate($rule, $msg);

        //===========start===========
        //验证数据格式
        if (!$validate->check($data)) {
            $this->return_msg('1', $validate->getError());
        }
        //写入数据库
        $user->age = $value;
        $res = $user->save();
        if ($res !== false) {
            $this->return_msg('0', '年龄修改成功', $value);
        } else {
            $this->return_msg('2', '服务器处理错误，请你重试');
        }
        //============end=============

    }


}

?>
