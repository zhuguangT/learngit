<?php
/**
 * 功能：修改站点
 * 作者：zhangdengsheng
 * 日期：2014-08-08
 * 描述：修改站点的信息及关联项目
*/
include '../temp/config.php';
$fzx_id			= FZX_ID;//中心
require_once "$rootdir/inc/site_func.php";
###################添加水源限制
if($_GET['syxz']){
	$onsy = $DB->fetch_one_assoc("select * from n_set where module_name='syxz' and module_value1='".$_GET['syxz']."'");
	if(!$onsy['id']){
		$DB->query("insert into n_set set fzx_id='1',module_name='syxz',module_value1='".$_GET['syxz']."'");
	}
}
###################导航
$trade_global['daohang'][] =  array('icon'=>'','html'=>'查看修改站点','href'=>'site/site_info.php?site_id='.$_GET['site_id'].'&group_name='.$_GET['group_name'].'&action=xdjdrw&fzx_id='.$_GET['fzx_id']);
$_SESSION['daohang']['site_info'] = $trade_global['daohang'];
###############################验证站点
$sid = 0;
if ( isset( $_GET['site_id'] ) && (int)$_GET['site_id'] != 0 )
    $sid = (int)$_GET['site_id'];
if ( !$sid )
    error_show( '非法站点编号' );
###################查看地图传过来的，更新经纬度
if($_GET['jingdu'] && $_GET['weidu'] ){
	$jingdu=$_GET['jingdu'];
	$weidu=$_GET['weidu'];
	$DB->query("UPDATE `sites` SET `jingdu` = '$jingdu',`weidu` = '$weidu' WHERE `sites`.`id` ='$sid';");
}

###################得到站点信息
$av_flag = false; //是否显示该站点化验项目*/
if ( $_GET['group_name'] ) {//是否得到批次，得到就是进行了分批的
    $av_flag = true;
    $sql = "
        SELECT s.*,sg.site_type AS site_type,group_name,sg.assay_values,sg.id AS sgid FROM site_group AS sg LEFT JOIN sites AS s
        ON sg.site_id = s.id
        WHERE sg.site_id = $sid AND act='1' AND sg.group_name='{$_GET['group_name']}'";
}else{
	if($_GET['site_type']=='0'){//没得到批次，判断是否为监督任务
		$sq = $DB->query("SELECT * FROM site_group WHERE site_id = $sid AND act='1' AND site_type ='0' AND `fzx_id`=$fzx_id AND group_name=''");
		$sq_num=$DB->num_rows($sq);
		if($sq_num>0){
			 $sql = "SELECT s.*,group_name,sg.assay_values,sg.id AS sgid FROM site_group AS sg LEFT JOIN sites AS s ON sg.site_id = s.id WHERE sg.site_id = $sid AND act='1' AND sg.group_name=''AND sg.site_type ='0' AND sg.fzx_id=$fzx_id";
		}else{$sql = "SELECT * FROM sites WHERE id = '$sid' ";}
	}else{//没得到批次也不是监督任务，那就是为分批站点
    $sql = "SELECT * FROM sites WHERE id = '$sid' ";}
}
$site_info = $DB->fetch_one_assoc( $sql );
###################获取水源限制下拉列表
$syxz = '';
$sysql = $DB->query("select * from n_set where module_name='syxz'");
while($sy = $DB->fetch_assoc($sysql)){
	if($sy){
		$syxzs = ','.$site_info['syxz'].',';
		$pd = '';
		$pd = strstr($syxzs,','.$sy['module_value1'].',');
		if($pd){
			$syxz .="<input type='checkbox' name='syxz[]' value='{$sy['module_value1']}' checked>{$sy['module_value1']}&nbsp;&nbsp;"; 
		}else{
			$syxz .="<input type='checkbox' name='syxz[]' value='{$sy['module_value1']}'>{$sy['module_value1']}&nbsp;&nbsp;";
		}
	}
}
if($u['userid']=='admin'){
	$cc = "<img src=\"$rooturl/img/tianjia.jpg\" name=\"tianjia\" width=\"51px\" height=\"24px\" class=\"bianse\" id='bianshe' onclick='tjsy()'/>";
}
if($_GET['site_type']=='0'){
	$site_in =$DB->fetch_one_assoc("SELECT * FROM `n_set` WHERE id = '{$site_info['group_name']}'");
	$site_info['group_name']= $site_in['module_value1'];
}

