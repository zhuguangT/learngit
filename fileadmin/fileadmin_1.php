<?php
/*
*文件管理
*/
include '../temp/config.php';
$fzx_id=$_SESSION['u']['fzx_id'];
//导航
$daohang= array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'文件管理','href'=>$rooturl.'/fileadmin/fileadmin.php')
);
$trade_global['daohang']= $daohang;

if($_GET['action']=='alert'){
$sql = "update filemanage set namebak='$_GET[name]' where `fzx_id`='$fzx_id' and id='$_GET[id]' limit 1";
$rs = $DB->query($sql);

}
if($_GET['action']=='insert'){

$xu = $_GET['xu']+0.001;
$sql1 = "insert into filemanage (pid,namebak,xu,fzx_id) values('$_GET[pid]','$_GET[name]',$xu,'$fzx_id')";
$DB->query($sql1);
$lid = mysql_insert_id();

$sql2 = "insert into filemanage (pid,name,fzx_id) values ('$lid','默认插入行～','$fzx_id')";
	$DB->query($sql2);
//防止刷新重复添加
$url	= $rooturl."/fileadmin/fileadmin.php";
header("Location: ".$url);

}
/*
if($_GET['action']=='insert2'){

$xu = $_GET['xu']+0.001;
$sql1 = "insert into filemanage (pid,name,xu) values('0','$_GET[name]',$xu)";
$DB->query($sql1);
$lid = mysql_insert_id();

$sql2 = "insert into filemanage (pid,namebak) values ('$lid','默认插入行～')";
	$DB->query($sql2);
$lid = mysql_insert_id();
	
$sql3 = "insert into filemanage (pid,name) values ('$lid','默认插入行～')";
	$DB->query($sql3);	
}
*/
$jarr =array(1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六',7=>'七',8=>'八',9=>'九',10=>'十',11=>'十一',12=>'十二',13=>'十三',14=>'十四',15=>'十五',16=>'十六',17=>'十七',18=>'十八',19=>'十九',20=>'二十',21=>'二十一',22=>'二十二',23=>'二十三',24=>'二十四',25=>'二十五',26=>'二十六',27=>'二十七',28=>'二十八',29=>'二十九',30=>'三十',31=>'三十一',32=>'三十二',33=>'三十三',34=>'三十四',35=>'三十五');
$result1 = $result2 = $result3 = array();
$ids1= '';
$tb1=false;
$sql1 = "select * from filemanage where fzx_id='$fzx_id' and pid=0  order by xu,id";
$rs = $DB->query($sql1);
while($r=$DB->fetch_assoc($rs)){
/*$result1[$r['id']]['name']=$r['name'];
$result1[$r['id']]['namebak']=$r['namebak'];
$result1[$r['id']]['flie']=''; 
$result1[$r['id']]['xu']=$r['xu']; */
$result1[$r['id']]=$r;
$result3[$r['id']]['id']=$r['id'];
$result3[$r['id']]['pid']=$r['pid'];
$ids1 .=$r['id'].','; 
$idsarr1[] = $r['id'];
}
$ids1 = substr($ids1,0,-1);
if(!empty($ids1))
{
	$idss="and pid in ($ids1)";
}
/*
**要素：
** id     fzx_id       pid      name   namebak   file             xv
**编号   分中心编号  父级编号   卷号   要素名    文件路径（空）  排序
*/
$sql1 = "select * from filemanage where fzx_id='$fzx_id' $idss order by xu asc,id desc";
$rs = $DB->query($sql1);
while($r=$DB->fetch_assoc($rs)){
/*$result2[$r['pid']][$r['id']]['name']=$r['name'];
$result2[$r['pid']][$r['id']]['namebak']=$r['namebak'];
$result2[$r['pid']][$r['id']]['flie']=$r['flie'];
$result2[$r['pid']][$r['id']]['id']=$r['id'];
$result2[$r['pid']][$r['id']]['pid']=$r['pid'];
$result2[$r['pid']][$r['id']]['xu']=$r['xu'];*/
$result2[$r['pid']][$r['id']]=$r;
$ids2 .=$r['id'].','; 
$idsarr2[$r['pid']][] = $r['id'];
}
$ids2 = substr($ids2,0,-1);
if(!empty($ids2))
{
	$idss2="and pid in ($ids2)";
}
/*
**类：
** id    fzx_id             pid              name   namebak  file     xv
**编号  分中心编号        父级编号           类名   状态    文件路径  排序
**                 （上面查询的要素的编号）
*/
$sql1 = "select * from filemanage where fzx_id='$fzx_id' $idss2 ";
$rs = $DB->query($sql1);
while($r=$DB->fetch_assoc($rs)){
/*$result3[$r['pid']][$r['id']]['name']=$r['name'];
$result3[$r['pid']][$r['id']]['namebak']=$r['namebak'];
$result3[$r['pid']][$r['id']]['flie']=$r['flie'];
$result3[$r['pid']][$r['id']]['id']=$r['id'];
$result3[$r['pid']][$r['id']]['pid']=$r['pid'];
$result3[$r['pid']][$r['id']]['xu']=$r['xu'];*/
$result3[$r['pid']][$r['id']]=$r;
$ids3 .=$r['id'].',';
$idsarr3[$r['pid']][] = $r['id']; 
}
/*
**$idsarr2是要素的编号
**$idsarr3是类的值是类的编号，键值是要素的编号
*/
if(!empty($idsarr2))
{
	foreach($idsarr2 as $kid => $vid){
		foreach($vid as $vid2){
			if(key_exists($vid2,$idsarr3))
				$idarr[$kid][] = $idsarr3[$vid2];
			else
				$idarr[$kid]['k'] +=1;
		}
	}
}
if(!empty($idarr))
{
	foreach($idarr as $kids => $vids){
		foreach($vids as $kids2 => $vids2){
			foreach($vids2 as $vids3){
					$idsarr[$kids][] =$vids3;
			}
		}
	}
}
$i= 1;
foreach($result1 as $kx => $vx){
	$col = count($idsarr[$kx]);
	//$lines .= "<tr><td rowspan=$col><a id=\"$vx[xu]\"  onclick='javascript:pro(this.innerHTML,\"$vx[xu]\",\"$kx\");return false'>$vx[name]</a></td>";
	foreach($result2[$kx] as $kx1 => $vx1){
		$col2 = count($result3[$kx1]);
		$lines .="<tr>
					<td rowspan=$col2>第$jarr[$i]卷</td>
					<td rowspan=$col2>
						<a id=\"$vx1[xu]\" onclick='javascript:pro(this.innerHTML,\"$vx1[xu]\",\"$kx1\",\"$kx\");return false'>$vx1[namebak]</a>
					</td>";
		foreach($result3[$kx1] as $kx2 => $vx2){
			$lines .="<td width=\"500\" >
						<!--<a href =show.php?id=$kx2>$vx2[name]</a>-->
						<a onclick='up_lei(this,$kx2)' title='点击修改类名' >$vx2[name]</a>
					</td>
					<td>
						<a href=upfile/$vx2[file] target=_blank>$vx2[file]</a>";
			$lines.=($vx2['file']!='')?"&nbsp;&nbsp;<a  class='red icon-remove bigger-140' title='删除文件' href='adddeal.php?id=$vx2[id]'></a>":'';
			/*$lines.="</td><td>$vx2[namebak]</td><td>
			<input class='btn btn-xs btn-primary' type=button name=add1x value='增加要素' onClick='javascript:pro2(\"$kx\",\"$vx1[xu]\");'\>
			<input type=button class='btn btn-xs btn-primary' name=add1x value='增加类' onClick='javascript:add1($vx2[pid]);'\>
			<input type=button name=fixx value='修改' class='btn btn-xs btn-primary' onClick='javascript:fix($vx2[id],\"$vx2[name]\",\"$vx2[namebak]\");'>
			<input type=button name=dell value='删除' class='btn btn-xs btn-primary' onClick='javascript:del($vx2[id],$vx2[pid]);'>
			<input type=button name=up value='上传' onClick='javascript:upload($vx2[id]);'</td></tr>";*/
			$lines.="</td>
					<td title='点击修改状态' onclick='up_state(this,$kx2)'>$vx2[namebak]</td>
					<td>
						<input class='btn btn-xs btn-primary' type=button name=add1x value='增加要素' onClick='javascript:pro2(\"$kx\",\"$vx1[xu]\");'\>
						<input type=button class='btn btn-xs btn-primary' name=add1x value='增加类' onClick='javascript:add1($vx2[pid]);'\>
						<a href='show.php?id=$kx2&name=$vx2[name]' class='blue bigger-130 icon-search ' title='查看{$vx2[name]}类的文件'></a>
						<!--<a href='#' class='green icon-edit bigger-130' title='修改'  onClick='javascript:fix($vx2[id],\"$vx2[name]\",\"$vx2[namebak]\");'></a>-->
						<a href='#' class='red icon-remove bigger-140' title='删除' onClick='javascript:del($vx2[id],$vx2[pid]);'></a>
						<a href='#' class='icon-cloud-upload  blue bigger-130' title='上传文件' onClick='javascript:upload($vx2[id]);'></a>
					</td>
				</tr>";
		}
		$i++;
	}
}
disp('fileadmin/fileadmin');
?>

