<?php
namespace app\index\logic;

use think\Model;
use think\Session;
use app\index\model\UserInfo;//用户模型
use app\index\model\Docter;//医生表模型
use app\index\model\Visit;//就诊历史表模型
use app\index\model\Ordered;//预约表模型
use app\index\model\Scheduling;//排班表模型
use think\Cache;//缓存
use think\Db;
use DateTime;

class OrderLogic extends Model
{
  //-----------取消预约------------
  public function cancelOrder()
  {
    //获取数据
    $openid = Session::get('user.openid');
    $docter = input('post.docter');
    $date = input('post.date');
    //修改数据
    $backInfo = $this->modifyData($openid,$docter,$date);
    //返回数据
    return json($backInfo);
  }

  private function modifyData($openid,$docter,$date)
  {
    //修改mysql对应数据
    $ordered = Ordered::get(['openid'=>$openid,'docter'=>$docter,'date'=>$date]);
    $visit = Visit::get(['openid'=>$openid,'docter'=>$docter,'date'=>$date]);
    $create_time = $ordered->create_time;//后面计算是不是今天得数据
    $docterMod = Docter::get(['name'=>$docter]);
    $delRes1 = $ordered->delete();
    $delRes2 = $visit->delete();

    //修改redis对应数据
    //先判断要取消预约的数据是不是当天预约的那条，这样如果删除了，当天还可以预约。(对today处理)

    $create_time = date_create($create_time);
    $create_time = date_format($create_time,"Y-m-d");
    $isToday = date('Y-m-d')==$create_time?true:false;
    //预约日期
    $dateFormat = date_create($date);
    $date1 = date_format($dateFormat,"Y-m-d");
    $time = date_format($dateFormat,"H:i");

    try {
      //删除医生当天时间段
      $date_docter_id = $date1.'_'.$docterMod->docter_id;
      $docterDay = @Cache::get($date_docter_id);//获取
      $docterArr = @json_decode($docterDay,true);
      $docterArr['is_has'] = $docterArr['is_has']+1;
      $timesArr = $docterArr[$time];
      $docterArr[$time] = array_diff($timesArr,[$openid]);//删除用户
      //重新写入
      $data1 = json_encode($docterArr,JSON_UNESCAPED_UNICODE);//json编码写入
      $res1 = Cache::set($date_docter_id,$data1,new DateTime(date($date1.' 23:59:59')));

      //删除今天记录
      if($isToday){
        $nowDay = @Cache::get('today');
        $nowDayArr = @json_decode($nowDay,true);
        $nowDayArr = array_diff($nowDayArr,[$openid]);//删除记录
        $data2 = json_encode($nowDayArr,JSON_UNESCAPED_UNICODE);//json编码写入
        $res2 = Cache::set('today',$data2,new DateTime(date('Y-m-d 23:59:59')));
      }

      //删除当天记录
      $orderDay = @Cache::get($date1.'_ordered');
      $orderDayArr = @json_decode($orderDay,true);
      $orderDayArr = array_diff($orderDayArr,[$openid]);//删除记录
      $data3 = json_encode($orderDayArr,JSON_UNESCAPED_UNICODE);//json编码写入
      $res3 = Cache::set($date1.'_ordered',$data3,new DateTime(date($date1.' 23:59:59')));

      //判断处理结果
      if($delRes1 !== false && $delRes2 !== false && $res1 !== false && @$res2 !== false && $res3 !== false){
        $backInfo = [
          'errcode'=>'0',
          'errmsg'=>'你已经成功取消预约了。'
        ];
      }else{
        $backInfo = [
          'errcode'=>'1',
          'errmsg'=>'服务器故障，请你重试。'
        ];
      }

      return $backInfo;

    } catch (\Exception $e) {
      file_put_contents('log/err/cancelError.txt',PHP_EOL.date('Y-m-d H:i:s').$e->getMessage(),FILE_APPEND);
    }




  }
  //-----------取消预约end----------

  //开始预约逻辑
  public function orderLogic()
  {
    //获取用户的openid
    $user = Session::get('user');
    $openid = $user['openid'];

    //获取用户数据（一天一次）(每天一次)
    $time = input('time');
    $day = input('day');
    $docter_id = input('docter_id');

    $moreDays = $day>=date('d')?$day-date('d'):date('t')-date('d')+$day;
    $date = date('Y-m-d',strtotime('+'.$moreDays.' day'));
    $date_docter_id = $date.'_'.$docter_id;

    //0判断今天是否预约过
    //当天是否预约过
    //1判断要预约时间段是否可以预约
    //2判断他是否预约过该时间段（一天预约一次不用判断）

    if(Cache::has('today')){//今天预约了
      $nowDay = @Cache::get('today');
      $nowDayArr = @json_decode($nowDay,true);
      if(!empty($nowDayArr) && in_array($openid,$nowDayArr)){//今天预约过
        $backInfo = [
          'errcode' => '1',
          'errmsg' => '你在今天已经预约过了，每天只可以预约一次哦。'
        ];
      }else{//今天美预约过
        //查询想预约当天是否预约过了
        $backInfo = $this->queryOrderDay($openid,$date,$date_docter_id,$time,$docter_id);
      }
    }else{
      //查询想预约当天是否预约过了
      $backInfo = $this->queryOrderDay($openid,$date,$date_docter_id,$time,$docter_id);
    }

    //返回json数据
    return json($backInfo);
  }

