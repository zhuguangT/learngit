<?php
/**
 * 功能：显示，增加，修改，某种样品类型下的某个项目的检验方法的处理页面
 * 作者：tielong zhangdengsheng
 * 日期：2014-03-18
*/
include '../../temp/config.php';
$fzx_id= FZX_ID;//中心
if($_POST['xxxg']=='xg'){
	####################单个修改
	if($_POST[update]=='修改'){
		$sql="UPDATE xmfa SET fangfa='{$_POST['fangfa']}', hyd_bg_id ='{$_POST['upbiao']}',yiqi='{$_POST['upyiqi']}',userid='{$_POST['upuser']}',userid2='{$_POST['upuser2']}',jcx='{$_POST['jcx']}',unit='{$_POST['upunit']}',zzrz='{$_POST['zzrz']}',w1='{$_POST['upw1']}',w2='{$_POST['upw2']}',w3='{$_POST['upw3']}',w4='{$_POST['upw4']}',w5='{$_POST['upw5']}' WHERE id='{$_POST['id']}'  AND  fzx_id=$fzx_id ";
		mysql_query($sql);
	}
	####################当前水样类型下的该项目都修改
	if($_POST[syupdate]=="'{$_POST['lname']}'类型下该项目的所有方法都修改"){
		$sql="UPDATE xmfa SET fangfa='{$_POST['fangfa']}', hyd_bg_id ='{$_POST['upbiao']}',yiqi='{$_POST['upyiqi']}',userid='{$_POST['upuser']}',userid2='{$_POST['upuser2']}',jcx='{$_POST['jcx']}',unit='{$_POST['upunit']}',zzrz='{$_POST['zzrz']}',w1='{$_POST['upw1']}',w2='{$_POST['upw2']}',w3='{$_POST['upw3']}',w4='{$_POST['upw4']}',w5='{$_POST['upw5']}' WHERE id='{$_POST['id']}'  AND  fzx_id=$fzx_id ";
		mysql_query($sql);
		$sqll="UPDATE xmfa SET userid='{$_POST['upuser']}',userid2='{$_POST['upuser2']}',jcx='{$_POST['jcx']}',unit='{$_POST['upunit']}',w1='{$_POST['upw1']}',w2='{$_POST['upw2']}',w3='{$_POST['upw3']}',w4='{$_POST['upw4']}',w5='{$_POST['upw5']}' WHERE lxid='{$_POST['lxid']}' AND xmid='{$_POST['xmid']}' AND fzx_id=$fzx_id";
		mysql_query($sqll);
	}
	###################所有水样类型下都修改
	if($_POST[qbupdate]=='所有水样类型下该项目的所有方法都修改'){
		$sql="UPDATE xmfa SET fangfa='{$_POST['fangfa']}', hyd_bg_id ='{$_POST['upbiao']}',yiqi='{$_POST['upyiqi']}',userid='{$_POST['upuser']}',userid2='{$_POST['upuser2']}',jcx='{$_POST['jcx']}',unit='{$_POST['upunit']}',zzrz='{$_POST['zzrz']}',w1='{$_POST['upw1']}',w2='{$_POST['upw2']}',w3='{$_POST['upw3']}',w4='{$_POST['upw4']}',w5='{$_POST['upw5']}' WHERE id='{$_POST['id']}'  AND  fzx_id=$fzx_id ";
		mysql_query($sql);
		$sqlll="UPDATE xmfa SET userid='{$_POST['upuser']}',userid2='{$_POST['upuser2']}',jcx='{$_POST['jcx']}',unit='{$_POST['upunit']}',w1='{$_POST['upw1']}',w2='{$_POST['upw2']}',w3='{$_POST['upw3']}',w4='{$_POST['upw4']}',w5='{$_POST['upw5']}' WHERE xmid='{$_POST['xmid']}' AND fzx_id=$fzx_id";
		mysql_query($sqlll);
	}
	gotourl("assay_method_edit_xx.php?id=$_POST[id]&value_C=$_POST[value_C]");
}
get_int($_GET['lxid']);
if($_GET['lxid']=="")
gotourl("assay_method_edit.php");
get_int($_GET['xmid']);
if($_GET['xmid']=="")
gotourl("assay_method_edit.php");
get_str($_GET['item']);
get_str($_GET['fafa']);
get_int($_GET['act']);
/*判断要处理的类型*/
switch ($_GET['item'])
{
	case "add"://添加新的方法
	{
		$upsql="UPDATE xmfa SET mr='0' WHERE lxid='$_GET[lxid]' AND  xmid='$_GET[xmid]'  AND  fzx_id=$fzx_id ";//如果默认值不为1，修改该类型下的默认为0
		$DB->query($upsql);
		$sql="INSERT INTO xmfa SET fzx_id=$fzx_id,lxid='$_GET[lxid]',xmid='$_GET[xmid]',yiqi='$_GET[yiqi]',mr='1',act='1',fangfa='$_GET[fafa]',unit='$_GET[unit]',zzrz='$_GET[zzrz]',jcx='$_GET[jcx]',userid='$_GET[user]',userid2='$_GET[user2]',hyd_bg_id='$_GET[bgid]',w1='$_GET[mrw1]',w2='$_GET[mrw2]',w3='$_GET[mrw3]',w4='$_GET[mrw4]',w5='$_GET[mrw5]' ";
		$DB->query($sql);
		$msg='添加成功！';
		break;
	}
	case "upmr"://修改默认方法
	{
		if($_GET[mr]=='1')
		{ 
			break;//如果已经为默认跳出
		}
		if($_GET[mr]=='0') 
		{ 
			if($_GET[act]=='1') 
			{
				if($_GET[fangfa]=='0'){
					$msg="该方法为启用方法，默认前请先设置方法!";
					prompt($msg);
					break;
				}
				if($_GET[user1]=''||$_GET[user1]='0'||$_GET[user1]=NULL){
					$msg="该方法为启用方法，默认前请先设置好好人员1!";
					prompt($msg);
					break;
				}
			} 
		}
		$upsql="UPDATE xmfa SET mr='0' WHERE lxid='$_GET[lxid]' and  xmid='$_GET[xmid]' AND  fzx_id=$fzx_id ";//如果默认值不为1，修改该类型下的默认为0
		$DB->query($upsql);
		$mrsql="UPDATE xmfa SET mr='1' WHERE id='$_GET[fid]'  AND  fzx_id=$fzx_id ";// 把该方法设置为新的默认方法 
		$DB->query($mrsql);
	break;
	}

	case "upact"://修改现在是否使用(弃用)
	{
		if($_GET[act]=='0')
		{
			 if($_GET[mr]=='1')//如果该方法是默认方法验证 
			 {
				if($_GET[fangfa]=='0'){
					$msg="该方法为默认方法，启用前请先设置方法!";
					prompt($msg);
					break;
				}
				if($_GET[user1]=''||$_GET[user1]='0'||$_GET[user1]=NULL){
					$msg="该方法为默认方法，启用前请先设置好好人员1!";
					break;
				}
			}
		}else{
			if($_GET[mr]=='1')//如果该方法是默认方法验证
			{
				$msg="该方法为默认方法，启用前请先默认其他方法!";
					prompt($msg);
					break;
			}
		}	
			if($_GET[act]=='1')
			{
				$act=0;//给是否启用状态赋值 
			}else{
				$act=1;
			}
			$upsql="UPDATE xmfa SET act ='$act' WHERE id='$_GET[fid]' AND  fzx_id=$fzx_id ";//更新状态
			$DB->query($upsql);
			break;
		//}
	}
	case "upfa"://修改现在化验方法
	{
		upsql(fangfa,$_GET[value],$_GET[fid],$fzx_id);break;
	}
    /*case "upbiao"://修改化验单模板表格
    {  
		upsql(hyd_bg_id,$_GET[value],$_GET[fid],$fzx_id);break;
    }*/
	 case "upyiqi"://修改仪器
    {  
		upsql(yiqi,$_GET[value],$_GET[fid],$fzx_id);break;
    }
	case "upuser"://修改化验员
    {  
		upsql(userid,$_GET[value],$_GET[fid],$fzx_id);break;
    }
	case "upuser2"://修改化验员2
    {  
		upsql(userid2,$_GET[value],$_GET[fid],$fzx_id);break;
    }
	/*case "upunit"://修改数据单位
	{
		upsql(unit,$_GET[value],$_GET[fid],$fzx_id);break;
	}
	case "upjcx"://修改检出限
	{
		upsql(jcx,$_GET[value],$_GET[fid],$fzx_id);break;
	}
	case "upw1"://修改默认修约位数结果<1
	{
		upsql(w1,$_GET[value],$_GET[fid],$fzx_id);break;
	}
	case "upw2"://修改默认修约位数1<结果<10
	{
		upsql(w2,$_GET[value],$_GET[fid],$fzx_id);break;
	}
	case "upw3"://修改默认修约位数10<结果<100
	{
		upsql(w3,$_GET[value],$_GET[fid],$fzx_id);break;
	}
	case "upw4"://修改默认修约位数100<结果<1000
	{
		upsql(w4,$_GET[value],$_GET[fid],$fzx_id);break;
	}
	case "upw5"://修改默认修约位数结果>1000
	{
		upsql(w5,$_GET[value],$_GET[fid],$fzx_id);break;
	}
	case "upbeizhu"://修改备注
	{
		upsql(beizhu,$_GET[value],$_GET[fid],$fzx_id);break;
	}*/
}
//更新数据库中的值
function upsql($ziduan,$value,$id,$fzx_id){
	    $sql="UPDATE xmfa SET $ziduan ='$value' WHERE id='$id'  AND  fzx_id=$fzx_id ";
		mysql_query($sql);	
}

gotourl("assay_method_edit.php?xmid=$_GET[xmid]&lxid=$_GET[lxid]&xmname=$_GET[xmname]");

?>
