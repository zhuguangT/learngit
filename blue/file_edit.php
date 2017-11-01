<?
/*
蓝色php开发平台 phplan V1.0
文件在线编辑程序
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
$Id: file_edit.php,v 1.4 2010-03-01 08:50:54 lisongsen Exp $
*/

include '../temp/config.php';

$action=getpost('action');
$_GET[name]=getpost('name');
if($action=='') $action='disp';
$_GET[name]=strtr($_GET[name],array(".."=>"","\\"=>""));
$_GET[name]=trim($_GET[name]," \r\n\t\x0B\0\/");  //去掉前后的'/'
$path=dirname($_GET[name]);
//if($path=="blue") goback("此目录不能修改");
if($path=="temp") goback("此目录不能修改");
switch($action)
{
case 'disp':
{
$filearray=file("$rootdir/$_GET[name]");
$linesall=count($filearray);
if($_GET[line]>$linesall or $_GET[line]<6 or $linesall<10) $_GET[line]='';
else $_GET[line]-=3;
/*
if($_GET[line]=='' or $_GET[line]==0)
{//寻找第一个 config.php
while($a=each($filearray))
{
if(strpos($a[value],'config.php')>0)
{
if($a[key]>1) $_GET[line]=$a[key];
break; //退出循环
}
}
reset($filearray);
$_GET[line]=array_search("config.php",$filearray);
}
*/

//分裂为2部分
while($a=each($filearray))
{
if($a[key]<$_GET[line]) $lines_hidden.=$a[value];
else 
{
$lines.=$a[value];
}
}
if($lines_hidden!='') $unhide="<span id='unhide' onclick=hide('unhide');unhide('hidearea');setfocus('data'); class=spanclick>隐藏$_GET[line]行内容,点击显示 +</span>";
$linecount=$linesall-$_GET[lines]+2;
$lines_hidden=htmlspecialchars($lines_hidden);
$lines=htmlspecialchars($lines);
$_GET[line]+=4; //让areatext 恢复到原来的高度
disp('file_disp');
break;
}
case 'modi':
{
$_POST[data]=stripslashes($_POST[data]);
$_POST[data]=strtr($_POST[data],array("\r"=>''));
$fp=fopen("$rootdir/$_GET[name]","w");
//if($u[userid]=='admin') $u[userid]='刘世伟';
fwrite($fp,"$_POST[data0]$_POST[data]");
fclose($fp);
@chmod("$rootdir/$_GET[name]",0666);
gotourl("$rooturl/blue/file_edit.php?action=disp&name=$name");
break;
}
}
toexit();
?>
