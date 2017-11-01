<?php
/**
 * 功能：报告列表显示
 * 作者：罗磊
 * 日期：2014-5-14
 * 描述：
*/
$title='检测报告列表';
$script_name='bg_liebiao';
include '../temp/config.php';
//include 'site_type.php';
if(empty($u['userid'])){
	nologin();
}
$fzx_id=$u['fzx_id'];//获得分中心id
if( !isset($_GET['site_type']) ){
    $_GET['site_type'] = "全部" ;
}
if(!$_GET['year']){
	$_GET['year']=date('Y');
}
if(!$_GET['month']){
	$_GET['month']=date('m');
}
if(empty($_GET['cy_date'])){
	$_GET['cy_date']=date('Y-m');
}
if(!isset($_GET['print_status'])){
	$_GET['print_status']=0;
}
//导航
$trade_global['daohang'][] = array('icon'=>'','html'=>"检测报告列表",'href'=>"./baogao/bg_liebiao.php?cy_date={$_GET['cy_date']}&site_type={$_GET['site_type']}&print_status={$_GET['print_status']}&year={$_GET['year']}&month={$_GET['month']}");
$_SESSION['daohang']['bg_liebiao']	= $trade_global['daohang'];
if($_GET['print_status']){
	$print_status_str="having avg(r.print_status)=1";
}else{
	$print_status_str="having avg(r.print_status)<1 or avg(r.print_status) is null";
}
$xm_name = $_SESSION['assayvalueC'];
//print_rr($mbarr);
$query_condition=" and c.cy_date LIKE '{$_GET[cy_date]}%'";  


if( $_GET["site_type"] != "全部" ) {
    $query_condition .= " AND c.site_type ={$_GET['site_type']}";
}

$query_condition.=" and c.status>=6 ";

//"SELECT  c.*,count(cr.id) as all_site_nums "."FROM cy c LEFT JOIN cy_rec cr ON c.id=cr.cyd_id LEFT JOIN report r ON cr.id=r.cy_rec_id WHERE cr.zk_flag>=0 and cr.sid>=0 "." and c.cy_date LIKE '{$_GET[cy_date]}%'"." AND c.site_type ={$_GET['site_type']}"."  and c.fzx_id='".$fzx_id."' group by c.id  ".$print_status_str." order by c.cyd_bh desc "


