<?php
include '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];

//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>$_GET['name'].'类文件管理','href'=>$rooturl.'/fileadmin/show.php?id='.$_GET['id'].'&name='.$_GET['name']);
$_SESSION['daohang']['show']= $trade_global['daohang'];
$trade_global['js']			= array('date-time/bootstrap-datepicker.min.js','date-time/bootstrap-timepicker.min.js','boxy.js');
$trade_global['css']		= array('lims/main.css','datepicker.css','bootstrap-timepicker.css','lims/buttons.css','boxy.css');
if($_GET['handle']=='down_load'){
	 header("Content-type: application/octet-stream;charset=gbk");
     header("Accept-Ranges: bytes");
     header("Content-Disposition: attachment; filename=$_GET[name].xls");
}
//取出该类的类型
$leixing_arr	= $DB->fetch_one_assoc("SELECT `namebak` FROM `filemanage` WHERE `id`=(SELECT `pid` FROM `filemanage` WHERE `id`='$_GET[id]')");
$leixing	= $leixing_arr['namebak'];
$getroot="select * from `filemanage` where fzx_id='$fzx_id' and pid=$_GET[id] ORDER BY fb_date";
$rs = $DB->query($getroot);
$i=1;
$strline	= '';
while($v = $DB->fetch_assoc($rs)){
	$file_link	= '';
	if($v['file']!='' && $v['file']!='null'){
		if(json_decode($v['file'] , true)){
			$file_link_arr = json_decode($v['file'] , true);
			$file_name_arr = json_decode($v['old_file_name'] , true);
			foreach($file_link_arr as $key=>$value){
				$file_link	.= "<a href='$rooturl/fileadmin/upfile/{$value}' target=_blank>$file_name_arr[$key]</a>&nbsp;&nbsp;<a class='red icon-remove bigger-140' title='删除文件' href=adddeal.php?id=$v[id]&pid=$v[pid]&key=$key></a><br>";
			}
		}else{
			$file_link	= "<a href='$rooturl/fileadmin/upfile/{$v[file]}' target=_blank>$v[old_file_name]</a>&nbsp;&nbsp;<a class='red icon-remove bigger-140' title='删除文件' href=adddeal.php?id=$v[id]&pid=$v[pid]></a><br>";
		}
		
		
	}else{
		$file_link = '';
	}
	if($v['fb_date'] == '0000-00-00'){
		$v['fb_date']	= '';
	}
	$strline	.= "<tr align=center id='{$v['id']}'>
						<td>$i</td>
						<td>$leixing</td>
						<td class='can_modify' name='name'>$v[name]</td>
						<td class='can_modify' name='file_num'>$v[file_num]</td>
						<td class='can_modify' name='fb_date'>$v[fb_date]</td>
						<td class='can_modify' name='use_range'>$v[use_range]</td>
						<td class='can_modify' name='namebak'>$v[namebak]</td>
						<td class='can_modify' name='note'>$v[note]</td>
						<td class='noprint' style='white-space:nowrap;'>$file_link</td>
						<td class='noprint'>
							<a href='#' class='red icon-remove bigger-140' title='删除' onClick='javascript:del($v[id],$v[pid]);'></a>
							<a href='#' class='icon-cloud-upload  blue bigger-130' title='修改文件'onClick='javascript:upload($v[id],$v[pid]);'></a>
						</td>
					</tr>";
	$i++;
}
disp('fileadmin/file_show');

?>

