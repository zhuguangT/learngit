<?php 
/**
 * 功能：单项质控报告
 * 作者：Mr Zhou
 * 日期：2014-08-19
 * 描述：
*/
include ('../../temp/config.php');
include ($rootdir.'/huayan/assay_form_func.php');
include_once($rootdir.'/huayan/zhikong_func.php');
//强制报错代码
//error_reporting(E_ALL); ini_set('display_errors', '1'); 
//这个方法去掉 数据中的 小于号 
function getvalue($value){
	if(strstr($value,'<')){
		$tx = explode('<',$value);
		return $tx[1];
	}elseif(strstr($value,'＜')){
		$tx = explode('＜',$value);
		return $tx[1];
	}else{
		return $value;
	}
}
//解决出现的科学计数法问题
function getnumber($pj){
	$s = explode('E',$pj);
	if($s[1]){
		$str = '';
		//没有考虑 10个零的
		$x  = substr($s[1],-1,1);
		$y  = substr($s[1],0,1);
		if($y=='-'){
			$str = '0.';
			for($j=1;$j<$x;$j++){
				$str .='0';
			}
			$str.=$s[0]*10;
		}else if($y='+'){
			$str.= $s[0]*10;
			for($j=1;$j<$x;$j++){
				$str .='0';
			}
		}	
		$pj = $str;
	}
	return $pj;
}
//修约  保留三位有效数字
function  xiuyue($shu){
    if($shu<10){
		$shu=_round($shu,2);
	}elseif($shu>=10&&$shu<100){
		$shu=_round($shu,1);
	}else{
		$shu=_round($shu,0);
	}
	return $shu;	
}
//在打印页面和下载的时候不输出此内容
echo $_GET['print']||$_GET['xz']?'':'<input class="btn btn-primary btn-xs" type="button" onclick="location.href+='."'&print=1'".'" value="打印" style="margin-left:1000px;"><br />';
//在下载是不输出css样式,加载excel下载插件
if(intval($_GET['xz']==1)){
	include "zk_xz_header.php";
}else {
?>
<style type="text/css">
*{margin:0;padding:0;}
.bgkd{
    margin-left: auto; 
    margin-right: auto;
    border-collapse: collapse;
    line-height:5.5mm;
    width:60%;
    border:thin solid black;
    align:center;
}
.red {color: #F00}
table.bgkd td,table.bgkd th{
    border: 1px solid black;
    padding:3px;
}
table.bgkd td.noborder{
    border:0px;
}
a{text-decoration:none}
table tr:hover{ background-color: #ccc;}
tr{height:27px;margin:0;padding:0;}
tr td{margin:0;padding:0;font-size:12px;}
input[type="text"]{font-size:12px; width:100%;margin: 0;}
textarea{font-size:9pt; width:100%;margin:0;padding:0;}
</style>
<?php
}
//八种指控表头名称
$index	= intval($_GET['zk_type']);
$year	= $_GET['year'];
$month	= $_GET['month'];
$ids	= $_GET['cyd_id'];
$data	= array();
//$zk_type=array('实验室两空白检测结果比较','实验室与现场空白检测结果比较','密码平行样检测结果','密码平行样检测结果评定','实验室平行样控制结果','实验室平行样检测结果评定','加标回收率检测结果','加标回收率检测结果评定');
include 'zk_view'.$index.'.php';
if(intval($_GET['xz']==1)){
	include "zk_xz_foot.php";
}
?>