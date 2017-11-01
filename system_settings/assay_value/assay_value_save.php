<?php
/**
 * 功能：化验项目保存页面（包括ajax修改 和 单独页面的修改和添加）
 * 作者：韩枫
 * 日期：2014-03-21
 * 描述：fzx_id 分中心Id
*/
include("../../temp/config.php");
$fzx_id  = "1";
#################assay_value_list.php 化验项目列表页面ajax修改
$jsonArr = array("jieguo"=>"失败");
$win     = '';
if(!empty($_GET['panduan'])){//化验项目列表里 ajax传过来要修改的值
	if(!empty($_GET['vid'])&&!empty($_GET['modifyName'])){
		$sqlUpdateValue = $DB->query("update `assay_value` set `".$_GET['modifyName']."`='".$_GET['modifyValue']."' where `id`='".$_GET['vid']."'");
		$win = $DB->affected_rows();
		if($win=='1'){
			//修改成功 后 这里加个判断，如果是 检出限，化验员，修约位数，修改时 顺带修改方法表
			if(in_array($_GET['modifyName'],array("jcx","userid","w1","w2","w3","w4","w5"))){
				$sqlUpdateXmfa = $DB->query("update `xmfa` set `".$_GET['modifyName']."`='".$_GET['modifyValue']."' where (`".$_GET['modifyName']."`='' or `".$_GET['modifyName']."` is null or `".$_GET['modifyName']."`='".$_GET['oldValue']."') and xmid='".$_GET['vid']."' and `fzx_id`='".$fzx_id."'");
			}
			elseif($_GET['modifyName']=='value_C'){//如果修改项目名 同时修改 标准表里相同的项目名
				$sqlUpdateJcbz = $DB->query("update `assay_jcbz` set `".$_GET['modifyName']."`='".$_GET['modifyValue']."' where (`".$_GET['modifyName']."`='' or `".$_GET['modifyName']."` is null or `".$_GET['modifyName']."`='".$_GET['oldValue']."') and vid='".$_GET['vid']."' and `fzx_id`='".$fzx_id."'");
			}
			$jieguo = '成功';
		}else{
			$jieguo    = "未进行修改";//.$sql;
		}
	}
	if(!empty($jieguo))$jsonArr['jieguo']=$jieguo;
	echo json_encode($jsonArr);
	exit;
}

#######################assay_value_modify.php 修改项目页面的提交修改
if(!empty($_POST['panduan'])&&$_POST['panduan']=='modify'){
	if(!empty($_POST['vid'])&&!empty($_POST['value_C'])&&$_POST['jcx']!=''){
					//获取出assay_value表修改前的数据，以供后面更新 方发表和标准表时 对比所用
					$rsValue        = $DB->fetch_one_assoc("select * from `assay_value` where id='".$_POST['vid']."'");
					//更新 assay_value表的对应信息
                        $sqlUpdateValue = $DB->query("update `assay_value` set `act`='".$_POST['act']."',`userid`='".$_POST['userid']."',`userid2`='".$_POST['userid2']."',`jcx`='".$_POST['jcx']."',`w1`='".$_POST['w1']."',`w2`='".$_POST['w2']."',`w3`='".$_POST['w3']."',`w4`='".$_POST['w4']."',`w5`='".$_POST['w5']."',`englishMark`='".$_POST['englishMark']."' where id='".$_POST['vid']."'");
                        $win = $DB->affected_rows();
                        if($win>=1){
							//##############项目表更新成功后，同时修改 方法表  和 标准表的信息
							/*if($rsValue['value_C']!=$_POST['value_C']){
								$DB->query("update `assay_jcbz` set `value_C`='".$_POST['value_C']."' where vid='".$_POST['vid']."' and (`value_C`='' or `value_C` is null or `value_C`='".$rsValue['value_C']."') and `fzx_id`='".$fzx_id."'");
							}*/
							if($rsValue['userid']!=$_POST['userid']){
								$DB->query("update `xmfa` set `userid`='".$_POST['userid']."' where xmid='".$_POST['vid']."' and (`userid`='' or `userid` is null or `userid`='".$rsValue['userid']."') and `fzx_id`='".$fzx_id."'");
							}
							if($rsValue['userid2']!=$_POST['userid2']){
								$DB->query("update `xmfa` set `userid2`='".$_POST['userid2']."' where xmid='".$_POST['vid']."' and (`userid2`='' or `userid2` is null or `userid2`='".$rsValue['userid2']."') and `fzx_id`='".$fzx_id."'");
							}
							if($rsValue['jcx']!=$_POST['jcx']){
								$DB->query("update `xmfa` set `jcx`='".$_POST['jcx']."' where xmid='".$_POST['vid']."' and (`jcx`='' or `jcx` is null or `jcx`='".$rsValue['jcx']."') and `fzx_id`='".$fzx_id."'");
                            }
							if($rsValue['w1']!=$_POST['w1']){
								$DB->query("update `xmfa` set `w1`='".$_POST['w1']."' where xmid='".$_POST['vid']."' and (`w1`='' or `w1` is null or `w1`='".$rsValue['w1']."') and `fzx_id`='".$fzx_id."'");
                            }
							if($rsValue['w2']!=$_POST['w2']){
								$DB->query("update `xmfa` set `w2`='".$_POST['w2']."' where xmid='".$_POST['vid']."' and (`w2`='' or `w2` is null or `w2`='".$rsValue['w2']."') and `fzx_id`='".$fzx_id."'");
							}
							if($rsValue['w3']!=$_POST['w3']){
								$DB->query("update `xmfa` set `w3`='".$_POST['w3']."' where xmid='".$_POST['vid']."' and (`w3`='' or `w3` is null or `w3`='".$rsValue['w3']."') and `fzx_id`='".$fzx_id."'");
							}
							if($rsValue['w4']!=$_POST['w4']){
								$DB->query("update `xmfa` set `w4`='".$_POST['w4']."' where xmid='".$_POST['vid']."' and (`w4`='' or `w4` is null or `w4`='".$rsValue['w4']."') and `fzx_id`='".$fzx_id."'");
							}
							if($rsValue['w5']!=$_POST['w5']){
								$DB->query("update `xmfa` set `w5`='".$_POST['w5']."' where xmid='".$_POST['vid']."' and (`w5`='' or `w5` is null or `w5`='".$rsValue['w5']."') and `fzx_id`='".$fzx_id."'");
							}
				//############同步修改 方发表 和标准表 完成
							echo "<script>alert('修改成功');location.href='assay_value_modify.php?vid=$_POST[vid]'</script>";
							exit;
						}
                
    }
	echo "<script>alert('修改失败');location.href='assay_value_modify.php?vid=$_POST[vid]'</script>";
}
?>