  //查询想预约当天是否预约过了
  public function queryOrderDay($openid,$date,$date_docter_id,$time,$docter_id)
  {
    $orderDay = @Cache::get($date.'_ordered');
    $orderDayArr = @json_decode($orderDay,true);
    if(!empty($orderDayArr) && in_array($openid,$orderDayArr)){//当天预约过
      $backInfo = [
        'errcode'=>'2',
        'errmsg'=>'你在当天已经预约过了，对应日期只能预约一次哦。'
      ];
    }else{//当天没预约过
      //判断医生该时间段是否有剩余
      $docterDay = @Cache::get($date_docter_id);
      $docterDayArr = @json_decode($docterDay,true);
      if(@count($docterDayArr[$time])<config('times_max')){//有剩余
        //开始预约
        $backInfo = $this->startOrder($docter_id,$date,$time,$openid);
      }else{//没有剩余
        $backInfo = [
          'errcode'=>'3',
          'errmsg'=>'该医生在该时间段预约人数已满，请你选择其他时间。'
        ];
      }
    }

    return $backInfo;
  }

  //开始预约函数
  public function startOrder($docter_id,$date,$time,$openid)
  {
    //--------------调内部系统预约接口---------

    if(true){//假设调接口成功

      //给用户发模板消息
      //实例化service类
      $template = \think\Loader::model('TemplateMes','service');

      //组装信息
      $user = UserInfo::get(['openid'=>$openid]);
      $docter = Docter::get(['docter_id'=>$docter_id]);
      $docterArr = json_decode(json_encode($docter,JSON_UNESCAPED_UNICODE),true);

      $info = [
        'openid'=>$openid,
        'name'=>$user->username,
        'sex'=>$user->sex,
        'department'=>$docter->Department,
        'doctor'=>$docterArr['name'],
        'seq'=>'666',
        'orderDate'=>$date.' '.$time
      ];

      //发送模板消息
      $templateRes = $template->orderMes($info);
      if($templateRes['errcode']==0){//发送成功
        //----------修改3条件---------
        //医生对应时间段
        $date_docter_id = $date.'_'.$docter_id;
        $docterDay = @Cache::get($date_docter_id);//获取
        $docterDayArr = @json_decode($docterDay,true);//解码
        $docterDayArr['is_has'] = $docterDayArr['is_has']-1;
        $docterDayArr[$time][] = $openid;//add data 修改

        $data = json_encode($docterDayArr,JSON_UNESCAPED_UNICODE);//json编码写入
        $res1 = Cache::set($date_docter_id,$data,new DateTime(date($date.' 23:59:59')));

        //记录今天预约表
        $nowDay = @Cache::get('today');
        $nowDayArr = @json_decode($nowDay,true);
        $nowDayArr[] = $openid;//添加数据
        $data = json_encode($nowDayArr,JSON_UNESCAPED_UNICODE);//json编码写入
        $res2 = Cache::set('today',$data,new DateTime(date('Y-m-d 23:59:59')));

        //记录预约当天时间表
        $orderDay = @Cache::get($date.'_ordered');
        $orderDayArr = @json_decode($orderDay,true);
        $orderDayArr[] = $openid;
        $data = json_encode($orderDayArr,JSON_UNESCAPED_UNICODE);//json编码写入
        $res3 = Cache::set($date.'_ordered',$data,new DateTime(date($date.' 23:59:59')));

        if(true==$res1 && true==$res2 && true==$res3){
          //写入就诊历史表与预约表
          $backInfo = $this->writeInfo($info);
        }else{
          $backInfo = [
            'errcode' => '6',
            'errmsg' => '服务器写入数据失败,请你重试.'
          ];
        }

      }else{//发送模板消息失败时
        //修改数据

        //发送给前端数据
        $backInfo = [
          'errcode' => '5',
          'errmsg' => '微信服务器出项错误，请你重新获取试试。'
        ];
      }


    }else{
      $backInfo = [
        'errcode' => '4',
        'errmsg' => '预约数据获取失败，请你重新提取。'
      ];

    }

    return $backInfo;

  }

  //预约成功处理预约表与就诊历史表
  private function writeInfo($info)
  {
    //获取$info数据
    $openid = $info['openid'];
    $docter =$info['doctor'];
    $department = $info['department'];
    $date = $info['orderDate'];
    //组装数据库data
    $data = [
      'openid'=>$openid,
      'docter'=>$docter,
      'department'=>$department,
      'date'=>$date
    ];
    //写入visit
    $visit = new Visit;
    $res = $visit->allowField(true)->save($data);
    //写入ordered
    $order = new Ordered;
    $res1 = $order->allowField(true)->save($data);
    if($res !== false && $res1 !== false){
      $backInfo = [
        'errcode' => '0',
        'errmsg' => '预约凭据已经发送到公众号，请注意查收'
      ];
    }else{
      $backInfo = [
        'errcode' => '6',
        'errmsg' => '服务器写入数据失败,请你重试.'
      ];
    }

    return $backInfo;
  }

