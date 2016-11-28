<?php

/*
 * @description   文件上传方法
 * @author widuu  http://www.widuu.com
 * @mktime 08/01/2014
 */

/*****************Laravel 调用env配置***************************/
$env = file_get_contents(__DIR__.'/../../../../../.env');
$envArray = explode(PHP_EOL,$env);
$envData=array();
foreach($envArray as &$value){
    if(empty($value)){unset($value);continue;}
    $envTmp=explode('=',$value);
    $envData[trim($envTmp[0])]=trim($envTmp[1]);
}
/*****************Laravel 调用env配置***************************/
global $QINIU_ACCESS_KEY;
global $QINIU_SECRET_KEY;

$QINIU_UP_HOST	= 'http://up.qiniu.com';
$QINIU_RS_HOST	= 'http://rs.qbox.me';
$QINIU_RSF_HOST	= 'http://rsf.qbox.me';

//配置$QINIU_ACCESS_KEY和$QINIU_SECRET_KEY 为你自己的key
$QINIU_ACCESS_KEY	= isset($envData['QINIU_AK'])?$envData['QINIU_AK']:'aCvUjtfc6yG_4FAYJlQZwzUvA5ihmhCKeUnnhLbC';
$QINIU_SECRET_KEY	= isset($envData['QINIU_SK'])?$envData['QINIU_SK']:'um2vOkrIm2VCzL2o-AuUGwn1k5tNUoa7vCpbLnWr';
/*$QINIU_ACCESS_KEY	= 'aCvUjtfc6yG_4FAYJlQZwzUvA5ihmhCKeUnnhLbC';
$QINIU_SECRET_KEY	= 'um2vOkrIm2VCzL2o-AuUGwn1k5tNUoa7vCpbLnWr';*/

//配置bucket为你的bucket
$BUCKET = isset($envData['QINIU_BT'])?$envData['QINIU_BT']:'center';
//$BUCKET = 'center';

//配置你的域名访问地址
$HOST  = isset($envData['QINIU_HOST'])?$envData['QINIU_HOST']:'http://7xosqo.com1.z0.glb.clouddn.com';;
//$HOST  = 'http://7xosqo.com1.z0.glb.clouddn.com';
if(substr($HOST,-1,1)=='/'){
    $HOST = substr($HOST,0,-1);
}
//上传超时时间
$TIMEOUT = "3600";

//保存规则
$SAVETYPE = "date";

//开启水印
$USEWATER = false;
$WATERIMAGEURL = "http://7xosqo.com1.z0.glb.clouddn.com/1449127339.png"; //七牛上的图片地址
//水印透明度
$DISSOLVE = 50;
//水印位置
$GRAVITY = "SouthEast";
//边距横向位置
$DX  = 10;
//边距纵向位置
$DY  = 10;

function urlsafe_base64_encode($data){
	$find = array('+', '/');
	$replace = array('-', '_');
	return str_replace($find, $replace, base64_encode($data));
}