if ( !$site_info ){
    error_show('该站点不存在');
}
##################转换显示经纬度
$site_info['weidu']=todfm($site_info['weidu']);
$site_info['jingdu']=todfm($site_info['jingdu']);
//print_rr($site_info);
##################得到任务类型
$rwlx=$global['site_type'];
foreach ($rwlx as $key=>$value)
{
	if( $key  == $_GET['site_type']){
		$site_type = $value;
	}
}
//$leix=get_syleibie($site_info['water_type']);//获取水样类型
$leix=get_syleixing($site_info['water_type'],$bs='123');//获取水样类型
$le=$DB->fetch_one_assoc("select lname from `leixing` where id='$site_info[water_type]'");
$leix2=$le[lname];
##################获取分中心
if($_GET['site_type']=='0'){
	$sql_fenzx = $DB->query("SELECT id,hub_name FROM `hub_info` ORDER BY `id` ASC");
	while($zx  = $DB->fetch_assoc($sql_fenzx))
	{
		if($zx['id']==$site_info['fp_id']){
			$fenzx.="<option value='$zx[id]' selected=\"selected\">$zx[hub_name]</option>";
		}else{
			$fenzx.="<option value='$zx[id]'>$zx[hub_name]</option>";
		}
	}
	$fzx='分中心:';
	$fzx2="<select name='fenz'>$fenzx</select>";
}else{$fzx2="<input type=\"hidden\" name=\"fenz\" value=\"$site_info[fp_id]\" />";}
#######################统计参数
$tjcs = $tjcs2 = "";
if($site_info['tjcs']){
$site_tjcs=$site_info['tjcs'];
$site_tjcs=Trim($site_tjcs,',');
}
$bglxArr	   = explode(',',$site_tjcs);//站点的所属统计参数
$queryTjlx     = $DB->query("select id,module_value1 from `n_set` where `fzx_id`=$fzx_id AND `module_name`='tjcs' AND `module_value3`='1'");
while($rsTjlx  = $DB->fetch_assoc($queryTjlx)){
	$tjcslx[]  = $rsTjlx['id'];//各中心自己的统计参数
}
if($site_tjcs != ''||count($tjcslx)!='0'){
if($site_tjcs == ''&&count($tjcslx)!='0'){$tjcsx=implode(',',$tjcslx);}//总中心没分配统计参数时
if($site_tjcs != ''&&count($tjcslx)=='0'){$tjcsx=$site_tjcs;}//各中心没有自己的统计参数
if($site_tjcs != ''&&count($tjcslx)!='0'){$tjcsx=$site_tjcs.','.implode(',',$tjcslx);}
$tjlxArr =array_unique(explode(',',$tjcsx));//各中心自己的统计参数和站点的所属统计参数
sort($tjlxArr);
foreach ($tjlxArr as $key=>$value)
{
	$tj= $DB->fetch_one_assoc("select id,fzx_id,module_value1 from `n_set` where id='$value'");
	if(in_array($tj['id'],$bglxArr)){
		if($fzx_id!=$tj['fzx_id']){//总中心分配的站点不可操作
			$tjcs .= "<label id='".$tj['id']."'><input type=\"checkbox\" name=\"tjcs_name[]\" checked value=\"".$tj['id']."\" disabled=\"disabled\"/>".$tj['module_value1']."<input type=\"hidden\" name=\"tjcs_name[]\" value=\"".$tj['id']."\"></label>&nbsp;&nbsp;";
			}else{
			$tjcs .= "<label id='".$tj['id']."'><input type=\"checkbox\" name=\"tjcs_name[]\" checked value=\"".$tj['id']."\" />".$tj['module_value1']."</label>&nbsp;&nbsp;";
			}
		}else{
			if($fzx_id!=$tj['fzx_id']){//总中心分配的站点不可操作
				$tjcs .= "<label id='".$tj['id']."'><input type=\"checkbox\" name=\"tjcs_name[]\" value=\"".$tj['id']."\" disabled=\"disabled\"/>".$tj['module_value1']."</label>&nbsp;&nbsp";
			}else{
			$tjcs .= "<label id='".$tj['id']."'><input type=\"checkbox\" name=\"tjcs_name[]\" value=\"".$tj['id']."\" />".$tj['module_value1']."</label>&nbsp;&nbsp";
			}
		}
		if($fzx_id==$tj['fzx_id']){//各中心自己的统计参数可操作
			$tjcs2 .= "<span><input type=\"text\" onkeyup=\"value=value.replace(/[(\ )(\~)(\`)(\!)(\@)(\#)(\$)(\￥)(\%)(\^)(\&)(\……)(\*)(\()(\))(\-)(\——)(\_)(\+)(\=)(\[)(\])(\{)(\})(\|)(\\)(\;)(\；)(\：)(\:)(\')(\‘)(\“)(\”)(\,)(\，)(\.)(\。)(\/)(\<)(\>)(\《)(\》)(\?)(\？)(\、)(\)]+/g,'')\" onbeforepaste=\"clipboardData.setData('text',clipboardData.getData('text').replace(/[(\ )(\~)(\`)(\!)(\@)(\#)(\$)(\￥)(\%)(\^)(\&)(\……)(\*)(\()(\))(\-)(\——)(\_)(\+)(\=)(\[)(\])(\{)(\})(\|)(\\)(\;)(\；)(\：)(\:)(\')(\‘)(\“)(\”)(\,)(\，)(\.)(\。)(\/)(\<)(\>)(\《)(\》)(\?)(\？)(\、)(\)]+/g,''))\" lxid='".$tj['id']."' huifu='".$tj['module_value1']."' value=\"".$tj['module_value1']."\" onblur=\"uplx(this)\" /></span>&nbsp;&nbsp;";//<img src=\"../images/shanchu.png\" name=\"shanchu\" class=\"bianse\" /></span>";
		}
}}
##################目前站点所属的组
$current_group = get_site_group( $sid,$_GET['site_type'],$fzx_id );
$site_info['current_group'] = convert_site_group( $current_group, 'checked="checked"' );
$sspc1 = '手工输入一个批名: <input type="text" class="inputl" name="custom_group_name" />';
foreach ($site_info['current_group'] as $key=>$value)
{
	$sspc.="<li style=\"width:25%;float: left;list-style-type:none;text-align: left;\">$value</li>";
}
#####################站点还可以属于下面的组
$all_group = get_groups( $site_info['site_type'], $site_info['water_type'] );
$opt_group = array_diff( $all_group, $current_group );
$site_info['opt_group'] = convert_site_group( $opt_group );
foreach ($site_info['opt_group'] as $key=>$value)
{
	$wspc.="<li style=\"width:20%;float: left;list-style-type:none;text-align: left;\">$value</li>";
}
#####################监督任务去掉批次
if($_GET['site_type']!='0'){
		$pici="<table class=\"table table-striped table-bordered table-hover center\">
		<tbody style=\"width:20cm;\">
		<tr align=\"center\" >
			<td colspan=\"2\"><b>该站点目前属于以下批:</b></td>
		</tr>
		<tr align=\"center\" >
			<td style=\"width:81%;padding-left:50px;\">$sspc</td>
			<td style=\"width:19%;\">$sspc1</td>
		</tr>
		</tbody>
	</table>
	<table class=\"table table-striped table-bordered table-hover center\">
		<tbody >
		<tr>
			<td><b>该站点还可以属于下面这些批:</b></td>
		</tr>
		<tr><td style=\"padding-left:50px;\">
			$wspc</td>
		</tr>
		</tbody>
	</table>";
}
#####################该站点目前关联的化验项目
$site_info['current_assayvalue']	= array();
$site_info['current_assay_value']	= array();
if ( $site_info['assay_values'] ){
	$site_info['current_assayvalue'] = explode( ',',$site_info['assay_values']);//获取关联的数组
}
$site_info["current_assay_value2"] = convert_assay_value( $site_info["current_assayvalue"], 'checked="checked"' );  
foreach ($site_info["current_assay_value2"] as $key=>$value)
{
	$glxm.="<li style=\"width:20%;float: left;list-style-type:none;text-align: left;\">$value</li>";
} 
$site_info["current_assay_value3"] = convert_assay_value2( $site_info["current_assayvalue"], 'checked="checked"' );
foreach ($site_info["current_assay_value3"] as $key=>$value)
{
	$glxm2.="<li style=\"width:20%;float: left;list-style-type:none;text-align: left;\">$value</li>";
}
$glxmsum=count($site_info["current_assay_value2"]);
####################获取模板
 $S = $DB->query( "SELECT * FROM `n_set` WHERE module_name='xmmb' AND fzx_id='$fzx_id' " );
 while( $row = $DB->fetch_assoc( $S ) ) {
	$mbxm.="<option value='$row[module_value1]'>$row[module_value2]</option> ";
 }
