<?php
include '../../temp/config.php';
###################导航
$daohang = array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'检验方法配置','href'=>'system_settings/assay_method/assay_method_list.php'),
		array('icon'=>'','html'=>'配置水样类型下的项目模板','href'=>'system_settings/assay_method/xm_muban.php?lx='.$_GET['lx'])
);
$trade_global['daohang'] = $daohang;
$fzx_id= FZX_ID;//中心
$sl=$_GET['lx'];
$sname = $DB->fetch_one_assoc("SELECT * FROM `leixing` WHERE id=$sl");
 $lname= $sname['lname'];
$lxlist=get_syleibie($sl);//获取水样类型
##################获取项目
/*if($sname[parent_id]!=0){//查询当子水样类型时的项目
	$sql = $DB->query("SELECT DISTINCT vid,value_C FROM `assay_jcbz` AS aj JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id  WHERE n.module_value2 IN ($sl,$sname[parent_id]) AND n.module_value3='1'  ORDER BY aj.vid ASC" );
	$t=array();
	while($r = $DB->fetch_assoc($sql))
	{
	$t[]=$r['vid'];
	}
}else{
	$sql = $DB->query("SELECT DISTINCT vid,value_C FROM `assay_jcbz` AS aj JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id  WHERE n.module_value2 =$sl AND n.module_value3='1'  ORDER BY aj.vid ASC" );
	$t=array();
	while($r = $DB->fetch_assoc($sql))
	{
	$t[]=$r['vid'];
	}
}
*/
$sql	= $DB->query("SELECT id as vid,value_C FROM `assay_value` where 1 ORDER BY seq,value_C");
$t=array();
        while($r = $DB->fetch_assoc($sql))
        {
        $t[]=$r['vid'];
		$value_options .= "<option value='{$r['vid']}'>{$r['value_C']}</option>";
        }
#########取出所有的项目模板
$xmmb_options	= '';
$sql_xmmb	= $DB->query("SELECT * FROM `n_set` WHERE fzx_id='$fzx_id' and module_name='xmmb'");// and module_value3='$site_type'");
while($rs_xmmb	= $DB->fetch_assoc($sql_xmmb)){
	$xmmb_options	.= "<option value='{$rs_xmmb['module_value1']}'>{$rs_xmmb['module_value2']}</option>";
}
##################获取已有项目
	/*$sql= $DB->query("SELECT aj.value_C AS value_C,aj.vid AS vid FROM `assay_jcbz` AS aj  INNER JOIN `xmfa` AS xf ON aj.vid=xf.xmid INNER JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id  WHERE  xf.fzx_id='".$fzx_id."'  AND n.module_value2=$sl AND n.module_value3='1' AND xf.lxid =$sl AND xf.mr='1' GROUP BY `vid`");*/
	$sql= $DB->query("SELECT av.value_C AS value_C,av.id AS vid FROM `xmfa` AS xf JOIN `assay_value` AS av ON xf.xmid=av.id WHERE  xf.fzx_id='".$fzx_id."' AND xf.lxid =$sl AND xf.mr='1' GROUP BY `vid` ORDER BY av.seq,av.value_C");
	while($sqll =$DB->fetch_assoc($sql)){
		$tt[]=$sqll[vid];
		$vid=$sqll[vid];
		$glxm.=

		"<label class=\"show\" style=\"float: left; margin-bottom: 1px; margin-left: 1px; height: 43px; width: 132px; border: 1px solid rgb(215, 215, 215); background-color: rgb(255, 255, 255); cursor: pointer;text-align: left;\"><input type=\"checkbox\" checked=\"checked\" name=\"vid[]\" value=\"{$vid}\" />$sqll[value_C]</label>";

	}
	$xm_values=implode(',',$tt);
