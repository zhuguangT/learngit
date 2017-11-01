<?php
//用于手机采样的数据同步页面
//数据同步采用POST 发送过来
//发送过来还有最后一次更新时间 zdate 根据最后更新时间 服务器段判定返回那些需要更新的数据
$checkLogin = false;
include('../temp/config.php');
// error_reporting(E_ALL);
// ini_set("display_errors", 1);
############以下数据同步代码
$update = time();// 返回给浏览器 方便下次上报：zdate
$zdate  = $_POST['zdate'];//如果这个手机第一次访问我们系统，没有zdate 值
if($zdate == ''){
	$striqi = date('Y-m-d',strtotime('-30 day'));
}
//sync是返回给浏览器的数据
$sync   = array();
$fzx_id = $_POST['fzx_id'];
//如果是登陆之前，没有fzx_id。就只更新用户信息
if(empty($fzx_id)){
	$usql = "SELECT `nickname`, `userid`, `password`, `fzx_id` FROM `users` WHERE `cy`='1' AND `group`!='0' AND `group`!='测试组' ORDER BY `userid`";
	$uR = $DB->query($usql);
	while($row = $DB->fetch_assoc($uR)){
		$sync['u:'.$row['nickname']]      = 'p'.$row['password'];
		$sync['fzx_id:'.$row['nickname']] = $row['fzx_id'];//分中心id
		$sync['user:'.$row['nickname']]   = $row['userid'];//采样员姓名
		$sync['ulist']  .= "<option value=$row[nickname]>$row[nickname]</option>";
	}
	echo json_encode($sync);
	exit;
}
//上传数据先保存，后更新
$savelist   = $_POST['cyrec'];
if(!empty($savelist)){
	//如果不是 cy_rec 表中字段 就插入 json 中
	$cykey   = array();
	$rescolumns = $DB->query("SHOW COLUMNS FROM `cy_rec` WHERE `Field` != 'json'");
	while($rec_code = $DB->fetch_assoc($rescolumns)){
		$cykey[] = $rec_code['Field'];
	}
	// 查找对应id的批次是否已经签字，如果已经签字就不允许更改了(包括退回的单子，退回的状态也不应该在手机采样更改)
	$cy_qz_arr = $no_site = $rec_rows = array();
	if(!empty($_POST['rec_id_arr'])){
		$rec_id_str	= implode(',',$_POST['rec_id_arr']);
		$qz_arr	= $DB->query("SELECT `cr`.`id`, `cr`.`site_name`, `cr`.`json`, cy.`status` FROM `cy` INNER JOIN `cy_rec` AS `cr` ON `cy`.`id`=`cr`.`cyd_id` WHERE `cr`.`id` IN($rec_id_str)");
		while($row = $DB->fetch_assoc($qz_arr)){
			if($row['status'] >=3){
				$cy_qz_arr[] = $row['id'];
				$no_site[] = $row['site_name'];
			}
			$rec_rows[$row['id']] = $row;
		}
	}
	foreach($savelist as $carr){
		$jsonstr= '';
		unset($carr['30']);//空数据，本php中定义的
		$cid    = $carr['id'];
		if($cid > 0 && !in_array($cid,$cy_qz_arr)){
			// 查询出当前json字段中已经存储的信息
			$json_arr = empty($rec_rows[$cid]['json']) ? array() : json_decode($rec_rows[$cid]['json'],true);
			foreach($carr as $key=>$value){
				$value  = $value;
				//现场检测项目的数据,更新到assay_order表
				if($key=='xc_value'){
					foreach($value as $xcjc_vid=>$xcjc_vd0){
                        $DB->query("UPDATE `assay_order` SET vd0='$xcjc_vd0' WHERE cid='$cid' AND vid='{$xcjc_vid}'");
                        if(@!in_array($cid,$syok) && $DB->affected_rows()){
                            $syok[] = $cid;//返回更新成功后的cid
                        }
                    }
                    continue;
				}
				if(in_array($key,$cykey)){
					$keyarr[]   = " `$key`='$value'";
				}else{
					$json_arr[$key] = $value;
				}
				// die;
			}
			$sqlkey = implode(',',$keyarr);
			if(count($json_arr)){
				$jsonstr = getJSON($json_arr);
			}
			if($jsonstr != ''){
				$sqlkey .= ",`json`='$jsonstr'";
			}
			$DB->query("UPDATE `cy_rec` SET $sqlkey where id='$cid' ");
			if(@!in_array($cid,$syok) && $DB->affected_rows()){
				$syok[] = $cid;//返回更新成功后的cid
			}
		}
	}
	if(count($syok)){
		$sync['syok']   = $syok;
		$sync['oksum']  = count($syok);
		if(!empty($no_site)){
			$sync['no_site']	= implode('、',$no_site);
		}
	}
	$zdatestr   = date('Y-m-d',$zdate);
}
//保存数据后下载更新  新数据
//首先更新用户 信息如  用户名密码：
$usql   = "SELECT nickname ,userid, password,fzx_id FROM  `users` WHERE `fzx_id`='{$fzx_id}' AND `cy`='1' and `group`!='0' AND `group`!='测试组'";
$uR = $DB->query($usql);
while($row = $DB->fetch_assoc($uR)){
	$sync['u:'.$row['nickname']]    = 'p'.$row['password'];//登陆名
	$sync['fzx_id:'.$row['nickname']]   = $row['fzx_id'];//分中心id
	$sync['user:'.$row['nickname']] = $row['userid'];//采样员姓名
	$sync['ulist']  .= "<option value=$row[nickname]>$row[nickname]</option>";
}
//更新人员任务每次update 不管更新时间是否是刚刚更新 都 是完整的返回
//要从上个月1号开始返回
$xc_vid = array();
$ulastym= date('Y-m-01',strtotime('-1 month'));
$rwsql  = "SELECT id,cy_user,cy_user2,cy_date,xc_exam_value FROM  `cy` WHERE `fzx_id`='$fzx_id' AND  `cy_date` >  '$ulastym' order by `cy_date` desc";
$rwR    = $DB->query($rwsql);
while($row = $DB->fetch_assoc($rwR)){
	if(!empty($row['xc_exam_value'])){
		$xc_vid['vid']  .= ",".$row['xc_exam_value'];
		$xc_vid['cydid'].= ",".$row['id'];
	}
	$cy_date_temp   = explode("-",$row['cy_date']);
	$row['Y']   = $cy_date_temp[0];
	$row['M']   = $cy_date_temp[1];
	$sync['rw'.$row['Y'].$row['M']][$row['cy_user']][]  = $row['id'];
	if($row['cy_user2'] != ''){
		$sync['rw'.$row['Y'].$row['M']][$row['cy_user2']][] = $row['id'];
	}
}
if(!empty($xc_vid['vid'])){
	$xc_vid['vid']  = substr($xc_vid['vid'],1);
	$xc_vid['vid']	= trim($xc_vid['vid'],',');
	$xc_vid['cydid']= substr($xc_vid['cydid'],1);
	$temp_xc_vid    = @explode(",",$xc_vid['vid']);
	$xc_vid['vid']  = implode(",",array_unique($temp_xc_vid));
}
//然后更新 cy ,cy_rec  以及 sites 中的 坐标信息
$csql   = "select cy_rec.*,cy.note as cynote,cy.cy_date as yqcydate, cy.group_name ,sites.jingdu as s_jd,sites.weidu as s_wd,sites.banjing  from cy inner join cy_rec on cy.id=cy_rec.cyd_id left join sites on sites.id=cy_rec.sid where cy.fzx_id='$fzx_id' and cy.`cy_date` >'$ulastym'  ORDER BY cy.`cy_date` desc, `cy_rec`.`id` ASC  LIMIT 800";//`cy_rec`.`modify_time` >'$ulastym' and `cy_rec`.`create_date`>'$ulastym'
$cR = $DB->query($csql);
while($row = $DB->fetch_assoc($cR)){
	// $sql = "SELECT `vid` , `vd0` FROM `assay_order` WHERE `cid` = '{$row['id']}'";
	// $re = $DB->query($sql);
	// while($data = $DB->fetch_assoc($re)){
	// 	$row[$data['vid']] = $data['vd0'];
	// }
	unset($row['assay_values']);
	unset($row['bar_code_position']);
	$jsarr  = array();
	if($row['json'] != ''){
		$jsarr  = json_decode($row['json'],true);//把json格式转成数组格式
	}
	unset($row['json']);
	$row    += $jsarr;
	$water_type_sql = "SELECT * FROM `leixing` WHERE `id` = '{$row['water_type']}'";
	$re = $DB->query($water_type_sql);
	$data = $DB->fetch_assoc($re);
	if($data['parent_id'] != '0'){
		$row['water_type'] = $data['parent_id'];
	}
	$sync['cy:'.$row['cyd_id']][$row['id']] = $row;
	//用于方便客户端开发  目前限制每个站点最多上传30个数据
	$sync['sync:'.$row['id']]   = array(30=>'','id'=>$row['id'],'xc_value'=>array());
}
#############//更新现场采样记录表表头信息（离线存储到手机上）
if(!empty($global['cy_record_bt'])){
	foreach ($global['cy_record_bt'] as $water_type_key => $value_arr) {
		//翻转键名和键值
		$sync['cy_record_bt'][substr($water_type_key , 0 , 1)]	= str_ireplace(array("<br>","<br />","<br/>"),'', array_flip($value_arr));
	}
	if(is_array($global['cy_record_bt_order'])){
		foreach ($global['cy_record_bt_order'] as $value) {
			if(is_array($global[$value])){
				$sync['cy_record_bt_content'][$value]	= $global[$value];
			}
		}
	}
}
##############//然后更新 现场检测项目的信息
if(!empty($xc_vid['vid'])){
	$sql_xc_value   = $DB->query("SELECT ao.cyd_id,ao.cid,ao.vid,ao.vd0,ap.assay_element,ap.unit FROM `assay_order` AS ao RIGHT JOIN `assay_pay` AS ap ON ao.tid=ap.id WHERE ap.is_xcjc='1' AND ao.cyd_id in ({$xc_vid['cydid']})   AND ao.vid in ({$xc_vid['vid']})");//
	while($rs_xc_value=$DB->fetch_assoc($sql_xc_value)){
		if(empty($rs_xc_value['vd0']) && $rs_xc_value['vd0']!='0'){
			$rs_xc_value['vd0'] = '';
		}
		$sync['cy:'.$rs_xc_value['cyd_id']][$rs_xc_value['cid']]['xc_value'][$rs_xc_value['vid']]['name']   = $rs_xc_value['assay_element']."(".$rs_xc_value['unit'].")";
		$sync['cy:'.$rs_xc_value['cyd_id']][$rs_xc_value['cid']]['xc_value'][$rs_xc_value['vid']]['vd0']    = $rs_xc_value['vd0'];
		$sync['sync:'.$rs_xc_value['cid']]['xc_value'][$rs_xc_value['vid']]	= $rs_xc_value['vd0'];
	}
}
// print_rr($sync);
$sync['zdate']  = $update;
//js 是 用于eval 执行的js代码 用于后期 升级或者调试
$sync['js'] = '';
//返回当前月的标志如：201211 代表2012年11月，  手机默认可以看到2个月任务分别是当前月和上一个月
$sync['dqym']   = date('Ym');
$sync['lastdate']=date('m月d号 H:i');
$sync['lastym'] = date('Ym',strtotime('-1 month'));
echo json_encode($sync);


// 传入数组返回 json数据
function getJSON($array) {
		arrayRecursive($array, 'urlencode', true);
		$json = json_encode($array);
		return urldecode($json);
}

/*   
function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
	static $recursive_counter = 0;
	if (++$recursive_counter > 1000) {
		die('possible deep recursion attack');
	}
	if($array=='')
	$array=array();
	foreach ((array)$array as $key => $value) {
		if (is_array($value)) {
			arrayRecursive($array[$key], $function, $apply_to_keys_also);
		} else {
			$array[$key] = $function($value);
		}
  
		if ($apply_to_keys_also && is_string($key)) {
			$new_key = $function($key);
			if ($new_key != $key) {
				$array[$new_key] = $array[$key];
				unset($array[$key]);
			}
		}
	}
	$recursive_counter--;
}*/
?>
