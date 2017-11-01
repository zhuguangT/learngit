<?php
include '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];
$searchx = $_POST[search];
$ss = trim($searchx);
$search="select * from filemanage where fzx_id='$fzx_id' and name like ('%$ss%') or file like('%$ss%')";
$rs = $DB->query($search);
$str='';
if(!mysql_num_rows($rs))
echo "<script>alert('未搜到您输入的数据');location.href='fileadmin.php'</script>";
while($res = $DB->fetch_assoc($rs)){
	if($res[pid]<=21){
		//print_r($res);
		$s2="select * from filemanage where fzx_id='$fzx_id' and id=$res[pid]";
		$rs2 = $DB->query($s2);
		$res2 = $DB->fetch_assoc($rs2);
		$xxx = explode($ss,$res[name],2);
		$yyy = explode($ss,$res[file],2);
		$str.= "<tr align=center><td>$res2[name]</td>";
		if(strpos($res[name],$ss)===false){
			$str .= "<td>$res[name]</td>";
		}else{
			$str.= "<td><a href=show.php?id=$res2[id]>$xxx[0]<font style=color:red>$ss</font>$xxx[1]</a></td>";
		}
			if(strpos($res[file],$ss)===false){
				$str .= "<td>$res[file]</td>";
		}else{
				$str.= "<td><a href='upfile/$res[file]'>$yyy[0]<font style=color:red>$ss</font>$yyy[1]</a></td>";

		}
		$str .= "<td>-----</td><td>-----</td></tr>";
	}
	if($res[pid]>21){
		$s3="select * from filemanage where fzx_id='$fzx_id' and id=$res[pid]";
		$rs3 = $DB->query($s3);
		if($res3 = $DB->fetch_assoc($rs3)){
			$s4="select * from filemanage where fzx_id='$fzx_id' and id=$res3[pid]";
			$rs4 = $DB->query($s4);
			$res4 = $DB->fetch_assoc($rs4);
			$xxx = explode($ss,$res[name],2);
			$yyy = explode($ss,$res[file],2);
			$str.="<tr align=center><td>$res4[name]</td><td>$res3[name]</td><td>-----</td>";
			if(strpos($res[name],$ss)===false){
				$str.="<td>$res[name]</td>";
			}else{
				$str.="<td><a href=show.php?id=$res3[id]>$xxx[0]<font style=color:red>$ss</font>$xxx[1]</a></td>";
			}
			if(strpos($res[file],$ss)===false){
				$str.="<td>$res[file]</td>";

			}else{
				$str.="<td><a href=upfile/$res[file]>$yyy[0]<font style=color:red>$ss</font>$yyy[1]</a></td>";
			}
		}
	}
}
disp('fileadmin/file_search');
?>
