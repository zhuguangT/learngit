<?php
/**
 * 功能：检查化验单载入文件配置
 * 作者: 郑森
 * 日期: 2014-10-22 
 * 描述: 现在化验项目都根据方法走，去xmfa表中查看化验单的关联数据
*/
include ('../temp/config.php');
if($_POST['action']=='load'&&!empty($_POST['fid'])){
	$ip_rs=$DB->fetch_one_assoc("SELECT `ip` FROM `yq_autoload_set` WHERE fid='{$_POST['fid']}'");
		$nums_rs=$DB->fetch_one_assoc("SELECT count(`id`) as nums FROM `yq_autoload_set` WHERE ip='{$ip_rs['ip']}' ");
		if($nums_rs['nums']>1){
			$load_set_rs=$DB->fetch_one_assoc("SELECT * FROM `yq_autoload_set` WHERE ip='{$ip_rs['ip']}' AND `keyword`=''");
			if(empty($load_set_rs)){
				echo "1";
			}else{
				echo "0";
			}
		}else{
			echo "1";
		}
		exit();
}