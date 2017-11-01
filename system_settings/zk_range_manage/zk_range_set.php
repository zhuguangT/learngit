<?php
/** 功能：质控合格范围页面
  * 作者：Mr Zhou
  * 时间：2014-08-01
 **/
include '../../temp/config.php';
//分中心id
$fzx_id		= FZX_ID;
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'质控合格范围设置','href'=>'./system_settings/zk_range_manage/zk_range_set.php')
);
// 是否具有修改质控配置的权限
$canModi = (($u['system_admin'] && $u['is_zz']) || $u['admin']) ? true : false;
if( $canModi && 'del_item'==trim($_GET['action'])&&intval($_GET['del_id'])){
	//删除获得id的质控合格范围
	$_GET['del_id'] = intval($_GET['del_id']);
	$del_sql = "DELETE FROM `zk_set` WHERE id={$_GET['del_id']}";
	echo ($DB->query($del_sql)) ? 1 : 0 ;
	exit();
}else if( $canModi && 'add_item' == trim($_GET['action'])){
	//增加项目的质控合格范围
	$_GET['nd']			= trim($_GET['nd']);
	$_GET['jbhs']		= trim($_GET['jbhs']);
	$_GET['sn_jmd']		= trim($_GET['sn_jmd']);
	$_GET['sj_jmd']		= trim($_GET['sj_jmd']);
	$_GET['sn_xdwc']	= trim($_GET['sn_xdwc']);
	$_GET['sj_xdwc']	= trim($_GET['sj_xdwc']);
	$_GET['add_item']	= intval($_GET['add_item']);
	$_GET['water_type']	= intval($_GET['water_type']);
	if(!$_GET['add_item']&&$_GET['nd']&&$_GET['jbhs']&&$_GET['sn_jmd']&&$_GET['sj_jmd']&&$_GET['sn_xdwc']&&$_GET['sj_xdwc']){
		echo json_encode(array('error'=>'1','notice'=>'请输入有效数据！错误代码001'));die;
	}
	$data		= array();
	$zk_vid_arr	= array();
	$data['id']	= 0;
	$data['exist'] = '0';
	$data['error'] = '0';
	$zk_vid_que	= $DB->query("SELECT `nd` FROM `zk_set` WHERE `vid` = '{$_GET['add_item']}' AND `water_type` = '{$_GET['water_type']}'");
	while($row=$DB->fetch_assoc($zk_vid_que)){
		$data['exist'] = '1';
		if($row['nd'] == $_GET['nd']){
			$data['notice'] = '在浓度为“'.$row['nd'].'”的质控信息已经存在，不可重复添加！';
			echo json_encode($data);
			die;
		}
	}
	$insert_sql="INSERT INTO `zk_set`( `water_type`, `vid`, `nd`, `sn_jmd`, `sj_jmd`, `jbhs`, `sn_xdwc`, `sj_xdwc`) VALUES ('{$_GET['water_type']}','{$_GET['add_item']}','{$_GET['nd']}','{$_GET['sn_jmd']}','{$_GET['sj_jmd']}','{$_GET['jbhs']}','{$_GET['sn_xdwc']}','{$_GET['sj_xdwc']}')";
	if($DB->query($insert_sql)){
		$data['id']	= $DB->insert_id();
	}
	$data['error']	= '0';
	$data['notice'] = '添加成功';
	echo json_encode($data);
	exit();
}else if( $canModi && 'edit_item' == trim($_GET['action'])){
	//修改项目的质控合格范围
	$data = array();
	$data['error'] = '0';
	if(trim($_GET['modi_name'])&&intval($_GET['modi_id'])){
		$update_sql="UPDATE `zk_set` SET  `".trim($_GET['modi_name'])."`='".trim($_GET['modi_value'])."' WHERE id='".intval($_GET['modi_id'])."'";
		if($DB->query($update_sql)){
			$data['error'] = '0';
			$data['notice']	= '修改成功！';
			echo json_encode($data);die();
		}else{
			$data['error']	= '1';
			$data['notice']	= '修改失败，错误代码001！';
			echo json_encode($data);die();
		}
	}else{
		$data['error']	= '1';
		$data['notice']	= '修改失败，错误代码002！';
		echo json_encode($data);die();
	}
}

