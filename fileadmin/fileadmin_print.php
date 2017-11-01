<?php
/*
**作者: 李岩
**时间：2016/6/3
**用途：文件管理页面的主界面
*/
include '../temp/config.php';
error_reporting(0);//警告错误压制
if($_GET['handle']=='download'){
	 header("Content-type: application/octet-stream;charset=gbk");
     header("Accept-Ranges: bytes");
     header("Content-Disposition: attachment; filename=文件管理.xls");
}else{
	echo "<script>window.print();window.close();</script>";
}
$fzx_id=$_SESSION['u']['fzx_id'];
//导航
$trade_global['daohang'][]	= array('icon'=>'','html'=>'文件管理','href'=>$rooturl.'/fileadmin/fileadmin.php');
$_SESSION['daohang']['fileadmin']       = $trade_global['daohang'];

$ys_sql="select * from filemanage where fzx_id='$fzx_id' and pid='0' order by pid desc , xu";
$ys_query = $DB->query($ys_sql);
while($ys_sel=$DB->fetch_assoc($ys_query)){
		$pid_arr[]=$ys_sel['id'];
		$ys_arr[]=$ys_sel;
}

if($pid_arr){
	$pid=implode(',',$pid_arr);
	// print_rr($pid_arr);
		$juan_sql = "SELECT * FROM `filemanage` WHERE `fzx_id` = '$fzx_id' AND `jid` = '0' AND `pid` != '0' AND `pid` IN ($pid) ORDER BY `pid` , `xu` ASC";
		$re = $DB->query($juan_sql);
		while($data = $DB->fetch_assoc($re)){
			$juan_arr[$key][]=$data;
			if($pid != $data['pid']){
				$jid[] = $data['id'];
			}
			$pid = $data['pid'];
		}
		// print_rr($jid);
	foreach($jid as $key=>$value){
		$j_id .=$value.',';
	}
	$j_id = substr($j_id , 0 , -1);
	// echo $j_id;
$lb_sql = "SELECT * FROM `filemanage` WHERE `fzx_id` = '$fzx_id' AND `jid` !='0' ORDER BY `jid`,`pid`, `xu` ASC";
// echo $lb_sql;
$re = $DB->query($lb_sql);
while($data = $DB->fetch_assoc($re)){
	$lb_arr[] = $data;
}
$files = array();
	$lines='';
	foreach($ys_arr as $ys_key=>$ys_value){
		foreach($juan_arr as $juan_key => $juan_value){
			foreach($juan_value as $k=>$v){
				if($ys_value['id'] == $v['pid']){
					$ys_value['juan'][$k] = $v;
					$m=0;
					foreach($lb_arr as $lb_key => $lb_value){
						// echo $ys_key;
						if($lb_value['jid'] == $v['id']){
							$ys_value['juan'][$k]['lb'][] = $lb_value;
							if($jid != $lb_value['jid']){
									$m++;
							}
						}
						$jid = $lb_value['jid'];
					}
				}
			}
			// $ys_value[$ys_key]['juan'][] = $juan_value;
		}
		$files[] = $ys_value;
	}
}

//查询最大序号
$max_sql="select max(xu) max from filemanage";
$max_query=$DB->query($max_sql);
$max_sel=$DB->fetch_assoc($max_query);
// print_rr($files);
$i = 0; //rowspan=$juan_row rowspan=$lb_row
foreach($files as $file_key => $file){
	$num1 = count($file['juan']);
	$num2 = count($file['juan'][$file_key]['lb']);
	$juan_row=$num1+$num2+1;//获取被要素下有多少卷
	if($juan_row !=''){
		$lines .= "<tr>
				<td class='lei_class'>
					<span style='cursor:pointer'  title='点击修改类型名' onclick='pro(this,{$file['id']})'>{$file['namebak']}</span>
					<a class='green glyphicon glyphicon-zoom-in' title='添加卷' href='#' onclick='ins_juan({$file['id']})'></a>
				</td>
				
				";
		foreach($file['juan'] as $juan_key=> $juan){//循环本要素下的卷
			$lb_row = count($juan['lb'])+1;
			// echo $lb_row;
			$lines .="<tr>
						<td title='点击修改卷名' class='juan_class' name_data='{$file['namebak']}' rowspan=$lb_row><span style='cursor:pointer' onclick='up_juan(this,{$juan['id']})'>{$juan['name']}</span>
							<a class='green glyphicon glyphicon-zoom-in' title='添加类别' href='#' onclick='ins_lei({$juan['id']},{$juan['pid']})'></a>
						 </td>

					 ";
			if(!empty($juan['lb'])){
				foreach($juan['lb'] as $key => $value){
								$lines.="
								</tr><tr>
									 <td class='lb_class' juan_data='{$juan['name']}' title='点击修改类别名' onclick='up_lei(this,$value[id])' style='cursor:pointer'>$value[name]</td>
									 <td style='cursor:pointer'  title='点击修改备注' onclick='up_state(this,$value[id])' >$value[note]</td>
									 <td>
										<a href='show.php?id=$value[id]&name=$value[name]' class='green icon-edit bigger-130' title='查看{$value[name]}类的文件'></a>
										<a href='#' class='red icon-remove bigger-140' title='删除' onclick='del($value[id],$value[pid])' ></a>
										<!-- <a href='#' class='icon-cloud-upload  blue bigger-130' title='上传文件' onClick='javascript:upload($value[id]);'></a> -->
									 </td></tr>";
							}
			}
			// else{
			// 	$lines .="</tr><tr><td></td><td></td><td></td></tr>";
			// }
					

		}
	}else{//如果要素下没有类
		foreach($juan['lb'] as $key => $value){
			echo 123;
				$lines.="<tr>
					<td rowspan=$juan_row><span style='cursor:pointer'  title='点击修改要素名' onclick='pro(this,{$file['id']})'>{$file['namebak']}</span><a class='green glyphicon glyphicon-zoom-in' title='添加卷' href='#' onclick='ins_juan({$file['id']})'></a></td>
					<td rowspan=$cos><span style='cursor:pointer' onclick='up_juan(this,$file[id])'>$value[name]</span>
						<a class='green glyphicon glyphicon-zoom-in' title='添加类别' href='#' onclick='ins_lei($value[id])'></a>
					 </td>
					 <td colspan=3></td>
					 ";
			}
		}
		$lines .= "</tr></tr>";
}

disp('fileadmin/fileadmin_print');
?>