$q="SELECT  c.*,count(cr.id) as all_site_nums ";
$q_="SELECT  count(distinct c.id) as total ";
$query="FROM cy c LEFT JOIN cy_rec cr ON c.id=cr.cyd_id LEFT JOIN report r ON cr.id=r.cy_rec_id WHERE cr.zk_flag>=0 and cr.sid>=0 ".$query_condition."  and c.fzx_id='".$fzx_id."' group by c.id  ".$print_status_str." order by c.cyd_bh desc ";
$t=$DB->fetch_one_assoc($q_.$query);
//组合查询站点条件
$R = $DB->query($q.$query);
//遍历站点信息
$xh=0;
while($r=$DB->fetch_assoc($R)){
	$xh++;
	//查询已完成打印的报告
	$print_over_arr=$DB->fetch_one_assoc("SELECT count(*) as over_nums FROM report WHERE print_status=1 AND  cyd_id='".$r['id']."'");
	//查询是否有报告被退回
	$is_back=$DB->fetch_one_assoc("SELECT count(*) as back_nums FROM report WHERE print_status='-1' AND cyd_id='".$r['id']."'");
	$back_warn='';
	if($is_back['back_nums']){
		$back_warn="<span style=\"color:red\">(".$is_back['back_nums']."张报告被退回)</span>";
	}
    $zongxm = '';
    $zongxm = $DB->fetch_one_assoc("SELECT  count(id) xm  FROM assay_pay where cyd_id={$r['id']} ");
    
    $ywc    = $DB->fetch_one_assoc("SELECT  count(id) ywc FROM assay_pay WHERE cyd_id ={$r['id']} AND over = '".$qzjb."'");
    $rows = $DB->query("SELECT  vid,site_name,tid,sid FROM assay_order WHERE `cyd_id` = {$r['id']} AND chao_biao =1");
	$cb_nums=mysql_num_rows($rows);
	if($cb_nums){
		$cb_xm="<a href=\"#\" onclick=\"return $(this).qbox({title:'超标项目列表',src:'$rooturl/baogao/cb_xm_list.php?ajax=1&cyd_id=$r[id]',w:1000,h:500});\" >".$cb_nums."</a>";
	}else{
		$cb_xm=0;
	}
   $sd=$DB->fetch_one_assoc("SELECT  * FROM cy where id={$r['id']} and site_type='3' and cy_flag='0'");
	//检查数据是否需要上传到对接表中
	$sqlDuiJie	= $DB->query("select cyd_id from `duijie` where cyd_id='".$r['id']."'");
	$duiJieNum	= $DB->num_rows($sqlDuiJie);
    $cyd['flag']=$flag[$r['status']];
	if($r['status']==6){
		$cyd['flag']='化验中';
	}
    switch($r['status']){
        case 6:
        case 7:
        case 8:
			if($u['jcbg_sh'] || $u['jcbg_qf']|| $u['userid']=="admin"){
				$operation="<a href=\"#\" onclick=\"return $(this).qbox({title:'检测报告列表',src:'$rooturl/baogao/bg_site_list.php?ajax=1&cyd_id=$r[id]',w:1400,h:500});\" class=\"btn btn-xs btn-primary\" >报告查看下载</a>&nbsp;&nbsp;<a href=\"modi_bg_message_list.php?cyd_id=$r[id]&cy_date=$r[cy_date]\" class=\"btn btn-xs btn-primary\">修改报告信息</a>";
			}
            break;
    }
    //查询样品编号
    $rec_bar	= $DB->fetch_one_assoc("SELECT group_concat(bar_code SEPARATOR '<br /></span><span>样品编号：') as bar FROM `cy_rec` WHERE `cyd_id`='{$r['id']}' AND (`sid`>'0' OR `zk_flag`='1')");
    if(!empty($rec_bar['bar'])){
    	$hide_bar	= "<span>样品编号：".$rec_bar['bar']."<br /></span>";
    }
    $report_bar	= $DB->fetch_one_assoc("SELECT group_concat(bg_lx,year,xuhao,'-',bg_bh SEPARATOR '<br /></span><span>报告编号：') as bar FROM `report` WHERE `cyd_id`='{$r['id']}'");
    if(!empty($report_bar['bar'])){
    	$hide_bar	.= "<span>报告编号：".$report_bar['bar']."<br /></span>";
    }
	$lines.=temp("bg/bg_liebiao_line.html");
}
//获得任务类型
$site_type_list="<option value='全部' >全部</option>";
foreach($global['site_type'] as $key=>$value){
	if($_GET['site_type']=="$key"){
		$site_type_list.="<option selected='selected' value=".$key.">".$value."</option>";
	}else{
		$site_type_list.="<option value=".$key.">".$value."</option>";
	}
}
//所有年
$year_data[] = $_GET["year"];
for( $i = date('Y'); $i >= 2005; $i-- )
    if( $i != $_GET['year'] ) 
        $year_data[] = $i;

$month_data[] = $_GET["month"];

$year_list = disp_options( $year_data );
//所有月
$month_max = ( $_GET['year'] == date('Y') ) ? (int)date('n') : 12;
$month_data = array( $_GET["month"]);
for( $i = $month_max; $i >= 1; $i-- ) {
    $month_text = ( $i < 10 ) ? "0{$i}" : $i;
    if( $month_text != $_GET['month'] )
        $month_data[] = $month_text;
}
$month_list = disp_options( array_unique($month_data) );
//打印状态
$print_sta_arr=array("0"=>"未完成打印","已完成打印");
$print_list="";
foreach($print_sta_arr as $key=>$value){
	if($_GET['print_status']==$key){
		$print_list.="<option value=".$key." selected=\"selected\">".$value."</optoin>";
	}else{
		$print_list.="<option value=".$key.">".$value."</option>";
	}
}
disp("bg/bg_liebiao_list");

?>
