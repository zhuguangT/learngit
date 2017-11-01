<?php

      //详细仪器的代码
     include "../temp/config.php";
//导航
$daohang= array(
        array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
        array('icon'=>'','html'=>'仪器管理','href'=>"$rooturl/yiqi/hn_yiqimanager.php"),
        array('icon'=>'','html'=>'仪器管理目录','href'=>"$rooturl/yiqi/hn_yiqiguanli.php?id={$_GET['id']}&action={$_GET['action']}")
);
$trade_global['daohang']= $daohang;
     $id = ($_GET['id'])?$_GET['id']:$_POST['id'];

     if($_POST['add']){
     	if(empty($_POST['bdname'])){
     		echo "<script>alert('表单名称不能为空');history.go(-1)</script>";
     		die;
     	}
		$path ='';
		if(!empty($_FILES['file'])){
			if($_FILES[file][size]<100000000){	
				$xxx = explode('.',$_FILES[file][name]);
				$cnt = count($xxx);
				$old_name= $_FILES[file]['name'];
				$newname = date('Ymdhis').".".$xxx[$cnt-1]; 
				$path 	 = "$rootdir/yiqi/upfile/".$newname;
					if(!move_uploaded_file($_FILES[file][tmp_name],$path)){
						$newname = '';
					}
					
			}
		}
		$addsql = "insert into yiqijd(yid,jdt1,jdt2,jdt3,jdt4) values($_POST[id],'$_POST[bdname]','$newname','$_POST[beizhu]','$old_name')";
		$addsqls = $DB->query($addsql);
		echo "<script>location.href='hn_yiqiguanli.php?id='+$id+'&action=show'</script>";
	 }
	 if($_GET['action']=='del'){
		 $sqldel = "delete from yiqijd where id = $_GET[id]";
		 $del_file = $DB->fetch_one_assoc('SELECT `jdt2` FROM `yiqijd` where id = '.$_GET['id'].' limit 1');
		 if($DB->query($sqldel)){
		 	$file = 'upfile/'.$del_file['jdt2'];
		 	if(file_exists($file))
		 		@unlink($file);
			echo "<script>location.href='hn_yiqiguanli.php?id='+$_GET[pid]+'&action=show'</script>";
		}
	 }
	 
	 if($_POST['fix']){
		 $newname = $_POST['file2'];
		if($_FILES){
			$xxx = explode('.',$_FILES[file][name]);
			$cnt = count($xxx);
			$newname =date('Ymdhis').".".$xxx[$cnt-1]; 
			$path = "upfile/".$newname;
			
			if(!move_uploaded_file($_FILES[file][tmp_name],$path)){
				$newname = $_POST['file2'];
			}else{
				$file = 'upfile/'.$_POST['file2'];
				if(file_exists($file))
		 			@unlink($file);
			}
		}
		$sqlfix = "update yiqijd set yid='$_POST[pid]',jdt1='$_POST[bdname]',jdt2='$newname',jdt3='$_POST[beizhu]' where id='$_POST[id]'";
		if($sqlfixs = $DB->query($sqlfix)){
			echo '<div style="text-align:center;margin-top:200px;">修改成功,1秒后返回</div>',"<script>setTimeout(\"location.href='hn_yiqiguanli.php?id='+$_POST[pid]+'&action=show'\",1000)</script>";
			die;
		}else{
			echo '<div style="text-align:center;margin-top:200px;">数据未修改或修改失败,2秒后返回</div>',"<script>setTimeout(\"location.href='hn_yiqiguanli.php?id='+$_POST[pid]+'&action=show'\",2000)</script>";
			die;
		}
	 }
	 $lines = '';
	 $i = 1;
	 if($_GET['action']=='show'){
	  $sql="select * from yiqijd where yid=$id";
		 $rs = $DB->query($sql);
		 while($r = $DB->fetch_assoc($rs)){ 
			$jdt2	= '<span>下载|</span>';
			if(!empty($r['jdt2'])){
				$jdt2	= "<a class='blue icon-download-alt bigger-130' href='upfile/$r[jdt2]' title='下载'></a> | ";
			}
		 	$lines .= '
		 		<tr align=center>
		 			<td>'.$i.'</td><td>'.$r['jdt1']."</td>
		 			<td><a href='upfile/$r[jdt2]' >$r[jdt4]</a></td>
		 			<td>".$r['jdt3'].'</td>
		 			<td>'.$jdt2."<a class='green icon-edit bigger-130' href='yiqijb.php?action=fix&id=$r[id]&bd=$r[jdt1]&bz=$r[jdt3]&file=$r[jdt2]&pid=$r[yid]' title='修改'></a> | <a class='red icon-remove bigger-130' href='#' onClick='del($r[id],\"$r[jdt1]\",\"$r[yid]\")' title='删除'></a>
		 		</td></tr>";
		 $i++;
	 }
	}
     disp('hn_yiqiguanli.html');  
?>