//获得所有的项目
$sql = "SELECT av.value_C,av.id FROM `assay_value` av LEFT JOIN `xmfa` x ON av.id=x.xmid WHERE x.`act` = '1' AND x.fzx_id='$fzx_id' GROUP BY av.id ORDER BY seq,av.id";
$sql_assay_value = $DB->query($sql);
while($rs_assay_value=$DB->fetch_assoc($sql_assay_value))
{
	$valueOption .= '<option value="'.$rs_assay_value['id'].'">'.$rs_assay_value['value_C'].'</option>';
}
//获得所有的一级水样类型
$_GET['water_type']	= intval($_GET['water_type']);
$wt_id = $_GET['water_type'] = intval($_GET['water_type']);
// 增加默认配置水样类型
$water_type = array(
	array(
		'id' => 0,
		'lname' => '默认配置'
	)
);
$water_type_option .= '<option value="'.$water_type[0]['id'].'" '.$select.'>'.$water_type[0]['lname'].'</option>';
$sql = "SELECT id,lname FROM `leixing` WHERE `parent_id` = 0 AND `act`=1 ORDER BY `id`";
$query = $DB->query($sql);
while($row=$DB->fetch_assoc($query))
{
	$water_type[]		= $row;
	$select				= ($row['id'] == $wt_id) ? 'selected' : '';
	$water_type_option .= '<option value="'.$row['id'].'" '.$select.'>'.$row['lname'].'</option>';
}
//查询出所有项目的质控合格范围记录
$sql = "SELECT `z`.*,`av`.`value_C` FROM `zk_set` `z` LEFT JOIN `assay_value` `av` ON `av`.`id`=`z`.`vid` WHERE 1  AND `z`.`water_type` = '{$wt_id}' ORDER BY `av`.`id`";
$rs = $DB->query($sql);
$zk_range_arr=array();
while($r = $DB->fetch_assoc($rs)){
	$zk_range_arr[$r['vid']][]=$r;
}
$lines='';
foreach ($global['zk']['zk_set'] as $key => $value) {
	if($key==0){
		$lines.='<tr align="center"><th rowspan="'.(count($global['zk']['zk_set'])).'" style="background-color:#F5F5F5;vertical-align:middle;text-align:center;color:#707070">默认配置</th>';
	}else{
		$lines.='<tr align="center">';
	}
	$lines.="<td >".$value['nd']."     </td>
			 <td >".$value['sn_jmd']." </td>
			 <td >".$value['sj_jmd']." </td>
			 <td >".$value['jbhs']."   </td>
			 <td >".$value['sn_xdwc']."</td>
			 <td >".$value['sj_xdwc']."</td>
			 <td >-</td></tr>";
}
foreach($zk_range_arr as $k1=>$v1){
	$tr_nums=count($v1);
	$i=1;
	foreach($v1 as $k2=>$v2)
	{
		if($i==1){
			$lines.='<tr align="center" id="tr'.$k1.'_'.$i.'" class="tr'.$k1.'">
				<th rowspan="'.$tr_nums.'" style="background-color:#F5F5F5;vertical-align:middle;text-align:center;color:#707070">'.$v2['value_C'].'</th>';
		}else{
			$lines.='<tr align="center" id="tr'.$k1.'_'.$i.'" class="tr'.$k1.'">';
		}
		if(($u['system_admin'] && $u['is_zz'])||$u['admin']){
			$del = "<a class='red icon-remove bigger-130' onclick=ajax_del('".$v2['id']."',this)></a>";
		}else{
			$del ='-';
			$add_button = '';
		}
		$lines.="<td onclick=ajax_modi('".$v2['id']."','nd',this)     >".$v2['nd']."     </td>
				 <td onclick=ajax_modi('".$v2['id']."','sn_jmd',this) >".$v2['sn_jmd']." </td>
				 <td onclick=ajax_modi('".$v2['id']."','sj_jmd',this) >".$v2['sj_jmd']." </td>
				 <td onclick=ajax_modi('".$v2['id']."','jbhs',this)   >".$v2['jbhs']."   </td>
				 <td onclick=ajax_modi('".$v2['id']."','sn_xdwc',this)>".$v2['sn_xdwc']."</td>
				 <td onclick=ajax_modi('".$v2['id']."','sj_xdwc',this)>".$v2['sj_xdwc']."</td>
				 <td nowrap=nowrap class=''>$del</td></tr>";
		$i++;
	}
}

