<?php
//地图上显示站点 
//2012-06-29  lisongsen
include "../temp/config.php";
$fzx_id	= $_SESSION['u']['fzx_id'];
if(!empty($_GET['fzx'])){
	$fzx_id	= $_GET['fzx'];
}
//地图页面用https:// 是访问不了的。但为了安全着想，只是将这个页面暂时处理为 http:// 的访问方式,其他地方不变
if($_SERVER["HTTPS"]=="on"){
	$xredir	 = "http://".$_SERVER["SERVER_NAME"]. $_SERVER["REQUEST_URI"];
	header("Location: ".$xredir);
}
$rooturl = str_replace('https','http',$rooturl);
//得到年份下拉菜单
$td	 = date('Y');
$month	 = date('m');

$ylist	.= "<option label=$td checked value='$td'>$td</option>";
for($i=($td-1);$i>=$begin_year;$i--){
	$ylist	.= "<option label=$i value='$i'>$i</option>";
}
//月份下拉菜单
if(!empty($_GET['m'])){
	$month	= $_GET['m'];
}else{
	$month   = date('m');
}
for($i=1;$i<13;$i++){
	//默认当前月
	$selected	= '';
	if($i==$month){
		$selected	= 'selected';
	}
	$mlist	.= "<option label=$i value='$i' $selected>$i</option>";
}
//得到 站点类型 下拉菜单  在temp/definition.php  中配置 
$site_options	 = "<option   value=''>全部</option>";
if(!empty($global['site_type'])){
	foreach($global['site_type'] as $key=>$value){
		$site_options	.= "<option   value='$key'>$value</option>";
	}
}
//获取本中心的经纬度
$jingdu	= $weidu	= '';
if($fzx_id	== '全部'){
	$where_fzx_id	= $_SESSION['u']['fzx_id'];
}else{
	$where_fzx_id	= $fzx_id;
}
$hub	= $DB->fetch_one_assoc("SELECT * FROM `hub_info` WHERE `id`='{$where_fzx_id}'");
if(!empty($hub['jingdu']) && !empty($hub['weidu'])){
	$jingdu	= trans_jwd($hub['jingdu']);
	$weidu	= trans_jwd($hub['weidu']);
}else{
	//默认首都的经纬度
	$jingdu	= '116.405419';
	$weidu	= '39.918698';
}
//分中心列表
$fzx_list	= '';
if($hub['is_zz']=='1'){
	$fzx_list	.= "分中心列表<select id='fzx' name='fzx' onchange=\"getsite()\"><option value='全部'>全部</option>"; 
	$hub_list_sql	= $DB->query("SELECT * FROM `hub_info` WHERE 1");
	$hub_num	= $DB->num_rows($hub_list_sql);
	while($hub_list_rs = $DB->fetch_assoc($hub_list_sql)){
		if($_GET['fzx'] == $hub_list_rs['id'] || (empty($_GET['fzx']) && $fzx_id == $hub_list_rs['id'])){
			$fzx_list	.= "<option value='{$hub_list_rs['id']}' selected>{$hub_list_rs['hub_name']}</option>";
		}else{
			$fzx_list	.= "<option value='{$hub_list_rs['id']}'>{$hub_list_rs['hub_name']}</option>";
		}
	}
	$fzx_list	.= "</select>";
	if($hub_num<=1){
    		$fzx_list	= '';
    	}
}
echo temp('sitemap');
function trans_jwd($jwd){
	if(preg_match("/[\x{4e00}-\x{9fa5}]+/u",$jwd)){
        $jd_xiugaihou	= preg_replace("/[\x{4e00}-\x{9fa5}]+/u",'-',$jwd);
        $jd_arr	= explode('-',$jd_xiugaihou);
        $jwd	= $jd_arr[0] + ($jd_arr[1]/60) + ($jd_arr[2]/3600);
        return $jwd;
	}elseif(preg_match("/[º|′|″|°|\'|\"]/", $jwd)){
		$jd_xiugaihou	= preg_replace("/[º|′|″|°|\'|\"]/",'-',$jwd);
        $jd_arr	= explode('-',$jd_xiugaihou);
        $jwd	= $jd_arr[0] + ($jd_arr[2]/60) + ($jd_arr[3] ? $jd_arr[3]/3600 : $jd_arr[5]/3600);
        return $jwd;
	}elseif(preg_match("/[度|分]/", $jwd)){
		$jd_xiugaihou	= preg_replace("/[度|分|\.]/",'-',$jwd);
        $jd_arr	= explode('-',$jd_xiugaihou);
        $jwd	= $jd_arr[0] + ($jd_arr[2]/60) + ($jd_arr[3] ? $jd_arr[3]/3600 : $jd_arr[5]/3600);
        return $jwd;
	}else{
		return $jwd;
	}
}
?>



