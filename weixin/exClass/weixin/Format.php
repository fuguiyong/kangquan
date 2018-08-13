<?php
/*
格式化信息类
*/
namespace app\weixin\exClass\weixin;

class Format
{
  	//文本格式函数
      public function formatText($object, $content)
      {
  		//设置文本回复格式
          $textTpl = "<xml>
                      <ToUserName><![CDATA[%s]]></ToUserName>
                      <FromUserName><![CDATA[%s]]></FromUserName>
                      <CreateTime>%s</CreateTime>
                      <MsgType><![CDATA[text]]></MsgType>
                      <Content><![CDATA[%s]]></Content>
                      <FuncFlag>0</FuncFlag>
                      </xml>";
          $resultStr = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
          return $resultStr;
      }

  	//多图文格式化函数
  	public function formatImageText($object,$arr)
  	{
  		//设置回复格式，注意：在填入xml时是变量的话必须用“.”连接
  		    $newsTplHead = "<xml>
                  <ToUserName><![CDATA[%s]]></ToUserName>
                  <FromUserName><![CDATA[%s]]></FromUserName>
                  <CreateTime>%s</CreateTime>
                  <MsgType><![CDATA[news]]></MsgType>
                  <ArticleCount>%s</ArticleCount>
                  <Articles>";
  			$newsTplBody = "<item>
                  <Title><![CDATA[%s]]></Title>
                  <Description><![CDATA[%s]]></Description>
                  <PicUrl><![CDATA[%s]]></PicUrl>
                  <Url><![CDATA[%s]]></Url>
                  </item>";
             $newsTplFoot = "</Articles>
                  <FuncFlag>0</FuncFlag>
                  </xml>";
  			//填入内容
  			//获取菜单长度，并且判断
  		  $bodyCount = count($arr);
  		  $bodyCount = $bodyCount <= 8 ? $bodyCount : 8;
        $body = '';
  		  $head = sprintf($newsTplHead, $object->FromUserName, $object->ToUserName,time(),$bodyCount);
  		  foreach ($arr as $v)
  		  {
  			$body.= sprintf($newsTplBody,$v['title'],$v['description'],$v['picUrl'],$v['url']);
  		  }
  		  $FuncFlag = 0;
        $foot = sprintf($newsTplFoot, $FuncFlag);
  		  return $head.$body.$foot;

  	}


    public function resNews($object,$newsData)
    {
        $CreateTime=time();
        $FuncFlag=0;
        $newTplHeader="<xml>
           <ToUserName><![CDATA[$object->FromUserName]]></ToUserName>
           <FromUserName><![CDATA[$object->ToUserName]]></FromUserName>
           <CreateTime>$CreateTime</CreateTime>
           <MsgType><![CDATA[news]]></MsgType>
           <Content><![CDATA[%s]]></Content>
           <ArticleCount>%s</ArticleCount><Articles>";
        $newTplItem="<item>
         <Title><![CDATA[%s]]></Title>
         <Description><![CDATA[%s]]></Description>
         <PicUrl><![CDATA[%s]]></PicUrl>
         <Url><![CDATA[%s]]></Url>
         </item>";
        $newTplFoot="</Articles>
         <FuncFlag>%s</FuncFlag>
         </xml>";
        $Content='';
        $itemsCount=count($newsData);
        $itemsCount=$itemsCount<10?$itemsCount:10;//微信公众平台图文回复的消息一次最多10条
        if($itemsCount){
         foreach($newsData as $key=>$item){
          if($key<=9){
         $Content.=sprintf($newTplItem,$item['Title'],$item['Description'],$item['PicUrl'],$item['Url']);
       }
         }
     }
        $header=sprintf($newTplHeader,0,$itemsCount);
        $footer=sprintf($newTplFoot,$FuncFlag);
        return  $header.$Content.$footer;
    }

}

 ?>