$trade_global = json_encode($trade_global);
if(($u['system_admin'] && $u['is_zz'])||$u['admin']){
	$add_button = '<button name="add_zk_range" type="button" id="add_zk_range" class="btn btn-xs btn-primary">添加</button>';
	$zhushi = '<p>注：输入以下字母可以替换相应的特殊符号：<b>a→&lt; b→&gt; c→～ d→≤ e→≥ f→±</b></p>';
}
echo temp('head');
echo temp('zk_range_set');?>
<script type="text/javascript">
<?php if($canModi){ ?>
	//手动添加的数据条数
	var add_click_times = 0;
	//删除一条质控设置信息
	function ajax_del(id,t){
		if(!confirm("确定要删除？")){
			return false;
		}
		if(id){
			$.get('zk_range_set.php?ajax=1&action=del_item',{del_id:id},function(data){
				if(data==1)
				{
					var id=$(t).parent().parent().attr('id');
					var cla=$(t).parent().parent().attr('class');
					var z_tr=$("."+cla).length;
					var tr_num=id.substr(-1,1);
					if(tr_num=="1")
					{
						var value_C=$("#"+cla+"_1").find("th:first").text();
						$("."+cla).each(function(){
							var id=$(this).attr('id');
							var tr_num=id.substr(-1,1)-1;
							$(this).attr("id",cla+"_"+tr_num);
						});
						$("#"+cla+"_1").find("td:first").before('<th>'+value_C+'</th>');
					}else{
						$("."+cla).each(function(){
							var id=$(this).attr('id');
							var now_tr_num=id.substr(-1,1);
							if(now_tr_num>tr_num)
							{
								var tr_num=id.substr(-1,1)-1;
								$(this).attr(cla+"_"+tr_num);
							}
						});
					}
					$(t).parent().parent().remove();
					var len=$("."+cla).length;
					$("#"+cla+"_1").find("th:first").attr("rowspan",len);
				}
			},'html');
		}else{
			$(t).parent().parent().remove();
		}
		return true;
		
	}
	//点击增加项目质控范围时执行函数
	$('#add_zk_range').click(function() {
		add_click_times++;
		var length = add_click_times;
		var option_data=$("#value_C").html(); //所有可用的项目
		$('#tab tr').eq(1).after(
			'<tr align="center" class="xm" id="add_item_tr'+length+'">'+
				'<td><select name="item'+length+'"	class="add_item chosen-select" id="add_item'+length+'" onblur="sub_new_zkfw(this)">'+option_data+'</select></td>'+
				'<td><input name="nd'+length+'"		id="nd'+length+'"		data-num="'+length+'" onblur="sub_new_zkfw(this)"/></td>'+
				'<td><input name="sn_jmd'+length+'"	id="sn_jmd'+length+'"	data-num="'+length+'" onblur="sub_new_zkfw(this)"/></td>'+
				'<td><input name="sj_jmd'+length+'"	id="sj_jmd'+length+'"	data-num="'+length+'" onblur="sub_new_zkfw(this)"/></td>'+
				'<td><input name="jbhs'+length+'"	id="jbhs'+length+'"		data-num="'+length+'" onblur="sub_new_zkfw(this)"/></td>'+
				'<td><input name="sn_xdwc'+length+'"id="sn_xdwc'+length+'"	data-num="'+length+'" onblur="sub_new_zkfw(this)"/></td>'+
				'<td><input name="sj_xdwc'+length+'"id="sj_xdwc'+length+'"	data-num="'+length+'" onblur="sub_new_zkfw(this)"/></td>'+
				'<td><a class="red icon-remove bigger-130" onclick="ajax_del(null,this)"></a></td></tr>');
		//将第一个 “全部” 选项去除
		$("#add_item"+length+' option').eq(0).remove();
		$(".chosen-select").chosen();
		//输入的字符过滤
		str_check("#nd"+length+",#sn_jmd"+length+",#sj_jmd"+length+",#jbhs"+length+",#sn_xdwc"+length+",#sj_xdwc"+length)
	});
	//限制字符输入 
	function str_check(select_id){
		$(select_id).keydown(function(event){
			if(!event.key.match("[0-9a-f.><～≤≥±xv]")){
				return false;
			}
		});
		$(select_id).keyup(function(event){
			var str_value = $(this).val();
			if(!event.key.match("[0-9.><～≤≥±]")){
				$(this).val(str_replace(str_value,event.key));
			}
		});
	}
	//使用abcdef替代><~≤≥±特殊字符的输入
	function str_replace(str_value,key){
		switch(key) {
				case 'a':return (str_value.replace(key,'<'));break;
				case 'b':return (str_value.replace(key,'>'));break;
				case 'c':return (str_value.replace(key,'～'));break;
				case 'd':return (str_value.replace(key,'≤'));break;
				case 'e':return (str_value.replace(key,'≥'));break;
				case 'f':return (str_value.replace(key,'±'));break;
				default :return (str_value.replace(key,''));
			}
	}

	//当光标失去时执行此函数提交增加的质控合格范围信息
	function sub_new_zkfw(t){
		var id			= t.id;
		var id_num		= $(t).attr('data-num');
		var item		= $("#add_item"+id_num).val();
		var item_name	= $("#add_item"+id_num).find("option:selected").text();
		var nd			= $("#nd"+id_num).val();
		var jbhs		= $("#jbhs"+id_num).val();
		var sn_jmd		= $("#sn_jmd"+id_num).val();
		var sj_jmd		= $("#sj_jmd"+id_num).val();
		var sn_xdwc		= $("#sn_xdwc"+id_num).val();
		var sj_xdwc		= $("#sj_xdwc"+id_num).val();

		var nd			= (nd=='')? '':nd;
		var jbhs		= (jbhs=='')? '':jbhs;
		var sn_jmd		= (sn_jmd=='')? '':sn_jmd;
		var sj_jmd		= (sj_jmd=='')?'':sj_jmd;
		var sn_xdwc		= (sn_xdwc=='')?'':sn_xdwc;
		var sj_xdwc		= (sj_xdwc=='')?'':sj_xdwc;
		if(nd || jbhs || sn_jmd || sj_jmd || sn_xdwc || sj_xdwc)
		{
			if(1)//confirm("确定要添加"+item_name+"的质控合格范围？"))
			{
				$.getJSON('zk_range_set.php?ajax=1&action=add_item',
					{water_type:$("#water_type").val(),add_item:item,nd:nd,jbhs:jbhs,sn_jmd:sn_jmd,sj_jmd:sj_jmd,sn_xdwc:sn_xdwc,sj_xdwc:sj_xdwc},
				function(data){
					if('1'==data.error){
						alert(data.notice);
					}else if(!data.id&&data.exist=='1'){
						alert(item_name+data.notice);
					}else if(data.id&&data.exist=='1'){
						window.location.href='zk_range_set.php?water_type='+$("#water_type").val();
					}else if(data.id&&data.exist=='0'){
						$(t).parent().parent().replaceWith(
							'<tr align="center" id="tr'+item+'_1" class="tr'+item+'" style="background-color:#F5F5F5;vertical-align:middle;text-align:center;color:#707070">'+
							'<th rowspan="1">'+item_name+'</th>'+
							'<td onclick="ajax_modi('+data.id+',\'nd\',this)">'+nd+'</td>'+
							'<td onclick="ajax_modi('+data.id+',\'sn_jmd\',this)">'+sn_jmd+'</td>'+
							'<td onclick="ajax_modi('+data.id+',\'sj_jmd\',this)">'+sj_jmd+'</td>'+
							'<td onclick="ajax_modi('+data.id+',\'jbhs\',this)">'+jbhs+'</td>'+
							'<td onclick="ajax_modi('+data.id+',\'sn_xdwc\',this)">'+sn_xdwc+'</td>'+
							'<td onclick="ajax_modi('+data.id+',\'sj_xdwc\',this)">'+sj_xdwc+'</td>'+
							'<td nowrap="nowrap"><a class="red icon-remove bigger-130" onclick="ajax_del('+data.id+',this)"></a></td></tr>');
						$(".add_item").each(function(){
							 $(this).find("option[value="+item+"]").remove();
							 var length=$(this).find("option").length;
							 if(length=='0')
							 $(this).parent().parent().remove();
						});
						return true;
					}
				});
			}else{
				$('#add_item_tr'+' input').val('');
				return false;
			}
		}
	}
	//点击浓度、加标回收、精密度的td时显示为input输入框
	function ajax_modi(id,name,t){
		var value=$(t).text();
		$(t).text('');
		$(t).append('<input type="text" style="width:150px" name="'+name+'" id="'+name+id+'" value="'+value+'" onblur="ajax_sub(this,'+id+',\''+value+'\')" class="modi">');
		$(t).attr('onclick','');
		//输入的字符过滤
		str_check('#'+name+id);
		$('#'+name+id).val(value).focus();
	}
	//光标离开时修改浓度、加标回收、精密度的值
	function ajax_sub(t,id,old_value){	
		var t_id=t.id;
		var name=t.name;
		var val=t.value;
		//if(val==''||val==null){if(!confirm('确定要清空此项吗')){$(t).val(old_value);return false;}	}
		$.getJSON('zk_range_set.php?ajax=1&action=edit_item',{modi_name:name,modi_value:val,modi_id:id},function(data){
			if('1' == data.error){
				alert(data.notice);
				val = old_value;
			}
			$("#"+t_id).parent().text(val).attr("onclick","ajax_modi('"+id+"','"+name+"',this)");
		});
	}
<?php } ?>
	//根据 项目名称 筛选 项目
	$("#value_C").change(function(){
		var value_C=$("#value_C").val();
		if(value_C=="全部"){
			$("#tab tr").show();
		}else{
			$("#tab tr:gt(1)").hide();
			$(".tr"+value_C).show();
		}
	});
</script>
<?php echo temp('bottom');
?>
