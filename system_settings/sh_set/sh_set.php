<?php
/*
*   化验员 的其它任务设置，如：校核，复核 谁的 化验项目或者曲线
*/

include '../../temp/config.php';
//导航
$trade_global['daohang'] = array(
    array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
    array('icon'=>'','html'=>'审核设置','href'=>'./system_settings/sh_set/sh_set.php')
);
$fzx_id=$u['fzx_id'];
$check_arr=array('校核','复核');
if($_GET['v']==''){
	$_GET['v']='校核';
}

if($_GET['v']=='校核'){
	$lx='v1';
}else{
	$lx='v2';
}
$_GET['id'] = intval($_GET['id'])?intval($_GET['id']):$u['id'];
$user_id = intval($_POST['user_id']) ? intval($_POST['user_id']):$_GET['id'];
$sql = "SELECT `id`,`userid` FROM `users` WHERE `fzx_id`='$fzx_id' AND `group`!='0' ";
$query=$DB->query($sql);
while ($row=$DB->fetch_assoc($query)) {
	$selected = ($user_id==$row['id'])?'selected':'';
	$userlist .= '<option '.$selected.' value="'.$row['id'].'">'.$row['userid'].'</option>';
}


if($user_id){
	$ur=$DB->fetch_one_assoc("SELECT * FROM user_other WHERE uid='".$user_id."'");
	if($ur['uid']<1){//说明这个用户第一次 使用这个功能 这个表里没有他的记录 就新建一个记录
		$DB->query("INSERT INTO `user_other` SET uid='$user_id'");
	}
}
if($_POST['item']){//如果是客户提交信息
	if(count($_POST['value'])){
		$xr= @implode("'',''",array_unique($_POST[value]));
	}else{
		$xr='';
	}
	$usql="UPDATE `user_other` SET `$lx` = '$xr' WHERE `uid` ='$user_id'";
	$DB->query($usql);
}
//从 数据库里 获取 user_other 的数据
$ur=$DB->fetch_one_assoc("SELECT * FROM user_other WHERE uid='$user_id'");
if(strlen($ur[$lx])<1){
	$lxt='0';
}else{
	$lxt=$ur[$lx];
}
$tj=" and userid in('$lxt')";
if(strlen($tj)<1){
	$tj=" and userid  in('0')";
}

$csql="SELECT * FROM `users` WHERE fzx_id='$fzx_id' AND (`group` LIKE '%化验%' OR `group` LIKE '%分析%') "; //得到所有化验员
//echo $csql;
$C=$DB->query($csql);
//构建包含化验员和化验项目的数组
while( $row = $DB->fetch_assoc( $C ) ){
	$sql_value="SELECT av.id,av.value_C FROM assay_value av JOIN xmfa x ON av.id=x.xmid WHERE x.userid='".$row['id']."' OR x.userid2='".$row['id']."' AND x.fzx_id='".$fzx_id."' AND x.act='1'";
	$query_value=mysql_query($sql_value);
	while($rs_value=mysql_fetch_array($query_value)){
		$qb[$row['userid']][$rs_value['id']]=$rs_value['value_C'];
	}
}
$ysql="SELECT * FROM `user_other` WHERE uid ='".$user_id."'"; //得到 已经选择的化验项目
$Y=$DB->fetch_one_assoc($ysql);
if($_GET[v]=="校核"){
	$yiarr=explode("','",$Y['v1']);
}else{
	$yiarr=explode("','",$Y['v2']);
}
$i=0;
foreach($qb as $k=>$v){
	$i++;
	foreach($v as $k1=>$v1){
		$v_arr[]=$k1;
	}
	if(array_intersect($yiarr,$v_arr)){
		$chek.="<tr><td><label><input name=\"names[]\" checked=\"checked\" value=".$i." type=\"checkbox\" id=".$i." onclick='check_all_value(this.value)'>".$k."</label></td><td>";
	}else{
		$chek.="<tr><td><label><input name=names[]  value=".$i." type=checkbox id=".$i." onclick='check_all_value(this.value)'>".$k."</td></label><td>";
	}
	unset($v_arr);
	foreach($v as $key=>$value){
		if(in_array($key,$yiarr)){
			$chek.="<label><input name=\"value[]\" class=".$i." checked='checked' value=".$key." type=checkbox onclick='check_user(this)'>".$value."</label>";
		}else{
			$chek.="<label><input name=\"value[]\" class=".$i."  value=".$key." type=checkbox onclick='check_user(this)'>".$value."</label>";
		}
	}
$chek.="</td></tr>";
}
$check_str="";
foreach($check_arr as $key=>$value){
	if($value==$_GET[v]){
		$check_str.="<option value=".$value." selected='selected'>".$value."</option>";
	}else{
		$check_str.="<option value=".$value.">".$value."</option>";
	}
}
disp('sh_set.html');
?>
