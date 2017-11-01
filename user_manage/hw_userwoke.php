<?php
include "../temp/config.php";
$fzx_id=$_SESSION['u']['fzx_id'];

/*
 * 处理ajax 动态控制datalist
 */
 /*
if($_GET['m'] === 'datalist'){
	$item = $_GET['item'];
	$ajax_sql = "select `id`,`str2` from `n_set` where `fzx_id`=".$fzx_id." and `name`='worke' and `str3`!='' and (`int1` is null or `int1`=0) and str1='$item' order by str3";
	$ajax_res = $DB->query($ajax_sql);
	$option = '';
	while($ajax_row = $DB->fetch_assoc($ajax_res)){
		$option .= "<option>{$ajax_row['str2']}</option>";
	}
	echo json_encode($option);
	die;
}*/
//查看分中心
//表头实验室信息
$hub_names = $all_address = array();
$hub_sql = $DB->query("SELECT * FROM `hub_info` where 1");
while($re2 = $DB->fetch_assoc($hub_sql)){
	$hub_names[$re2['id']] = $re2['hub_name'];
}
$sel = '';
if($_GET['fzx']){
	$fzx = $_GET['fzx'];
}else{
	$fzx = $fzx_id;
}
if($fzx_id==1){
		$sel .= "<br><center><select name='fzx' id='dayin' onchange='cfzx(this)' flag='$type'><option value=''>请选择...</option>";
		foreach($hub_names as $k3=>$v3){
			if($k3 == $fzx){
				$sel .="<option value='$k3' selected>$v3</option>";
			}else{
				$sel .="<option value='$k3'>$v3</option>";
			}
		}
		$sel .="</select></center><br/>";
	$fzx_id = $fzx;
}
$xm =$arr =$ren = array();
$xms = $qx =$rsx ='';
if($_GET['xmm']){
	@$xmm = mysql_escape_string(trim($_GET['xmm']));
	$qx .= "and v.value_C='$xmm'";
}
if($_GET['ffx']){
	@$ffx = mysql_escape_string(trim($_GET['ffx']));
	$qx .= "and method_number like '%$ffx%'";
}

//$sql = "select `id`,`str1`,`str2`,`str3`,`str4`,`str5` from `n_set` where `fzx_id`=".$fzx_id." and `name`='worke' and `str3`!='' and (`int1` is null or `int1`=0) $qx order by str3";
$sql="SELECT u.userid,u.id uid, method_number, v.value_C,x.id as fid
FROM users u
LEFT JOIN xmfa x ON ( u.id = x.`userid`
OR u.id = x.userid2 )
LEFT JOIN assay_method m ON x.fangfa = m.id
LEFT JOIN assay_value v ON x.xmid = v.id
where u.fzx_id='$fzx_id' and u.group!='0' AND u.group!='测试组' and v.value_C !='' $qx  
GROUP BY u.userid, x.xmid
";
$rs = $DB->query($sql);
if(!empty($qx)){
	//#########导航
	$daohang = array(
			array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
			array('icon'=>'','html'=>'检索的上岗项目统计','href'=>'user_manage/hw_userwoke.php?xmm='.$xmm.'&ffx='.$ffx),
	);
	$trade_global['daohang'] = $daohang;

	$num = 1;
	while($row = $DB->fetch_assoc($rs)){
		/*if($row['str5']!='' && $row['str4']!=''){
                $strx = '+'.$row['str4'].' days';
                $c1 =  strtotime($strx);
                $date = date("Y-m-d",$c1);
                $c2 = strtotime($date);
                $today0 = time();
                $today1 = date("Y-m-d",$today0);
                $today2 = strtotime($today);
                //下次检定的日期
                $time2 = strtotime($row['str5']);
                if($c2 >= $time2 && $today2 <= $time2){
                        $color='red';
                }else{
                        $color='';
                }
        }else{
                        $color='';
                }*/
        $zheng = $DB->fetch_one_assoc("select * from users_zheng where userid='".$row['userid']."' and fid = '".$row['fid']."'");
		$line_search .= "<tr style='color:$color'>";
		$line_search .= "<td>$num</td>";
		$line_search .= "<td>{$row['userid']}</td>";
		$line_search .= "<td>{$row['value_C']}</td>";
		$line_search .= "<td>{$row['method_number']}</td>";
		$line_search .= "<td>{$zheng['limit_date']}</td>";
		//$line_search .= "<td><nobr><a href='hw_userwork_action.php?zid=$row[id]'>修改</a>|<a href='hw_userwork_action.php?id=$row[id]&user=$row[str3]'>删除</a></nobr></td>";
		$line_search .= "</tr>";
		$num++;
	}
	$item = $xmm;
	disp('user_manager/hw_userwoke_search');
	die;
}else{

	//#########导航
	$daohang = array(
			array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
			array('icon'=>'','html'=>'上岗项目统计','href'=>'user_manage/hw_userwoke.php'),
	);
	$trade_global['daohang'] = $daohang;

	//将查询的数据放到$rrs数组中
	while($r = $DB->fetch_assoc($rs)){
		$rrs[]=$r;
	}
	foreach($rrs as $key =>$val)
	{
		if(!in_array($val['value_C'],$xm)){
			$xms .= "<option value='$val[value_C]'>$val[value_C]</option>";
			$xm[] = $val['value_c'];//项目
		}
		if(!in_array($val['method_number'],$xm)){
			$ffs .= "<option value=\"$val[method_number]\">$val[method_number]</option>";
			$ff[] = $val['method_number'];//方法
		}
		if(!in_array($val['userid'],$ren)){
			$ren[$val['uid']] = $val['userid'];
		}
		$value_C[$val['uid']][]=$val['value_C'];
	}
	$rsx = count($ren);
	$i=0;
	foreach($ren as $key =>$value){
		$i++;
		if(!empty($_GET['xmm']) || !empty($_GET['ffx'])){
			$total = $DB->fetch_one_assoc("select count(distinct(`str1`)) as total from `n_set` where `fzx_id`=".$fzx_id." and `name`='worke' and `str3`='$key' and (`int1` is null or `int1`=0)");
			$xcont = $total['total'];
		}else{
			$xcont = count($value_C[$key]);
		}
		$lines.=temp('user_manager/hw_userwoke_line');
	}
	//最开始没有数据时 提供新增按钮
	if(empty($lines))
		$new = "<input type='button' value='新增' style=\"background:none repeat scroll 0 0 #AACCFF\" onclick = 'location.href=\"hw_useradd.php?add=1\"'>";

	disp('user_manager/hw_userwoke');
}
?>
