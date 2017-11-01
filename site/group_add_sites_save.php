<?php
/**
 * 功能：添加站点保存
 * 作者：zhangdengsheng
 * 日期：2014-07-14
 * 描述：接收从group_add_sites.html页面传过来的值添加新站点（可添加多条）
*/
require_once "../temp/config.php";
require_once __SITE_ROOT . "inc/site_func.php";
$fzx_id		= FZX_ID;//中心
$jieGuo		= 'no';//ajax添加站点用
$ajax_group	= array();
$site_type	= $_POST['site_type'];//任务类型
$group_name	= trim($_POST['group_name']);//批次
$vids = '';
$xids = '';
if($_POST['xid'])//这里将方法id变成 项目id和方法id
{
	$vids= join(',',$_POST['xid']);
}else{
	if($_POST['vid']){//如果不是 xid 表示 是原来的  站点管理
		$vids = join(',',$_POST['vid']);
	}
}
if($_POST['tjcs_name'])//得到统计参数 
{
	$tjcs=','.join(',',$_POST['tjcs_name']).',';
}else{$tjcs=',,';}
//$_POST['tjcs_name']
for($i=0;$i<count($_POST['site_name']);$i++){
    $site_name = trim($_POST['site_name'][$i]);
    if( $site_name ){
        $site_info = array();
		$site_info['fzx_id']		= $fzx_id;
        $site_info['site_type']		= $site_type;//任务类型
		$site_info['water_type']	= $_POST['customers'][$i];//水样类型
        $site_info['site_name']		= $_POST['site_name'][$i];//站名
		$site_info['fp_id']			= $_POST['fenz'][$i];//分中心
		$site_info['river_name']	= $_POST['river_name'][$i];//区域
		$site_info['xz_area']		= $_POST['xz_area'][$i];//行政区
		$site_info['site_address']	= $_POST['site_address'][$i];//街道
		$site_info['tjcs']			= $tjcs;
		$site_info['site_code']		= $_POST['code'][$i];//站码
		//$site_info['create_date']	= 'now()';//创建时间
        //$sid = is_dup_site( $site_info );
        //if( !$sid )
        $sid = save_new_site( $site_info );//引用save_new_site方法向site表插数据
	    $tjcs1=trim($tjcs,',');
	    $tjcs1=trim($tjcs1);
		$tjcs_n=explode(',',$tjcs1);
		//print_rr($tjcs_n);die;
		if($_POST['action']=='site_add_ajax'){
			$sql = "INSERT INTO site_group SET 
				site_id			= {$sid},
				fzx_id			= '$fzx_id',
				site_type		= '$site_type',
				group_name		= '$group_name',
				cuser			= '{$u['userid']}',
				ctime			= now(),
				assay_values	= '$vids'";//向site_group表插数据
				$DB->query( $sql );
				//判断是否插入成功，并加入数组ajax返回用
				$new_group_id	= $DB->insert_id();
				if((int)$new_group_id>0){
				$jieGuo	= 'yes';
				$ajax_group[$new_group_id]	= $site_info['site_name'];
				}
		}else{//监督任务添加
			foreach ($tjcs_n as $key => $a) {//向site_group表插 统计参数 数据记录
				$sql = "INSERT INTO site_group SET 
				site_id			= {$sid},
				fzx_id			= '$fzx_id',
				site_type		= '$site_type',
				group_name		= '$a',
				cuser			= '{$u['userid']}',
				ctime			= now(),
				assay_values	= '$vids'";//向site_group表插数据
				$DB->query( $sql );
			}
        }  
    }
}
if($_POST['actions']=='tjjdrw'){
	gotourl( "$rooturl/site/site_list_new.php" );
}
if($_POST['action']=='site_add_ajax'){
	echo json_encode(array('jieGuo'=>$jieGuo,'ajax_group'=>$ajax_group));
}else{ //监督任务添加
	gotourl( "$rooturl/site/group_add_sites.php?site_type=0&action=fzxgl" );
}
?>