$glxmsum=count($tt);
##################获取未有项目
if(isset($t)||isset($tt)){
	if(isset($t)&&isset($tt)){
		$w = array_diff( $t, $tt );
	}
	if(!isset($tt)&&isset($t)){
		$w =$t;
	}
	foreach ($w as $key=>$value)
	{   
		
		//$value_C = $DB->fetch_one_assoc("SELECT DISTINCT vid,value_C FROM `assay_jcbz` AS aj JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id  WHERE vid=$value AND n.module_value2 IN ($sl,$sname[parent_id]) AND n.module_value3='1' ");
		//有些检测项目是没有限值的，没有限值也需要检测（如:悬浮物、叶绿素等）
		$value_C	= $DB->fetch_one_assoc("SELECT id as vid,value_C FROM `assay_value` where id='$value' ORDER BY seq,value_C");
		$vid=$value_C[vid];
		$wglxm.="<label class=\"show\" style=\"float: left; margin-bottom: 1px; margin-left: 1px; height: 43px; width: 132px; border: 1px solid rgb(215, 215, 215); background-color: rgb(255, 255, 255); cursor: pointer;text-align: left;\"><input type=\"checkbox\" $checked name=\"vid[]\" value=\"{$vid}\" />$value_C[value_C]</label>";
	}
	$wglxmsum=count($w);
}
	
//print_rr($t);
//print_rr($tt);
//print_rr($w);
/*###################得到站点信息
$sid = 0;
if ( isset( $_GET['site_id'] ) && (int)$_GET['site_id'] != 0 )
    $sid = (int)$_GET['site_id'];
if ( !$sid )
    error_show( '非法站点编号' );


if ( $_GET['group_name'] ) {
    $av_flag = true;
    $sql = "
        SELECT s.*,group_name,sg.assay_values FROM site_group AS sg LEFT JOIN sites AS s
        ON sg.site_id = s.id
        WHERE sg.site_id = $sid AND sg.group_name='{$_GET['group_name']}'";
}else{
    $sql = "SELECT * FROM sites WHERE id = '$sid' ";
}
$site_info = $DB->fetch_one_assoc( $sql );
if ( !$site_info ){
    error_show('该站点不存在');
}

$leix=get_syleibie($site_info['water_type']);//获取水样类型

#####################该站点目前关联的化验项目
$site_info['current_assayvalue'] = array();
$site_info['current_assay_value'] = array();
if ( $site_info['assay_values'] ){
	$site_info['current_assayvalue'] = explode( ',',$site_info['assay_values']);//获取化验 元素的编号的数组
}
$site_info["current_assay_value"] = convert_assay_value( $site_info["current_assayvalue"], 'checked="checked"' );  
foreach ($site_info["current_assay_value"] as $key=>$value)
{
	$glxm.="<li style=\"width:20%;float: left;list-style-type:none;text-align: left;\">$value</li>";
}
$glxmsum=count($site_info["current_assay_value"]);

####################该站点尚未关联的化验项目
$XM = $DB->query( "SELECT aj.vid AS vid FROM `assay_jcbz` AS aj JOIN `n_set` AS n ON aj.jcbz_bh_id=n.id JOIN `assay_value` AS av ON aj.vid=av.vid WHERE n.module_value2= '$site_info[water_type]' AND n.module_value3='1' AND av.act='1' AND av.fzx_id='".$fzx_id."' AND n.fzx_id='".$fzx_id."'" );
$all_assay_value=array();
while( $xmm = $DB->fetch_assoc( $XM )){
	$all_assay_value[]=$xmm['vid'];
}
//print_rr($all_assay_value);
//$all_assay_value = get_all_assay_value();
$site_info['opt_assay_value'] = array_diff( $all_assay_value, $site_info['current_assayvalue'] );
$site_info["opt_assay_value"] = convert_assay_value( $site_info["opt_assay_value"] );
foreach ($site_info["opt_assay_value"] as $key=>$value)
{
	$wglxm.="<li style=\"width:20%;float: left;list-style-type:none;text-align: left;\">$value</li>";
}
$wglxmsum=count($site_info["opt_assay_value"]);
*/
disp(xm_muban);
?>
