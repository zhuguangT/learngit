<?php
include '../temp/config.php';
//这个页面是设置化验项目模板的页面  的保存 或者是添加新的 模板名称
$fzx_id	= FZX_ID;//中心
$mb=explode('*',$_POST[mbxm]);
$mbid = $mb[1];//获取id，接下来的大多函数都要用到id.

if($_POST[action]=='shai')//ajax操作后修改下拉菜单并选中最新添加或修改的
{
	$mbxm = '<option value="">----请选择----</option>';
	$S = $DB->query( "SELECT * FROM `n_set` WHERE module_name='xmmb' AND fzx_id='$fzx_id'" );
	while( $row = $DB->fetch_assoc( $S ) ) {
		if($_POST[ming]==$row[module_value2]&&$_POST[mbid]==$row[id])
		{
			if($row[module_value1]!='')
			{
				$mbxm.="<option selected=\"selected\"  value=\"$row[module_value1]*$row[id]\">$row[module_value2]</option> ";
			}else{
				$mbxm.="<option selected=\"selected\"  value='*$row[id]'>$row[module_value2]</option> ";
			}
		}else{
			$mbxm.="<option value=\"$row[module_value1]*$row[id]\">$row[module_value2]</option> ";
		}
	}
	echo $mbxm;
}

if($_POST[action]=='shai1')//ajax操作后修改下拉菜单并选中最新添加或修改的
{
	$mbxm = '<option value="">----请选择----</option>';
	$S = $DB->query( "SELECT * FROM `n_set` WHERE module_name='xmmb' AND fzx_id='$fzx_id'" );
	while( $row = $DB->fetch_assoc( $S ) ) {
		if($_POST[ming]==$row[module_value2]&&$_POST[mbid]==$row[id])
		{
			if($row[module_value1]!='')
			{
				$mbxm.="<option selected=\"selected\"  value=\"$row[module_value1]\">$row[module_value2]</option> ";
			}else{
				$mbxm.="<option selected=\"selected\"  value='*$row[id]'>$row[module_value2]</option> ";
			}
		}else{
			$mbxm.="<option value=\"$row[module_value1]\">$row[module_value2]</option> ";
		}
	}
	echo $mbxm;
}

if($_POST[action]=='addmb')//添加
{
	$data=array();//单击添加，清空上一次的项目id
	if($_POST[newname]==''){
		echo '0';
	}
	else{
		$xm = $_POST[vids];

		$DB->query("INSERT INTO `n_set` set module_name='xmmb',module_value2='$_POST[newname]',module_value1='$xm',fzx_id='$fzx_id',module_value3='1'");
		$m[id] = $DB->insert_id();
		$m[name]=$_POST[newname];
		echo json_encode($m);
	}
	
}

if($_POST[action]=='del')//删除
{	
	$result = $DB->query("delete from `n_set` where module_name='xmmb' and module_value2='$_POST[gai]' and id='$mbid'");
	if($result){
		echo '1';
	}else{
		echo '0';
	}
}

if($_POST[action]=='upmb')//修改   
{
	if($_POST[newname]==''&&$_POST[gai]!="----请选择----"){
		$result = $DB->query("UPDATE `n_set` set module_value1='$_POST[nxm]' where module_name='xmmb' and module_value2='$_POST[gai]' and id='$mbid'");
		$m[id]=$mbid;
		$m[name]=$_POST[gai];
		echo json_encode($m);
	}
	else if($_POST[gai]=="----请选择----")
	{
		echo '发生未知错误';
	}
	else
	{
		$S=$DB->query("select * from `n_set` where module_name='xmmb'" );
		while($row = $DB->fetch_assoc($S)){
			if($_POST[gai]==$row[module_value2]){
				$result = $DB->query("UPDATE `n_set` set module_value2='$_POST[newname]',module_value1='$_POST[nxm]' where module_name='xmmb' and module_value2='$_POST[gai]' and id='$mbid'");
			}
		}
		if($result)
		{
			$m[id]=$mbid;
			$m[name]=$_POST[newname];
			echo json_encode($m);
		}else{
			echo '发生未知错误';
		}
	}

}

if($_POST[vid]!='')//保存  
{
	$arr = $_POST[vid];
	$xm = implode(',',$arr);

	$sql="update `n_set` set module_value1='$xm'  where id='$mbid' and module_name='xmmb' LIMIT 1";
	$result = $DB->query($sql);
	if($result)
	{
		echo $mbid;
	}else{
		echo '0';
	}
	
}

?>