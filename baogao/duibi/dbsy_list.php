<?php
/**
 * 功能：对比试验列表
 * 作者：Mr Zhou
 * 日期：2014-11-09
 * 描述：
*/
include '../../temp/config.php';
$fzx_id=$u['fzx_id'];
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'对比试验','href'=>$current_url),
);
$trade_global['css'] = array('boxy.css','lims/buttons.css','lims/jbox.css');
$trade_global['js']  = array('boxy.js');

if(!$_GET['year']){
	$_GET['year']=date('Y');
}
if(!$_GET['month']){
	$_GET['month']=date('m');
}
if(empty($_GET['cy_date'])){
	$_GET['cy_date']=date('Y-m');
}
$xm_name = $_SESSION['assayvalueC'];

$query_condition=" and cy.cy_date LIKE '{$_GET[cy_date]}%'";  

if( !isset($_GET['site_type']) )
    $_GET['site_type'] = "全部" ;
$_GET["site_type"] = 0;
if( $_GET["site_type"] != "全部" ) {
    $query_condition .= " AND site_type ={$_GET['site_type']}";
}
$query_condition.=" and `cy`.`status`>=6 ";

$q="SELECT  cy.*,count(`cy_rec`.`id`) as `_sites` ";
$q_="SELECT  count(distinct cy.`id`) as `total` ";
$query="
FROM cy, cy_rec
WHERE 
    cy_rec.cyd_id = cy.id and 
    cy_rec.zk_flag not in(2,6) 
    $query_condition  and fzx_id='".$fzx_id."' group by `cy_rec`.`cyd_id` order by `cy`.`cyd_bh` desc ";
$t=$DB->fetch_one_assoc($q_.$query);

//组合查询站点条件
$R = $DB->query($q.$query);
//遍历站点信息
while($r=$DB->fetch_assoc($R)){
    $zongxm = '';
    $zongxm = $DB->fetch_one_assoc("SELECT  count(id) xm  FROM `assay_pay` where cyd_id={$r['id']} ");
    
    $ywc    = $DB->fetch_one_assoc("SELECT  count(id) ywc FROM `assay_pay` WHERE `cyd_id` ={$r['id']} AND `over` = '$qzjb'");
       $rows = $DB->query("SELECT  `vid`,`site_name`,`tid`,`sid` FROM `assay_order` WHERE `cyd_id` = {$r['id']} AND `chao_biao` =1");
	   $cb = $cb_list ='';
	   while($row = $DB->fetch_assoc($rows)){
			$cb[] .=$row['sid'];       
			$cb_list .= "<li align='left'>
						 <a href='#' tabindex='-1'>$row[site_name] - ".$xm_name[$row[vid]]."</a></li>";
	   }
	
	 if ($cb){
	    $cb_ul ='<ul class="dropdown-menu pull-right">';
		$cb_ul_l ='</ul>';
	    $cb_sl = count($cb);
	 }else{
	 	$cb_ul ='';
		$cb_ul_l ='';
	    $cb_sl ="0";
		$cb_list = '';
	 }
   $sd=$DB->fetch_one_assoc("SELECT  * FROM cy where id={$r['id']} and site_type='3' and cy_flag='0'");
	//检查数据是否需要上传到对接表中
	$sqlDuiJie	= $DB->query("select cyd_id from `duijie` where cyd_id='".$r['id']."'");
	$duiJieNum	= $DB->num_rows($sqlDuiJie);
    $cyd['flag']=$flag[$r['status']];
    switch($r['status']){
        case 6:
			$cyd['flag']='化验中';
            if($u['jcbg_sh'] || $u['jcbg_qf']|| $u['userid']=='admin'){
				$operation="<a href=\"#\" onclick=\"return $(this).qbox({title:'检测报告列表',src:'$rooturl/baogao/duibi/cg_site_list.php?ajax=1&cyd_id=$r[id]',w:800,h:450});\" >查看</a>";
			}
            break;
        case 7:
        case 8:
			if($u['jcbg_sh'] || $u['jcbg_qf']|| $u['userid']=="admin"){
				$operation="<a href=\"#\" onclick=\"return $(this).qbox({title:'检测报告列表',src:'$rooturl/baogao/duibi/cg_site_list.php?ajax=1&cyd_id=$r[id]',w:900,h:500});\" >查看</a>";
			}
            break;
    }

	$lines.=temp("dbsy/dbsy_list_line.html");
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


disp("dbsy/dbsy_list");

?>