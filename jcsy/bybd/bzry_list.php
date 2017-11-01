<?php
/**
 * 功能：标准溶液标定列表程序
 * 作者：Mr Zhou
 * 日期：2014-12-04
 * 描述：
*/
include "../../temp/config.php";
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'基础实验','href'=>'#'),
    array('icon'=>'','html'=>'标准溶液标定原始记录表','href'=>$current_url),
);
$trade_global['js']         = array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','typeahead-bs2.min.js');
$trade_global['css']        = array('lims/main.css','datepicker.css','bootstrap-timepicker.css');
$current_date = date('Y-m-d');
$sj_yxrq_date = date('Y-m-d',strtotime('+1 month'));
$fzx_id=FZX_ID;
/*******************************************************/
if($u['is_zz']){
    $fzx_id = $_GET['fzx_id'] =  intval($_GET['fzx_id']) ? intval($_GET['fzx_id']) : $fzx_id;
    $sql = "SELECT * FROM `hub_info`";
    $query = $DB->query($sql);
    $hub_info_select = '实验室名称：<select name="fzx_id">';
    while ($row=$DB->fetch_assoc($query)) {
        $select = ($row['id']==intval($_GET['fzx_id']))? 'selected' : '';
        $row['hub_name'] = str_replace('辽宁省水环境监测中心', '', $row['hub_name']);
        empty($row['hub_name']) && $row['hub_name']='辽宁省水环境监测中心';
        $hub_info_select .= '<option '.$select.' value="'.$row['id'].'">'.$row['hub_name'].'</option>';
    }
    $hub_info_select .= '</select>';
}else{
    $hub_info_select = '';
}
/*******************************************************/
if(!$_GET['status']) $_GET['status']='全部';
$all_status=array('正在标定','正在使用','已停用');
for($i=0;$i<count($all_status);$i++){
    if($all_status[i]!=$_GET['status']){
    	$status_list.='<option value="'.$all_status[$i].'">'.$all_status[$i].'</option>';
    }
}
if($_GET['status']!='全部') $status_list.='<option value="全部">全部</option>';
//年份
$_GET['year'] = empty($_GET['year'])?date('Y'):$_GET['year'];
for($i=date('Y');$i>=$begin_year;$i--){
    $select = ($i==$_GET['year'])? 'selected' : '';
    $year_list.='<option '.$select.' value="'.$i.'">'.$i.'年</option>';
}
//月份
$_GET['month'] = empty($_GET['month'])?date('m'):$_GET['month'];
$month_list = '<option value="全部">全部</option>';
$last_month = ($_GET['year']<date('Y')) ? 12 : date('n');
for($i=$last_month;$i>=1;$i--){
    $select = ($i==intval($_GET['month']))? 'selected' : '';
    $month_list .= '<option '.$select.' value="'.($i<10?'0'.$i:$i).'">'.($i<10?'0'.$i:$i).'月</option>';
}
//项目列表
$xm_list = '';
$sql = "SELECT v.id,v.value_C FROM `assay_value` v LEFT JOIN `jzry` j ON v.id=j.vid WHERE j.fzx_id='$fzx_id' AND `sj_yxrq`>=curdate() GROUP BY j.vid ORDER BY CONVERT( `value_C` USING gbk )";
$query = $DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
    $selected = ($row['id']==intval($_GET['vid'])) ? 'selected' : '';
    $xm_list .= '<option '.$selected.' value="'.$row['id'].'">'.$row['value_C'].'</option>';
}
$fx_user_data = array();
$sql = "SELECT DISTINCT `userid` FROM `users` u LEFT JOIN `jzry_bd` j ON u.userid=j.fx_user WHERE j.`fzx_id`='$fzx_id' AND `fx_user` IS NOT NULL AND fx_user != '' ORDER BY `userid`";
$R = $DB->query( $sql );
while( $r = $DB->fetch_assoc( $R ) ){
	if(''==trim($_GET['userid']) && $u['userid']==$r['userid']){
		$_GET['userid'] = $u['userid'];
	}
    $selected = ($r['userid']==trim($_GET['userid'])) ? 'selected' : '';
    $pz_user_list .= '<option '.$selected.' value="'.$r['userid'].'">'.$r['userid'].'</option>';
}
//所有化验员
$_GET['userid'] = (''==trim($_GET['userid']))?'全部':trim($_GET['userid']);
##
//状态为`正在使用`的标定日期已超过30天的标准溶液自动停用
$out_date_flag=date('Y-m-d',strtotime('-1 month'));
$DB->query("UPDATE `jzry_bd` SET `status`='已停用' WHERE `status` IN ('正在使用','正在标定') AND `bzry_bdrq`<'$out_date_flag'");
if('全部'==$_GET['month']){
    $_GET['month']='';
}
$sql_where = " AND `bzry_bdrq` LIKE '$_GET[year]-$_GET[month]%'";
$sql_where .= (intval($_GET['vid']))?" AND `vid`='{$_GET['vid']}'":'';
$sql_where .= ('全部'==trim($_GET['userid']))?'':" AND `fx_user`='{$_GET['userid']}'";
$sql_where .= ($_GET['status'] && $_GET['status']=='全部')?'':" AND `status`='{$_GET['status']}'";
$i=0;
$sql ="SELECT * FROM  `jzry_bd` WHERE `fzx_id`='$fzx_id' $sql_where ORDER BY `id` DESC";
$result=$DB->query($sql);
while($r=$DB->fetch_assoc($result)){
    $i++;
    $xm = $_SESSION['assayvalueC'][$r['vid']];
    $del = $edit ='';
    $qianzi_status = '';
    if(''!=$r['sh_qz_date']){
        $qianzi_status = '已审核';
    }else if(''!=$r['fh_qz_date']){
        $qianzi_status = '已复核';
    }else if(''!=$r['jh_qz_date']){
        $qianzi_status = '已校核';
    }else if(''!=$r['fx_qz_date']){
        $qianzi_status = '已签字';
    }else{
        $qianzi_status = '未签字';
    }
    if((trim($r['fx_user'])==trim($u['userid'])&&''==$r['sign_01'])||$u['admin']){
        $del = ' | <a href="#" data="'.$r['id'].'" class="red icon-remove bigger-130" title="删除"></a>';
        $edit ='<a href="bzry_bd.php?fzx_id='.$fzx_id.'&bd_id='.$r[id].'&action=edit" class="green icon-edit bigger-130" title="修改"></a>';
    }else{
        $edit ='<a href="bzry_bd.php?fzx_id='.$fzx_id.'&bd_id='.$r[id].'&action=view" class="blue icon-zoom-in bigger-130" title="查看"></a>';
    }
	$lines.=temp('jcsy/bybd/bzry_list_line.html');
}
disp('jcsy/bybd/bzry_list');
?>
