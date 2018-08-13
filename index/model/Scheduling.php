<?php
namespace app\index\model;

use think\Model;

class Scheduling extends Model
{
  protected $table = 'docter_scheduling';
  //  protected $autoWriteTimestamp = true;
  //类型转换
  protected $type = [
    'date'    =>  'datetime:Y-m-d',
    'start_time'     =>  'datetime:H:i',
    'end_time'  =>  'datetime:H:i',
  ];
}

?>
