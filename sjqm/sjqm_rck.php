<?php
//2012-8-21 add by lixiaojun
//功能 试剂器皿管理  入库出库
include "../temp/config.php";
$fl = array('试剂'=>0,'药品'=>3,'器皿'=>1,'杂物'=>2,'全部'=>'0,1,2,3');
$f = array_flip($fl);
if($_POST['chuku_self']){
	// print_rr($_POST);die;
	//查看需要领取物品的数量
	$count=count($_POST['shuliang']);
	$id='';
	for($i=0;$i<$count;$i++){
		$id.=$_POST['id'][$i].',';
	}
	$id=substr($id,0,-1);
		$sql = "SELECT * FROM `sjqm` WHERE id in ($id)";
		$res=$DB->query($sql);
		$i=0;
		$id='';
		$shuliang='';
		$w=count($_POST['id'])-1;
		while($data=$DB->fetch_assoc($res)){
			$id.=$data['id'].',';
			$shuliang.=$_POST['shuliang'][$w].',';
			// echo $shuliang;echo '<hr>'.$data['kucun'].'<hr>';
			if($_POST['shuliang'][$w]>$data['kucun']){
				echo 123;die;
			echo "<script>alert('出库数据有误');location.href='$rooturl/main.php';</script>";
			die;
			}
			$time =time() ;
			$w--;
		}
			$id=substr($id,0,-1);
			$shuliang=substr($shuliang,0,-1);
			$user=$_POST['user'];
			$sql="SELECT * FROM `sjqm` WHERE id IN ($id)";
			$name='';
			$re=$DB->query($sql);
			while($data=$DB->fetch_assoc($re)){
				$name.=$data['name'].',';
			}
			$name=substr($name,0,-1);
			$sql="INSERT INTO `sjqm_sq` (`sq_id` , `sq_name` , `sq_num` ,`lq_user` , `sq_date`) VALUES ('$id' , '$name' , '$shuliang' , '$user' , '$time')";
			$re=$DB->query($sql);
			echo "<script>alert('申请成功');location.href='$rooturl/main.php';</script>";
		die;
}
if($_GET['action']=='queren'){
	$time=time();
	$sql="SELECT *,ss.id as sid FROM `sjqm_sq` AS ss LEFT JOIN `sjqm` AS s ON ss.sq_id = s.id WHERE sq_date<$time AND sq_status=0 ORDER BY `sq_date`";
	$re=$DB->query($sql);
	$line='';
	$id='';
	$shuliang='';
	$user='';
	$pro_id='';
	$i=1;
	while($data=$DB->fetch_assoc($re)){
		$kucun='';
		$date=date('Y-m-d',$data['sq_date']);
		$id.=$data['sq_id'].'-';
		$shuliang.=$data['sq_num'].',';
		$user.=$data['lq_user'].',';
		$pro_id.=$data['id'].',';
		$sid.=$data['sid'].',';
		$sql="SELECT * FROM sjqm WHERE id IN ($data[sq_id])";
		$res=$DB->query($sql);
		while($v=$DB->fetch_assoc($res)){
			$kucun.=$v['kucun'].',';
		}
		$line.="<tr align=center><td>$i</td><td>$data[sq_name]</td><td>$kucun</td><td>$data[guige]</td><td>$data[jibie]</td><td>$data[pihao]</td><td>$data[sq_num]</td><td>$data[lq_user]</td><td>$date</td><td><a href='sjqm_rck.php?action=get_out&id=$data[sq_id]&num=$data[sq_num]&user=$data[lq_user]&pro_id=$data[id]&sid=$data[sid]&kucun=$kucun'>确认出库</a>||<a href='sjqm_rck.php?action=del&key=$data[sid]'>删除</a></td></tr>";
		$i++;
	}
	$id=substr($id,0,-1);
	$shuliang=substr($shuliang,0,-1);
	$user=substr($user,0,-1);
	$pro_id=substr($pro_id,0,-1);
	$pro_id=substr($sid,0,-1);
	$line.="<input type='hidden' value='$id' name='id'>";
	$line.="<input type='hidden' value='$shuliang' name='shuliang'>";
	$line.="<input type='hidden' value='$user' name='user'>";
	$line.="<input type='hidden' value='$pro_id' name='pro_id'>";	
	disp('sjqm_sq_list.html');
	die;
}
if($_GET['action']=='get_out'){
	$id=$_GET['id'];
	$shuliang=$_GET['num'];
	$user=$_GET['user'];
	$pro_id=$_GET['pro_id'];
	$sid=$_GET['sid'];
	$kucun=$_GET['kucun'];
	$time=time();
	if(strpos($id,',')){
		$arr_id=explode(",",$id);
		$arr_shuliang=explode(",",$shuliang);
		$arr_kucun=explode(",",$kucun);
		foreach($arr_id as $key=>$value){
			if($arr_kucun[$key]!=0){
				$sql1 = "UPDATE  `sjqm` SET `kucun` =  `kucun`- ".intval($arr_shuliang[$key])." WHERE id in ($value)";
				$DB->query($sql1);
				$sql2 = "SELECT `kucun` FROM `sjqm` WHERE `id` in ('$value')";
				$rs = $DB->query($sql2);
				$r = $DB->fetch_assoc($rs);
				$sql3 = "INSERT INTO `sjqm_ls` (`sj_id`, `type`, `time`, `shuliang`,  `jiecun`, `user`) VALUES('$value', 'c', '$time', '$arr_shuliang[$key]', '$r[kucun]', '$user')";
				$DB->query($sql3);
				$sql="UPDATE `sjqm_sq` SET `sq_status` = 1 WHERE id = '$sid'";
		 	   $res=$DB->query($sql);
			}	
	 	}
	}else{
		$sql1 = "UPDATE  `sjqm` SET `kucun` =  `kucun`- ".intval($shuliang)." WHERE id in ('$id')";
		$DB->query($sql1);
		$sql2 = "SELECT `kucun` FROM `sjqm` WHERE `id` in ('$id') ";
		$r=$DB->fetch_one_assoc($sql2);
		$sql3 = "INSERT INTO `sjqm_ls` (`sj_id`, `type`, `time`, `shuliang`,  `jiecun`, `user`) VALUES('$id', 'c', '$time', '$shuliang', '$r[kucun]', '$user')";
		$DB->query($sql3);
		$sql="UPDATE `sjqm_sq` SET `sq_status` = 1 WHERE id = '$sid'";
		$DB->query($sql);
	}
	
		echo "<script>alert('出库成功');history.back(-1);</script>";
	die;
}
if($_GET['action']=='del'){
	$sql="DELETE FROM `sjqm_sq` WHERE id=$_GET[key]";
	$re=$DB->query($sql);
	echo "<script>alert('删除成功');history.back(-1);</script>";
	die;
}


