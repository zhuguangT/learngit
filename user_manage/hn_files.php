<?php
require '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];
//获取正确的上级url，放到导航栏中
$url    = "$rooturl/user_manage/hn_usermanager.php";
foreach ($_SESSION['url_stack'] as $key => $value) {
	if(stristr($value,'hn_usermanager.php?') && !stristr($value,'&print')){
		$url	= $value;
		break;
	}
}
//#########导航
$daohang = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'人员档案管理','href'=>$url),
	array('icon'=>'','html'=>$_GET['r'].'档案管理目录','href'=>'user_manage/hn_files.php?r='.$_GET['r'].'&uid='.$_GET['uid']),
);
$trade_global['daohang'] = $daohang;
$username = $_GET['r'];
$uid = $_GET['uid'];
//$sql = "select `id`,`name`,`str5`,`str4`,`int2`,`int3` from n_set where fzx_id='$fzx_id' and `int2`='$uid' order by id";
$sql="SELECT `id`,`fzx_id`,`name`,`file_name`,`src`,`remark`,`u_id` FROM user_files WHERE u_id=$uid ORDER BY id";
$res = $DB->query($sql);
$lines = '';
$i = 1;
$display	= '';
while($row = $DB->fetch_assoc($res)){
	$lines .= '<tr align=center>';
	$lines .= '<td>'.$i.'</td>';
	$lines .= '<td>'.$row['name'].'</td>';
	$lines .= "<td><a href='./upload/{$row['src']}' target='_bank'>".$row['file_name'].'</a></td>';
	$lines .= '<td>'.$row['remark'].'</td>';
	if($fzx_id == $row['fzx_id']){
		$lines .= "<td><a class='green icon-edit bigger-130' title='修改' href=hn_files_mod.php?all=$row[id]-$uid-$username&m=file></a>";
		$lines .= '|<a class="red icon-remove bigger-140" title="删除" href="javascript:s_confirm('."'hn_files_del.php?all=$row[id]-$uid-$row[src]'".')"></a></td>';
	}else{
		$display	= ' style="display:none;" ';
	}
	$lines .= '</tr>';
	$i++;
}
if(empty($lines)){
	$user_fzx	= $DB->fetch_one_assoc("SELECT `fzx_id` FROM `users` WHERE `id`='{$uid}'");
	if($fzx_id != $user_fzx['fzx_id']){
		$display	= ' style="display:none;" ';
	}
}
disp('user_manager/hn_files');
?>
