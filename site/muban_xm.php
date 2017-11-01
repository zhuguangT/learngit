<?php
include '../temp/config.php';

//这个页面是设置化验项目模板的页面
//得到模板的下拉菜单
$fzx_id	= FZX_ID;//中心
$S = $DB->query( "SELECT * FROM `n_set` WHERE module_name='xmmb' AND fzx_id='$fzx_id'" );
$mbxm = '<option value="">----请选择----</option>';
while( $row = $DB->fetch_assoc( $S ) ) {
	if(isset($_POST[mbname])){
		if($_POST[mbname]==$row[module_value2]&&$_POST[xm]==$row[module_value1])
		{
			$mbxm.="<option selected=\"selected\"  value=\"$row[module_value1]*$row[id]\">$row[module_value2]</option> ";
		}else{
			$mbxm.="<option value=\"$row[module_value1]*$row[id]\">$row[module_value2]</option> ";
		}
	}
}
####################显示项目
if($_POST[fa] == '1'){
	$sql="SELECT distinct av.value_C,av.id AS vid FROM `assay_value` AS av WHERE  1 ORDER BY av.id ASC"; 
}else{
	$sql="SELECT distinct av.value_C,av.id AS vid FROM `xmfa` AS aj JOIN `assay_value` AS av ON aj.xmid=av.id WHERE  1 ORDER BY av.id ASC"; 
}
$av=$DB->query($sql);
$s=1;
$lan=strlen($_POST[mbname]);
if($_POST[xm]!='')
{
	$mbs=explode(',',$_POST[xm]);
}else{
	$mbs=array();
}
while( $row = $DB->fetch_assoc( $av ) )
{	
	$y=$s%5;
	$mid=$row['vid'];
	
	if($mbs==''){
		$mbs=array();
	}
	if(in_array($row[vid],$mbs)){
		$pd='checked="checked"';
	}  else{$pd='';}
	$mx[$y]='<label class="show" flag="mb" style="cursor: pointer;"><input '.$pd.' name="vid[]" flag="mb" value="'.$mid.'" type="checkbox">'.$row['value_C'].'</label>';
	
	//echo "id:$row[vid]  项目:  $row[value_C]";
	if($s%5==0)
	{
		$lines.=temp('muban_xm_line');
		$n=$s;
		unset($mx);
		unset($mid);
		unset($pd);
	}	
	$s++;
}
if($s>$n){
	$lines.=temp('muban_xm_line');
}
disp('muban_xm');
?>