$id		= ($_GET['id'])?$_GET['id']:$_POST['id'];
$sqlsee	= "SELECT * FROM `sjqm` WHERE id=$id";
$rsee	= $DB->query($sqlsee);
$r		= $DB->fetch_assoc($rsee);
//导航
$daohang_name	= '入库';
switch($_GET['action']){
case 'ru':$daohang_name       = $r['name']."入库";break;
case 'chu':$daohang_name       = $r['name']."出库";break;
}
$daohang= array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'库房管理','href'=>"$rooturl/sjqm/sjqm_list.php"),
	array('icon'=>'','html'=>$daohang_name,'href'=>"$rooturl/sjqm/sjqm.php?action=入库&id={$id}")
);
//$trade_global['js'] = array('bootbox.min.js');
$trade_global['daohang']= $daohang;

$_POST['shuliang']	= intval($_POST['shuliang']);
$_POST['zhaiyao']	= trim($_POST['zhaiyao']);
$_POST['time']		= trim($_POST['time']);
if($_POST['ruku']){
	if($_POST['shuliang']==0){
		echo "<script>alert('入库数据有误,请填写大于0的整数！');location.href='sjqm_list.php'</script>";
		die;
	}
	$_POST['kucun']	= $_POST['shuliang'];
	$time	= empty($_POST['time']) ? time() : strtotime($_POST['time'].date(' H:i:s'));
	//先定义一个变量判断下是否有修改
	if($_POST['insert'] != ''){
		//如果有修改就获取原来的信息，进行对比
		$sjqm_old	= $DB->fetch_one_assoc("SELECT * FROM `sjqm` WHERE `id`='{$_POST['id']}'");
		$_POST['insert']	= substr($_POST['insert'],3);
		$insert_arr	= explode("|#|",$_POST['insert']);
		$insert_sql	= 'INSERT INTO `sjqm` SET ';
		$if_insert	= '';
		foreach ($sjqm_old as $key => $value) {
			if($key == 'id'){
				continue;
			}
			if(!empty($_POST[$key]) && $_POST[$key]!=$value){
				$value		= $_POST[$key];
				$if_insert	= 'yes';//确认是需要新插入一条记录的
			}
			$insert_sql	.= "`$key`='$value',";
		}
		$insert_sql	= substr($insert_sql,0,-1);
		//对比完如果有不一样的点，就重新插入一条记录（原信息中有的新的信息中没有的也要插入）。并获取新的id
		if($if_insert == 'yes'){
			$DB->query($insert_sql);
			$_POST['id']	= $DB->insert_id();
		}else{
			//然后插入入库信息（用下面已有的代码）
			$sql1	= "UPDATE  `sjqm` SET `kucun` =  `kucun`+ ".$_POST['shuliang']." WHERE id=$_POST[id]";
			$DB->query($sql1);
		}
	}else{
		//然后插入入库信息（用下面已有的代码）
		$sql1	= "UPDATE  `sjqm` SET `kucun` =  `kucun`+ ".$_POST['shuliang']." WHERE id=$_POST[id]";
		$DB->query($sql1);
	}
	$sql2	= "SELECT * FROM `sjqm` WHERE `id` = '$id'";
	$r		= $DB->fetch_one_assoc($sql2);
	$sql3	= "INSERT INTO `sjqm_ls` (`sj_id`, `type`, `time`, `shuliang`, `zhaiyao`, `jiecun`, `user`) VALUES('$_POST[id]', 'r', '$time', '{$_POST['shuliang']}', '{$_POST['zhaiyao']}', '{$r['kucun']}', '{$_POST['user']}')";
	$type = $r['type'];
	if($DB->query($sql3)){
		echo "<script>alert('入库完成');location.href='sjqm_list.php?type=$type'</script>";
		die;
	}else {
		echo "<script>alert('入库失败');location.href='sjqm_list.php?type=$type'</script>";
		die;
	}
}else if($_POST['chuku']){
	if($_POST['shuliang']>$r['kucun']){
			echo "<script>alert('你填写的出库数量".$_POST['shuliang'].$r['danwei']."大于现有库存".$r['kucun'].$r['danwei']."!!!');location.href='sjqm_list.php'</script>";
			die;
	}
	$time = ($_POST['time']=='') ? time() : strtotime($_POST['time'].date(' H:i:s'));
	$sql1 = "UPDATE  `sjqm` SET `kucun` =  `kucun`- ".$_POST['shuliang']." WHERE id=$_POST[id]";
	$DB->query($sql1);
	$sql2 = "SELECT * FROM `sjqm` WHERE `id` = '$id'";
	$r = $DB->fetch_one_assoc($sql2);
	$sql3 = "INSERT INTO `sjqm_ls` (`sj_id`, `type`, `time`, `shuliang`, `zhaiyao`, `jiecun`, `user`) VALUES('$_POST[id]', 'c', '$time', '{$_POST['shuliang']}', '{$_POST['zhaiyao']}', '{$r['kucun']}', '{$_POST['user']}')";
	$type = $r['type'];
	if($DB->query($sql3)){
		echo "<script>alert('出库完成');location.href='sjqm_list.php?&type=$type'</script>";
			die;
	}else {
		echo "<script>alert('出库失败');location.href='sjqm_list.php?name=".$r['name']."&type=$type'</script>";
		die;
	}
}
$date	= date('Y-m-d');
$hidid	= "<input type=hidden name=id value=$_GET[id]>";
switch($_GET['action']){
	case 'ru':
		$title	= "入库";
		$title2	= '入库人';
		$sub	= "<input type='submit' onclick='return ck()' name='ruku' id='ruku' value='提交'>";
		disp("sjqm_rck_ru");
		break;
	case 'chu':
		$title	= "出库";
		$title2	= '领用人';
		$sub	= "<input type='submit' onclick='return ck()' name='chuku' id='chuku' value='提交'>";
		$ck_kucun ='$("#sl").blur(function(){
		var sl = parseInt($("#sl").val());
		var ssl = parseInt(document.getElementById("ssl").value);
		if(sl>ssl){
			alert("出库数量 "+sl+"'.$r['danwei'].' 不能超过现有库存 "+ssl+"'.$r['danwei'].' !!!");
			this.value="";
			return false;
		}
	});';
	disp("sjqm_rck_chu");
		break;
}
?>
