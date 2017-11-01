<?php
/**
 * 功能：标(基)准溶液配制
 * 作者：Mr Zhou
 * 日期：2014-12-02
 * 描述：
*/
include '../../temp/config.php';
$fzx_id=FZX_ID;
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'基础实验','href'=>'#'),
    array('icon'=>'','html'=>'标(基)准溶液配制','href'=>$current_url),
);
$trade_global['js']         = array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','typeahead-bs2.min.js','jquery.maskedinput.min.js');
$trade_global['css']        = array('lims/main.css','datepicker.css','bootstrap-timepicker.css');
$current_date = date('Y-m-d');//当前日期
$sj_yxrq_date = date('Y-m-d',strtotime('+1 month'));//有效日期
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
foreach ($_SESSION['assayvalueC'] as $vid => $valueC) {
    $selected = ($vid==intval($_GET['vid'])) ? 'selected' : '';
    $xm_list .= '<option '.$selected.' value="'.$vid.'">'.$valueC.'</option>';
}
//所有化验员
$fx_user_data = array();
$R = $DB->query( "SELECT * FROM `users` WHERE `fzx_id`='$fzx_id' AND `group`!='0' AND `group`!='测试组' AND `hua_yan`='1' ORDER BY `userid`" );
while( $r = $DB->fetch_assoc( $R ) ){
    $fx_user_data[] = $r['userid'];
    if(''==trim($_GET['userid']) && $u['userid']==$r['userid']){
        $_GET['userid'] = $u['userid'];
    }
}
$pz_user_list = disp_options( $fx_user_data );
$_GET['userid'] = (''==trim($_GET['userid']))?'全部':trim($_GET['userid']);
if('全部'==$_GET['month']){
    $sql_where = " AND YEAR(`pzrq`) = '$_GET[year]'";
}else{
    $sql_where = " AND `pzrq` LIKE '$_GET[year]-$_GET[month]%'";
}
$sql_where .= (intval($_GET['vid']))?" AND `vid`='{$_GET['vid']}'":'';
$sql_where .= ('全部'==trim($_GET['userid']))?'':" AND `pz_user`='{$_GET['userid']}'";

$xuhao = 0;
$sql = "SELECT * FROM `jzry` WHERE `fzx_id`='$fzx_id' $sql_where";
$query=$DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
    $xuhao++;
	$json_data = json_encode($row);
    if($row['pz_user']==$u['userid']||$u['admin']){
        $del = '<a href="#" data=\''.$json_data.'\' class="green icon-edit bigger-130" title="修改'.$row['id'].'"></a>';
        $edit = '<a href="#" data="'.$row['id'].'" class="red icon-remove bigger-130" title="删除'.$row['id'].'"></a>';
    }else{
        $del = '-';
    }
    $lines .= temp('jcsy/bypz/jzry_list_line');
}
disp('jcsy/bypz/jzry_list');
?>