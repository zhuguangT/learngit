<?php
/*
 *功能：化验单关联的图谱，进行上传
 *作者：汤永胜
 *时间：2016-05-26
 */
/*error_reporting(E_ALL);
ini_set('display_errors', '1'); */
header("Content-Type:text/html;charset=utf-8"); 
include "../temp/config.php";
$ip=$_SERVER['REMOTE_ADDR']; //获取本机电脑的IP
global $fzx_id;
if($_POST['submit']){
 	//化验单号
	$tid=$_POST['hid'];
	if(!is_array($_FILES['upfile']['name'])){
		foreach($_FILES['upfile'] as $key => $value){
			$_FILES['upfile'][$key]	= array();
			$_FILES['upfile'][$key]['0']	= $value;
		}
	}
	//查询化验单的对应的打印机
	//$sql="3479";
	$fid_data = $DB->fetch_one_assoc("SELECT id,userid,userid2,fid FROM `assay_pay` WHERE id='".$tid."'");
	//die($fid_data['fid']);
	$fid_yiqi = $DB->fetch_one_assoc("SELECT yiqi FROM `xmfa` WHERE id='".$fid_data['fid']."'");
	//die("SELECT yiqi FROM `xmfa` WHERE id='".$fid_data['fid']."'");
	$fid_storeroom = $DB->fetch_one_assoc("SELECT `storeroom_id` FROM `yq_autoload_set` WHERE yq_id='".$fid_yiqi['yiqi']."' AND fzx_id='".$fzx_id."'");
	$fid_printer = $DB->fetch_one_assoc("SELECT `printer` FROM `yq_autoload_storeroom` WHERE id='".$fid_storeroom['storeroom_id']."' ");
	$arr_file=$_FILES['upfile']['name'];
	$arr_false=$arr_true=$arr_size=array();
	foreach($arr_file as $key=>$value)
	{//遍历文件得到键名键值,value为文件名称
		$pdf=explode('.',$value);
		$name_suffix= substr($value,0,strrpos($value,"."));
		if($pdf[1]=='pdf')
		{
			if($_FILES['upfile']['size'][0]<1024*1024*3)
			{
				$xxx	= explode('.',$_FILES['upfile']['name'][$key]);
				$cnt	= count($xxx);
				$newname= date(Ymdhis).$i++."_".$fid_printer['printer'].".".$xxx[$cnt-1]; 
				//判断文件夹是否存在,不存在就建立
				if(!is_dir("/home/files/".date(Ym).""))mkdir("/home/files/".date(Ym)."",0777);
				$path	= "/home/files/".date(Ym)."/".$newname;
				//201605/20160519161618_PDF1.pdf
				$files="".date(Ym)."/".$newname."";
				//将文件移入指定文件夹,并插入到数据库
				if(move_uploaded_file($_FILES['upfile']['tmp_name'][$key],$path))
				{
					$sql="INSERT INTO `pdf` (`file`, `cdate`, `ip`,`print_name`) VALUES ('".$files."',date('Y-m-d H:i:s'),INET_ATON('".$ip."'),'".$fid_printer['printer']."')";
					$sql_read="SELECT * FROM `pdf` WHERE `file`='".$files."'";
					if($DB->query($sql)){
						//自动关联化验单
						$query = $DB->query($sql_read);
						while ($rs = $DB->fetch_assoc($query))
						{
							$insert="INSERT INTO `hydpdf` (`tid` ,`pid`)VALUES ('".$tid."','".$rs['id']."')";
							$DB->query($insert);
						}
						$arr_true[]=$value;
					}
				}
			}
			else
			{
				$arr_size[]=$value;
			}
		}
		else
		{
			$arr_false[]=$value;
		}
	}
	if(count($arr_true)||count($arr_false) ||count($arr_size))
	{
		echo "<br />";
		for($i=0;$i<count($arr_true);$i++)
		{
			echo "<center><font color='red'>您的文件--".$arr_true[$i]."--上传成功！请您继续您的操作！</font></center>" ;
			echo '<br />';
		}
		echo "<br />";
		for($j=0;$j<count($arr_false);$j++)
		{
			echo "<center><font color='red'>您的上传的--".$arr_false[$j]."--文件不是PDF文件，请您重新上传PDF文件！</font></center>" ;
			echo '<br />';
		}
		echo "<br />";
		for($r=0;$r<count($arr_size);$r++)
		{
			echo "<center><font color='red'>您的上传的--".$arr_size[$r]."--文件超过3M，请您重新上传小于3M文件</font><center>" ;
			echo '<br />';
		}
		reto('hydpdf.php?ajax=1&tid='.$tid,1);
	}
}
function reto($path,$time)
{
	$t = $time * 3600;
	echo "<script type='text/javascript'>
	      setTimeout(\"location.href = '".$path."'\",".$t.");
	      </script>";
}
?>