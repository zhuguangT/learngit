<?php
/**功能： 仪器载入设置页面
 *author:zhengsen
 *时间：2014-09-18
 **/
include "../../temp/config.php";
$fzx_id=$u['fzx_id'];
//删除仪器载入配置信息
if($_GET['action']=="del"&&$_GET['id']){
	$sql_del="delete from `yq_autoload_set` where id='".$_GET[id]."'";
	$query_del=$DB->query($sql_del);
	if($query_del)
	echo 1;
	else
	echo 0;
	exit();
} 
//查询厂商、仪器类型
if($_GET['action']=='add_load_info'){
	$factory_options="";
	$yq_type_options="";
	$data=array();
	$sql_yq_type="select * from `yq_type` where is_load='1'";
	$query_yq_type=$DB->query($sql_yq_type);
	$yq_type_one="";
	while($rs_yq_type=$DB->fetch_assoc($query_yq_type))//仪器种类名称  $rs_yq_type
	{
		if(empty($yq_type_one))
		$yq_type_one=$rs_yq_type[id];
		$yq_type_options.="<option value=".$rs_yq_type['id'].">".$rs_yq_type['yq_type_name']."</option>";
	}
	$data['yq_type_op']=$yq_type_options;
	$sql_factory="select * from `yq_autoload_storeroom` as s left join `yq_factory` as f on s.yq_factory_id=f.id where s.yq_type_id='".$yq_type_one."' group by yq_factory_id";
	$query_factory=$DB->query($sql_factory);
	while($rs_factory=$DB->fetch_assoc($query_factory))//公司名称 $rs_factory
	{
		$factory_options.="<option value=".$rs_factory['id'].">".$rs_factory['factory_name']."</option>";//当第一次运行界面的时候直接出现factory  删除则没有
	}
	$data['factory_op']=$factory_options;
	echo json_encode($data);
	exit();
}
//查询某一仪器类型对应的厂家
if($_GET['action']=="get_fac"&&$_GET['yq_type_id']){
	$factory_options='';
	$sql_factory="select * from `yq_autoload_storeroom` as s left join `yq_factory` as f on s.yq_factory_id=f.id where s.yq_type_id='".$_GET['yq_type_id']."' group by yq_factory_id";
	$query_factory=$DB->query($sql_factory);
	while($rs_factory=$DB->fetch_assoc($query_factory))//yq_autoload_storeroom的
	{
		$factory_options.="<option value=".$rs_factory['id'].">".$rs_factory['factory_name']."</option>";
	}
	echo $factory_options;
	exit();
}
//查询某一厂家某一仪器种类对应的仪器型号
if($_GET['action']=='get_mode'){
	$sql_mode="select * from `yq_autoload_storeroom` where yq_factory_id='".$_GET['fac_id']."' and yq_type_id='".$_GET['yq_type_id']."' group by yq_mode_name";
	$query_mode=$DB->query($sql_mode);
	$yq_mode_options="";
	while($rs_mode=$DB->fetch_assoc($query_mode))
	{
		$yq_mode_options.="<option value=".$rs_mode['yq_mode_name'].">".$rs_mode['yq_mode_name']."</option>";
	}
	if(empty($yq_mode_options))
		echo 0;
	else
		echo $yq_mode_options;
	exit();
}
//获得载入方式
if($_GET['action']=='get_loadway')
{
	$loadway_options="";
	if($_GET['fac_id']&&$_GET['yq_mode_name']&&$_GET['yq_type_id'])
	{
		$sql_loadway="select load_way from `yq_autoload_storeroom` where yq_factory_id='".$_GET['fac_id']."' and yq_mode_name='".$_GET['yq_mode_name']."' and yq_type_id='".$_GET['yq_type_id']."' GROUP BY load_way";
		$query_loadway=$DB->query($sql_loadway);
		while($rs_loadway=$DB->fetch_assoc($query_loadway))
		{
				$loadway_options.="<option value=".$rs_loadway['load_way'].">".$global['load_way'][$rs_loadway['load_way']]."</option>";
		}
		if(!empty($loadway_options))
			echo $loadway_options;
		else
			echo 0;
		exit();

	}
	else
	{
		echo 0;
		exit();
	}
}
//获得载入文件
if($_GET['action']=='get_load_file')
{
	$load_file_options="";
	$print_name_options="";
	if($_GET['fac_id']&&$_GET['yq_type_id']&&$_GET['yq_mode_name']&&$_GET['load_way'])
	{
		$sql_load_file="select load_file,id from yq_autoload_storeroom where yq_type_id='".$_GET['yq_type_id']."' and yq_factory_id='".$_GET['fac_id']."' and yq_mode_name='".$_GET['yq_mode_name']."' and load_way='".$_GET['load_way']."' ";
		//die($sql_load_file);
		$query_load_file=$DB->query($sql_load_file);
		while($rs_load_file=$DB->fetch_assoc($query_load_file))
		{
			$load_file_options.="<option value=".$rs_load_file['id'].">".$rs_load_file['load_file']."</option>";
		}
		$data['load_file_op']=$load_file_options;
		//查询相同类型、相同厂商、相同型号的虚拟打印机名称（添：多查一个字段 printer）
		$print_nums_sql="SELECT * FROM  yq_autoload_storeroom AS st  WHERE st.yq_type_id='".$_GET['yq_type_id']."' AND st.yq_factory_id='".$_GET['fac_id']."' AND st.yq_mode_name='".$_GET['yq_mode_name']."' AND st.load_way='".$_GET['load_way']."'";
		$print_nums_query=$DB->query($print_nums_sql);
		while($print_nums_rs=$DB->fetch_assoc($print_nums_query)){
			$print_name_options.=$print_nums_rs['printer']; //修改之后以文本形式显示，原下拉表
		}
		$data['print_name_op']=$print_name_options;
		$data['message']='1';
	}else{
		$data['message']='0';
	}
	echo json_encode($data);
	exit();
}
//保存新添加的仪器载入设置信息
if($_GET['action']=='save_set'){ //&&isset($_GET['printer'])
	if($_GET['storeroom_id']&&$_GET['yq_id']){ 
		$sql_insert_set="insert into `yq_autoload_set`(`fzx_id`,`storeroom_id`,`yq_id`)values('".$fzx_id."','".$_GET['storeroom_id']."','".$_GET['yq_id']."')";
	
		$query_insert_set=$DB->query($sql_insert_set);
		//查询打印机的名称
		$sql_printer="select printer from  yq_autoload_storeroom where id='".$_GET['storeroom_id']."' ";
		$rs_printer=$DB->query($sql_printer);
		while($printer_rs=$DB->fetch_assoc($rs_printer))
		{
			$print_name=$printer_rs['printer'];
		}
	
		if(mysql_insert_id()){
			if($_GET['load_way']==1){
				exec("/usr/sbin/lpadmin -p $print_name -v cups-pdf:/ -E",$out,$return);//执行成功时返回0
				if(!$retrun){
					echo 1;
				}else{
					$insert_id=mysql_insert_id();
					$DB->query("DELETE FROM yq_autoload_set WHERE id='".$insert_id."'");
					echo 2;
				}
			}else{
				echo 1;
			}
			
		}
		else{
			echo 0;
		}
	
	}
	exit();
}
//查询配置的仪器载入信息
$sql_autoload="select s.*,fac.factory_name,st.yq_type_name,r.printer,r.load_way,r.load_file ,r.yq_mode_name,y.yq_xinghao,y.yq_chucangbh,y.yq_mingcheng from `yq_autoload_set` as s left join `yq_autoload_storeroom` as r on s.storeroom_id =r.id left join `yq_factory` as fac on r.yq_factory_id =fac.id left join `yq_type` as st on r.yq_type_id =st.id left join `yiqi` as y on s.yq_id=y.id where s.fzx_id='".$fzx_id."' GROUP BY st.yq_type_name,s.id";
//die($sql_autoload);
$query_autoload=$DB->query($sql_autoload);
$lines="";
$set_yq_arr=array();
$xh=0;
while($rs_autoload=$DB->fetch_assoc($query_autoload)){
	$xh++;
	$set_yq_arr[]=$rs_autoload['yq_id'];
	$yq_xh=$rs_autoload['yq_xinghao'];
	$yq_bh=$rs_autoload['yq_chucangbh'];
	if(empty($yq_xh)){
		$yq_xh='无';
	}
	if(empty($yq_bh)){
		$yq_bh='无';
	}
	if(empty($rs_autoload['printer'])){ //print_nums
		$print_name='无';
	}else{
		$print_name=$rs_autoload['printer']; //$global['print_bs'].$rs_autoload['print_nums'];
	}
	$yq_info=$rs_autoload['yq_mingcheng'].'(型号: '.$yq_xh.'；编号: '.$yq_bh.')';
	$lines.="<tr>
				 <td>".$xh."</td>
				 <td>".$yq_info."</td>
				 <td>".$rs_autoload['yq_type_name']."</td>
				 <td>".$rs_autoload['factory_name']."</td>
				 <td>".$rs_autoload['yq_mode_name']."</td>
				 <td>".$global['load_way'][$rs_autoload['load_way']]."</td>
				 <td>".$rs_autoload['load_file']."</td>
				 <td id='printer'>".$print_name."</td>
				 <td><a class='btn btn-xs btn-primary' onclick=ajax_del(".$rs_autoload[id].",".$rs_autoload[yq_id].",this)>删除</a><!-- <a class='btn btn-xs btn-primary' href=\"autoload_set_edit.php?id=$rs_autoload[id]\">修改</a> --></td></tr>";
}

