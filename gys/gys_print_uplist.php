<?php
include "../temp/config.php";
$fzx_id	= $u['fzx_id'];
$id=$_GET['id'];
$gid=$_GET['gid'];
$sql = "SELECT * FROM `gys_gl` WHERE `id` = '$id'";
$arr	= $DB->fetch_one_array($sql);
//联系人
	$lianxiren_arr=json_decode($arr['lianxi'],true);
	$lianxi_num=2;
		foreach($lianxiren_arr as $k=>$v){
		$lianxiren.=<<<EOF
		<tr align='center' style='width:2cm;height:1cm' name='lianxiren'>
		<td>联系人$lianxi_num</td>
		<td align='left'><input type='text' class=inputc name='lxr2[]'  size='30' value='$v[lxr]'/></td>
		<td>联系方式</td>
		<td align='left'><input type='text' class=inputc name='lxdh2[]'  size='30' value='$v[lxdh]' /></td>
		</tr>
EOF;
$lianxi_num++;
	}	

	//年度评价
	$pingjia_arr=json_decode($arr['year_pingjia'],true);
	foreach($pingjia_arr as $k=> $v){
		$pingjia.=<<<EOF
		<tr align='center' style='width:2cm;height:1cm' name='year_pingjia'><td><input type='text' class=inputc name='niandu[]' value='$v[niandu]'></td><td><input type='text' class=inputc name='zonghe_pingjia[]' value='$v[zonghe_pingjia]'></td><td><input type='text' class=inputc name='pingjia_ren[]' value='$v[pingjia_ren]'></td><td><input type='text'  name='pingjia_time[]' value='$v[pingjia_time]'></td></tr>
EOF;
	}
disp('gys_print_uplist.html');