####################该站点尚未关联的化验项目
if(isset($_GET['q'])){
	$site_info[water_type]=$_GET['q'];
}
$ssgc	= "SELECT * FROM `leixing` WHERE id='$site_info[water_type]'";
$ssgcc	= $DB->fetch_one_assoc($ssgc);
if($ssgcc[parent_id]!=0){//查询当子水样类型时的项目
	//该水样类型下有方法的 
	$sql = $DB->query("SELECT DISTINCT  xmid FROM `xmfa` inner join `assay_value` as av on xmfa.xmid=av.id where xmfa.fzx_id='$fzx_id' and xmfa.lxid in ($site_info[water_type],$ssgcc[parent_id]) and xmfa.act='1' and xmfa.mr='1' ");
	while($sqll = $DB->fetch_assoc($sql)){
	$sit[] = $sqll[xmid];}
}else{//查询当父水样类型时的全部项目 
	//该水样类型下有方法的
	$sql = $DB->query("SELECT DISTINCT xmid FROM `xmfa` inner join `assay_value` as av on xmfa.xmid=av.id where xmfa.fzx_id='$fzx_id' and xmfa.lxid = $site_info[water_type] and xmfa.act='1' and xmfa.mr='1' ");
	while($sqll =$DB->fetch_assoc($sql)){
	$sit[] = $sqll[xmid];}
}

if(isset($sit)){
$site_info['opt_assay_value'] = array_diff( $sit,$site_info['current_assayvalue'] );//除去已选项目的
$site_info["opt_assay_value"] = convert_assay_value( $site_info["opt_assay_value"] );
foreach ($site_info["opt_assay_value"] as $key=>$value)
{
	$wglxm.="<li style=\"width:20%;float: left;list-style-type:none;text-align: left;\">$value</li>";
}}
$site_zd	= array_diff($sit,$site_info['current_assayvalue']);
$wglxmsum	= count($site_zd);
if($wglxmsum==''){$wglxmsum='0';}

if(isset($_GET['q'])){
$glxm='';
echo ($glxm.'@'.$wglxm.'@'.$wglxmsum);
exit(); }

if(empty($global['firm_type'])){
	$global['firm_type']	= 'zls';
}
disp("site_info_zls");
?>
