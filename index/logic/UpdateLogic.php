<?php
namespace app\index\logic;

use think\Session;
use app\index\model\UserInfo;//用户模型
use think\Validate;

class UpdateLogic
{
    //telForm logic
    public function telForm()
    {

        try {
            $tel = input('post.tel');
            $token = input('__token__');
            //验证token
            $valid = validate('Token');
            $telvalid = validate('User');
            $res = $valid->check(['__token__' => $token]);
            //验证数据
            if ($res) {//token验证成功
                $restel = $telvalid->scene('tel')->check(['tel' => $tel]);
                if ($restel) {//数据验证成功
                    //获取用户的openid
                    $openid = Session::get('user.openid');
                    $user = UserInfo::get(['openid' => $openid]);
                    $user->tel = $tel;
                    $resmysql = $user->isUpdate(true)->save();
                    if ($resmysql !== false) {
                        $backInfo = [
                            'errcode' => '0',
                            'errmsg' => '手机号码修改成功'
                        ];
                    } else {
                        $backInfo = [
                            'errcode' => '3',
                            'errmsg' => '服务器错误，请你重试'
                        ];
                    }
                } else {
                    $backInfo = [
                        'errcode' => '2',
                        'errmsg' => $telvalid->getError() . '请你重试'
                    ];
                }
            } else {
                $backInfo = [
                    'errcode' => '1',
                    'errmsg' => $valid->getError() . '请你重试'
                ];
            }
        } catch (\Exception $e) {
            file_put_contents('err.txt', $e->getMessage());
        }


        return json($backInfo);

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
        if ($res !== false) {//数据库写入成功
            $backInfo = [
                'errcode' => '0',
                'errmsg' => '信息修改成功',
                'successData' => $value
            ];
        } else {
            $backInfo = [
                'errcode' => '2',
                'errmsg' => '服务器处理错误，请你重试'
            ];

        }
        return $backInfo;
    }

    private function updateName($user, $value)
    {
        $rule = ['username' => 'require|chs|length:2,15'];
        $data = ['username' => $value];
        $validate = new Validate($rule);

        if ($validate->check($data)) {//数据验证成功
            $user->username = $value;
            $res = $user->save();
            if ($res !== false) {//数据库写入成功
                $backInfo = [
                    'errcode' => '0',
                    'errmsg' => '信息修改成功',
                    'successData' => $value
                ];
            } else {
                $backInfo = [
                    'errcode' => '2',
                    'errmsg' => '服务器处理错误，请你重试'
                ];

            }
        } else {//数据验证失败
            $backInfo = [
                'errcode' => '1',
                'errmsg' => $validate->getError() . '请你重试'
            ];
        }

        return $backInfo;

    }

    private function updateAge($user, $value)
    {

        $rule = ['age' => 'require|between:1,120'];
        $data = ['age' => $value];
        $validate = new Validate($rule);

        if ($validate->check($data)) {//数据验证成功
            $user->age = $value;
            $res = $user->save();
            if ($res !== false) {//数据库写入成功
                $backInfo = [
                    'errcode' => '0',
                    'errmsg' => '信息修改成功',
                    'successData' => $value
                ];
            } else {
                $backInfo = [
                    'errcode' => '2',
                    'errmsg' => '服务器处理错误，请你重试'
                ];

            }
        } else {//数据验证失败
            $backInfo = [
                'errcode' => '1',
                'errmsg' => $validate->getError() . '请你重试'
            ];
        }

        return $backInfo;


    }


}

?>