  //获取医生排班信息,返回给前端的数据
  //data = [
  //   'is_scheduling'=>
  //   'inputS'=>]

  public function first_form()
  {

    $backInfo = [];
    //获取该医生id和日期
    $docter_id = @input('docter_id');
    $day = @input('day');
    //$backInfo['id'] = $docter_id;
    //先查询缓存中是否有该医生当天排版记录，否则查询排班表，判断该医生是否排班，如果排班写入缓存
    //组装日期与医生id(判断日期是当天的后几天)
    $moreDays = $day>=date('d')?$day-date('d'):date('t')-date('d')+$day;
    $date = date('Y-m-d',strtotime('+'.$moreDays.' day'));
    $date_docter_id = $date.'_'.$docter_id;

    if(Cache::has($date_docter_id)){//医生当天排班
      //判断redis的数据
      $date_docter = Cache::get($date_docter_id);
      $arrData = json_decode($date_docter,true);

      //判断是否排班
      if($arrData['is_scheduling']==0){
        //返回前端
        $backInfo['is_scheduling'] = 0;
        $backInfo['inputs'] = null;
      }else{

        //----------计算可以预约时间段--------
        $inputsArr = $this->allowTimes($date_docter_id,$date,$arrData['start_time'],$arrData['end_time']);

        //返回前端
        $backInfo['is_scheduling'] = 1;
        $backInfo['inputs'] = $inputsArr;

      }

    }else{//缓存没有，在数据库查询再写入缓存

      $sche = Scheduling::get(['docter_id'=>$docter_id,'date'=>$date]);
      if($sche==null){//当天没有排班
        //返回前端数据
        $backInfo['is_scheduling'] = 0;
        $backInfo['inputs'] = null;
        //写入缓存数据
        $data = [
          'is_scheduling'=>0,
          'is_has'=>0,
          'start_time'=>0,
          'end_time'=>0
        ];
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        Cache::set($date_docter_id,$data,new DateTime(date('Y-m-d 23:59:59',strtotime('+'.$moreDays.' day'))));
      }else{//当天排班了

        //返回前端数据
        $backInfo['is_scheduling'] = 1;
        //----------计算可以预约时间段--------
        $inputsArr = $this->allowTimes($date_docter_id,$date,$sche->start_time,$sche->end_time);
        $backInfo['inputs'] = $inputsArr;
        //写入缓存
        $data = [
          'is_scheduling'=>1,
          'is_has'=>$sche->number,//每天可以预约的人数
          'start_time'=>$sche->start_time,
          'end_time'=>$sche->end_time
        ];
        $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        Cache::set($date_docter_id,$data,new DateTime(date('Y-m-d 23:59:59',strtotime('+'.$moreDays.' day'))));
      }
    }

    //返回以上数据json
    return json($backInfo);
  }

  //计算可以预约时间段-
  private function allowTimes($date_docter_id,$date,$start_time,$end_time)
  {
    $rest_start = config('rest_start');
    $rest_end = config('rest_end');
    $inputs = null;//可以预约时间段
    $i = 1;
    $nextInput = $start_time;
    //判断用户请求的日期是否当天=》是当天要判断是否过期
    if($date==date('Y-m-d')){//是当天
      //先判断end_time是否过了
      if($end_time<date('H:i')){
        return null;
      }

      while($nextInput <= $end_time){
        //判断每个时间段是否过期、是否还有剩余预约
        if($nextInput > date('H:i') && ($nextInput<$rest_start || $nextInput>$rest_end)){
          if($this->is_order($date_docter_id,$nextInput)){
            //生成满足的预约时间段
            $inputs['input'.$i] = $nextInput;
            $i++;
          }
        }
        //计算下一个时间段+30minutes
        $nextInput = date('H:i', strtotime ("+30 minutes", strtotime($nextInput)));
      }

    }else{

      while($nextInput <= $end_time){
        //去掉休息时间
        if($nextInput<$rest_start || $nextInput>$rest_end){
          //判断每个时间段、是否还有剩余预约
          if($this->is_order($date_docter_id,$nextInput)){
            //生成满足的预约时间段
            $inputs['input'.$i] = $nextInput;
            $i++;
          }
        }
        //计算下一个时间段
        $nextInput = date('H:i', strtotime ("+30 minutes", strtotime($nextInput)));
      }

    }
    //返回可以预约数组
    return $inputs;
  }

  //是否还有剩余预约
  private function is_order($date_docter_id,$nextInput)
  {

    if(Cache::has($date_docter_id)){
      $docter = Cache::get($date_docter_id);
      $docterArr =  json_decode($docter,true);

      if(@count($docterArr[$nextInput])<config('times_max')){
        return true;
      }else{
        return false;
      }
    }else{
      return true;
    }
  }

}

?>
