<?php
$cvs_ver='$Id: phpadmin.php,v 1.8 2011-08-18 12:04:55 root Exp $';
/*
蓝色php开发平台 phplan V1.0
phpmyadmin接口程序
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
$Id: phpadmin.php,v 1.8 2011-08-18 12:04:55 root Exp $
*/

include "../temp/config.php";
quanxian(admin);
      $_SESSION['myadminhost']=$DB->servername;
      $_SESSION['myadminport']=$DB->port;
      $_SESSION['myadminuserid']=$DB->dbusername;
      $_SESSION['myadminpassword']=$DB->dbpassword;
      $_SESSION['myadmindb']=$DB->dbname;

if($_GET[name]=='') gotourl("/phpmyadmin");
$_GET[name]=strtr($_GET[name],array('`'=>''));
gotourl("/phpmyadmin/tbl_structure.php?lang=zh-utf-8&server=1&db=$DB->dbname&table=$_GET[name]");
?>