//查询本实验室用的所有仪器
//mysql_query("set names utf8");
//$sql_yq="SELECT yq.id,yq.yq_mingcheng,yq.yq_xinghao,yq.yq_chucangbh FROM `yiqi` yq LEFT JOIN yq_type yt ON yq.yq_mingcheng=yt.yq_type_name WHERE yq.fzx_id='".$fzx_id."' AND yq.yq_state='启用' AND yt.is_load='1'";
$sql_yq="SELECT yq.id,yq.yq_mingcheng,yq.yq_xinghao,yq.yq_chucangbh FROM `yiqi` yq  WHERE yq.fzx_id='".$fzx_id."' AND yq.yq_state='启用'";

//die($sql_yq);
$query_yq=$DB->query($sql_yq);
$yq_options="";
while($rs_yq=$DB->fetch_assoc($query_yq))
{
	if(!in_array($rs_yq['id'],$set_yq_arr)){
		$yq_xh=$rs_yq['yq_xinghao'];
		$yq_bh=$rs_yq['yq_chucangbh'];
		if(empty($yq_xh)){
			$yq_xh='无';
		}
		if(empty($yq_bh)){
			$yq_bh='无';
		}
		$yq_options.="<option value=".$rs_yq['id'].">".$rs_yq['yq_mingcheng'].'(型号: '.$yq_xh.'；编号: '.$yq_bh.")</option>";
	}
}
disp("autoload_set");
?>