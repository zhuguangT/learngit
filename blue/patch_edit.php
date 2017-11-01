<?
/*
蓝色php开发平台 phplan V1.0
数据库patch在线编辑程序
copyright (C)2006  刘世伟

本程序为自由软件；您可依据自由软件基金会所发表的GNU通用公共授权条款规定，
就本程序再为发布与/或修改；无论您依据的是本授权的第二版或（您自行选择的）任一日后发行的版本。
本程序是基于使用目的而加以发布，然而不负任何担保责任；
亦无对适售性或特定目的适用性所为的默示性担保。详情请参照GNU通用公共授权。
同目录下的文件GPL.txt为协议正文,
如果没有，请写信至自由软件基金会：59 Temple Place - Suite 330, Boston, Ma 02111-1307, USA。
同时附上如何以电子及书面信件与您联系的资料。
或者浏览 http://www.gnu.org/licenses/licenses.html
中文版 http://www.gnu.org/licenses/licenses.cn.html
$Id: patch_edit.php,v 1.4 2010-03-01 08:32:34 lisongsen Exp $
*/

include '../temp/config.php';

$action=getpost('action');
$_GET[name]=getpost('name');
if($action=='') $action='disp';
$name=$_GET[name];
switch($action)
{
case 'disp':
{
if(file_exists("$rootdir/patch/$_GET[name]"))
{
$fp=fopen("$rootdir/patch/$_GET[name]",'r');
$note=strtr(trim(fgets($fp)),array('<br>'=>'','//'=>'')); //取第一行看注释
if($note[0]=='<')
$note=strtr(trim(fgets($fp)),array('<br>'=>'','//'=>'')); //取第2行看注释
$linecount=2;
while(!feof($fp))
{
$linecount++;
$aline=fgets($fp);
if(trim($aline)=='include "top.php";') $lines='';
else if(trim($aline)=='include "bottom.php";') break; 
else if(substr(trim($aline),0,2)!='?>') $lines.=$aline;
}
}
$lines=htmlspecialchars($lines);
disp('patch_disp');
break;
}
case 'new':
$_GET['name']=date('Y-m-d-Hi').'.php';
$_POST['name']=$_GET['name'];
case 'modi':
{
$name=getpost(name);
$_POST[data]=stripslashes($_POST[data]);
$_POST[data]=strtr($_POST[data],array("\r"=>''));
$fp=fopen("$rootdir/patch/$name","w");
if($u[userid]=='admin') $u[userid]='刘世伟';
fwrite($fp,"<?php
//$_POST[note]<br>
echo '<a href=index.php>返回</a><br>
<!-- $u[userid] --><br>';
include \"top.php\";
$_POST[data]
include \"bottom.php\";
?>
");
fclose($fp);
@chmod("./$name",0666);
gotourl("$rooturl/blue/patch_edit.php?action=disp&name=$name");
break;
}
}
toexit();
?>
