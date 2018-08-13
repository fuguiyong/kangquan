<?php
namespace app\index\service;

use think\Model;

class Sign extends Model
{
  /**
	* 获取签名
	* @param array $arr
	* @return string
	*/
    public function getSign($arr){
        //去除空值
        $arr = array_filter($arr);
        if(isset($arr['sign'])){
            unset($arr['sign']);
        }
        //按照键名字典排序
        ksort($arr);
        //生成url格式的字符串
       $str = $this->arrToUrl($arr).'&key='.PAYKEY;
       return strtoupper(md5($str));
    }
    /**
	* 获取带签名的数组
	* @param array $arr
	* @return array
	*/
    public function setSign($arr){
      //$this->getSign($arr);
        $arr['sign'] = $this->getSign($arr);;
        return $arr;
    }
	/**
	* 数组转URL格式的字符串
	* @param array $arr
	* @return string
	*/
    public function arrToUrl($arr){
        $str = urldecode(http_build_query($arr));
        return preg_replace('# #', '',$str);
    }

    //验证签名（有签名的数组）
    public function checkSign($arr)
    {
      //取得签名
      $sign1 = $arr['sign'];
      //生成签名
      $sign2 = $this->getSign($arr);
      if($sign1==$sign2){
      return true;
    }else{
      return false;
    }
}
}
 ?>
