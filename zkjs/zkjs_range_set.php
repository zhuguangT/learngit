<?php
/** 功能：质控合格范围页面
  * 作者：Mr Zhou
  * 时间：2014-08-01
 **/
include '../temp/config.php';
//分中心id
$fzx_id		= FZX_ID;
//导航
$trade_global['daohang'] = array(
	array('icon'=>'icon-home home-icon','html'=>'首页','href'=>'main.php'),
	array('icon'=>'','html'=>'质控计算设置','href'=>'./system_settings/zk_range_manage/zkjs_range_set.php')
);
$jbhs_cs=$global['zk_js']['jbhs'];
if('del_item'==trim($_GET['action'])&&intval($_GET['del_id'])){
	//删除获得id的质控合格范围
	$_GET['del_id'] = intval($_GET['del_id']);
	$del_sql = "DELETE FROM `zk_js` WHERE id={$_GET['del_id']}";
	echo ($DB->query($del_sql)) ? 1 : 0 ;
	exit();
}
else if('add_item' == trim($_GET['action'])){
	//增加项目的质控合格范围
	$jbhs['jsgs']		= trim($_GET['jsgs']);
	$jbhs['tjxs']		= trim($_GET['tjxs']);
	$jbhs['jcx_jg']		= trim($_GET['jcx_jg']);
	$jbhs['sj_jg']		= trim($_GET['sj_jg']);
	$jbhs['yxws']		= trim($_GET['yxws']);
	$_GET['add_item']	= intval($_GET['add_item']);
	// $jbhs['water_type'] = (0==$_GET['water_type'])?1:$_GET['water_type'];
	$jbhs=json_encode($jbhs);
	if(!$_GET['add_item']&&$_GET['jsgs']&&$_GET['tjxs']&&$_GET['jcx_jg']&&$_GET['sj_jg']&&$_GET['yxws']){
		die;
	}
	// $data		= array();
	// $zk_vid_arr	= array();
	// $data['id']	= 0;
	// $data['exist'] = '0';
	// $data['error'] = '0';
	// $zk_vid_que	= $DB->query("SELECT `nd` FROM `zk_js` WHERE `vid` = '{$_GET['add_item']}' AND `water_type` = '{$_GET['water_type']}'");
	// while($row=$DB->fetch_assoc($zk_vid_que)){
	// 	$data['exist'] = '1';
	// 	if($row['nd'] == $_GET['nd']){
	// 		$data['notice'] = '在浓度为“'.$row['nd'].'”的质控信息已经存在，不可重复添加！';
	// 		echo json_encode($data);
	// 		die;
	// 	}
	// }
	$insert_sql="INSERT INTO `zk_js` ( `vid`, `jbhs` ) VALUES ('{$_GET['add_item']}','{$jbhs}')";
	if($DB->query($insert_sql)){
		echo 1;
	}
	else
	{
		echo 2;
	}
	exit();
}
else if('edit_item' == trim($_GET['action'])){
	//修改项目的质控计算
	$data = array();
	$data['error'] = '0';
	if(trim($_GET['modi_name'])&&intval($_GET['modi_id'])){
		$sql="SELECT * FROM `zk_js` where id ='{$_GET['modi_id']}'";
		$sj=$DB->fetch_one_assoc($sql);
		$jb=json_decode($sj['jbhs'],true);
		$jbhs['jsgs']			 = trim($jb['jsgs']);
		$jbhs['tjxs']			 = trim($jb['tjxs']);
		$jbhs['jcx_jg']			 = trim($jb['jcx_jg']);
		$jbhs['sj_jg']			 = trim($jb['sj_jg']);
		$jbhs['yxws']		     = trim($jb['yxws']);
		$jbhs[$_GET['modi_name']]= trim($_GET['modi_value']);
	// $jbhs['water_type'] = (0==$_GET['water_type'])?1:$_GET['water_type'];
	$jbhs=JSON($jbhs);
		$update_sql="UPDATE `zk_js` SET  `jbhs`='".$jbhs."' WHERE id='".intval($_GET['modi_id'])."'";
		if($DB->query($update_sql)){
			$data['error'] = '1';
			$data['cs'] = $jbhs_cs[$_GET['modi_name']][$_GET['modi_value']];
			echo json_encode($data);
			die();
		}else{
			echo 0;
			die();
		}
	}else{
		echo 0;
		die();
	}
}//参数获取 
else if('get_jbcs' == trim($_GET['action']))
{
	if($_GET['csname'])
	{
		$get_jsgs='';
		foreach ($jbhs_cs[$_GET['csname']] as $key => $value) {
			$get_jsgs.="<option value='$key'>".$value."</option>";
		}
		echo $get_jsgs;
		die();
	}
	die();
}

foreach ($jbhs_cs as $mc => $value) {
	foreach ($value as $key => $value) {
		$$mc.="<option value='$key'>".$value."</option>";
	}

}
//获得所有的项目
$sql = "SELECT av.value_C,av.id FROM `assay_value` av LEFT JOIN `xmfa` x ON av.id=x.xmid WHERE x.`act` = '1' AND x.fzx_id='$fzx_id' GROUP BY av.id ORDER BY seq,av.id";
$sql_assay_value = $DB->query($sql);
while($rs_assay_value=$DB->fetch_assoc($sql_assay_value))
{
	$valueOption .= '<option value="'.$rs_assay_value['id'].'">'.$rs_assay_value['value_C'].'</option>';
}
//查询出所有项目的质控合格范围记录
// $water_type_sql = ($wt_id != 0 ) ? " AND z.water_type = $wt_id" : '';
$sql = "SELECT z.*,av.value_C FROM zk_js z LEFT JOIN assay_value av ON av.id=z.vid WHERE 1  ORDER BY av.id";
$rs = $DB->query($sql);
$zkjs_range_arr=array();
while($r = $DB->fetch_assoc($rs)){
	
	$zkjs_range_arr[$r['vid']][]=$r;
}
$add_button = '<button name="add_zkjs_range" type="button" id="add_zkjs_range" class="btn btn-xs btn-primary">添加</button>';
$lines='';
$lines.='<tr align="center"><th colspan="6" style="background-color:#EAE8E8;vertical-align:middle;text-align:center;color:#707070;font-size:17px">默认配置</th><td style="background-color:#EAE8E8;"></td></tr>';
$lines.='<tr align="center"><td style="background-color:#F5F5F5;vertical-align:middle;text-align:center;color:#707070;font-size:15px">默认配置<br />(除特殊项目以外)</td>';
$lines.="<td >".$jbhs_cs['jsgs'][$global['zk']['jb_js']['jsgs']]."     </td>
		 <td >".$jbhs_cs['tjxs'][$global['zk']['jb_js']['tjxs']]." </td>
		 <td >".$jbhs_cs['sj_jg'][$global['zk']['jb_js']['sj_jg']]." </td>
		 <td >".$jbhs_cs['jcx_jg'][$global['zk']['jb_js']['jcx_jg']]."   </td>
		 <td >".$jbhs_cs['blws'][$global['zk']['jb_js']['blws']]."</td>
		 <td >-</td></tr>";
$lines.='<tr align="center"><th colspan="6" style="background-color:#EAE8E8;vertical-align:middle;text-align:center;color:#707070;font-size:17px">特殊项目</th>';
$lines.='<td  style="background-color:#EAE8E8;">'.$add_button.'</td>';
foreach($zkjs_range_arr as $k1=>$v1){
	foreach($v1 as $k2=>$v2)
	{
		$lines.='<tr align="center" id="tr'.$k1.'_'.$i.'" class="tr'.$k1.'">
			<th  style="background-color:#F5F5F5;vertical-align:middle;text-align:center;color:#707070">'.$v2['value_C'].'</th>';
		if(($u['system_admin'] && $u['is_zz'])||$u['admin']){
			$del = "<a class='red icon-remove bigger-130' onclick=ajax_del('".$v2['id']."',this)></a>";
		}else{
			$del ='-';
			$add_button = '';
		}
		$jbhs=json_decode($v2['jbhs'],true);
		$lines.="<td onclick=ajax_modi('".$v2['id']."','jsgs',this) >".$jbhs_cs['jsgs'][$jbhs['jsgs']]."  </td>
				 <td onclick=ajax_modi('".$v2['id']."','tjxs',this) >".$jbhs_cs['tjxs'][$jbhs['tjxs']]." </td>
				 <td onclick=ajax_modi('".$v2['id']."','sj_jg',this)   >".$jbhs_cs['sj_jg'][$jbhs['sj_jg']]."   </td>
				 <td onclick=ajax_modi('".$v2['id']."','jcx_jg',this) >".$jbhs_cs['jcx_jg'][$jbhs['jcx_jg']]." </td>
				 <td onclick=ajax_modi('".$v2['id']."','blws',this)>".$jbhs_cs['blws'][$jbhs['blws']]."</td>
				 <td nowrap=nowrap class=''>$del</td></tr>";
		$i++;
	}
}

echo temp('head');
echo temp('zkjs_range_set');?>

<script type="text/javascript">
<?php if(($u['system_admin'] && $u['is_zz'])||$u['admin']){ ?>
	//手动添加的数据条数
	var add_click_times = 0;
	function ajax_del(id,t){
		if(!confirm("确定要删除？")){
			return false;
		}
		if(id){
			$.get('zkjs_range_set.php?ajax=1&action=del_item',{del_id:id},function(data){
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
	$('#add_zkjs_range').click(function() {
		add_click_times++;
		var length = add_click_times;
		var option_data=$("#value_C").html(); //所有可用的项目
		var jsgs_data=$("#data_jsgs").html(); //所有可用的项目
		var tjxs_data=$("#data_tjxs").html();
		var sj_jg_data=$("#data_sj_jg").html();
		var jcx_jg_data=$("#data_jcx_jg").html();
		var blws_data=$("#data_blws").html();
		$('#tab tr').eq(1).after(
			'<tr align="center" class="xm" id="add_item_tr'+length+'">'+
				'<td><select name="item'+length+'"	class="add_item chosen-select" id="add_item'+length+'" onblur="sub_new_zkfw(this)">'+option_data+'</select></td>'+
				'<td><select name="jsgs'+length+'"  id="jsgs'+length+'" data-num="'+length+'"  onblur="sub_new_zkfw(this)">'+jsgs_data+'</select></td>'+
				'<td><select name="tjxs'+length+'"	id="tjxs'+length+'"	data-num="'+length+'" onblur="sub_new_zkfw(this)">'+tjxs_data+'</select></td>'+
				'<td><select name="sj_jg'+length+'"	id="sj_jg'+length+'" data-num="'+length+'" onblur="sub_new_zkfw(this)">'+sj_jg_data+'</select></td>'+
				'<td><select name="jcx_jg'+length+'" id="jcx_jg'+length+'"	data-num="'+length+'" onblur="sub_new_zkfw(this)">'+jcx_jg_data+'</select></td>'+
				'<td><select name="yxws'+length+'"id="blws'+length+'"	data-num="'+length+'" onblur="sub_new_zkfw(this)">'+blws_data+'</select></td>'+
				'<td><a class="red icon-remove bigger-130" onclick="ajax_del(null,this)"></a></td></tr>');
		//将第一个 “全部” 选项去除
		$("#add_item"+length+' option').eq(0).remove();
		$(".chosen-select").chosen();
		//输入的字符过滤
		 //str_check("#jsgs"+length+",#tjxs"+length+",#js_jg"+length+",#jcx_jg"+length+",#yxws"+length)
	});

	//当光标失去时执行此函数提交增加的质控合格范围信息
	function sub_new_zkfw(t){
		var id			= t.id;
		var id_num		= $(t).attr('data-num');
		var item		= $("#add_item"+id_num).val();
		var item_name	= $("#add_item"+id_num).find("option:selected").text();
		var jsgs		= $("#jsgs"+id_num).val();
		var tjxs		= $("#tjxs"+id_num).val();
		var jcx_jg		= $("#jcx_jg"+id_num).val();
		var sj_jg		= $("#sj_jg"+id_num).val();
		var yxws		= $("#yxws"+id_num).val();

		// var jsgs			= (jsgs=='')? '':nd;
		// var jbhs		= (jbhs=='')? '':jbhs;
		// var sn_jmd		= (sn_jmd=='')? '':sn_jmd;
		// var sj_jmd		= (sj_jmd=='')?'':sj_jmd;
		// var sn_xdwc		= (sn_xdwc=='')?'':sn_xdwc;
		// var sj_xdwc		= (sj_xdwc=='')?'':sj_xdwc;
		if(jsgs || tjxs || jcx_jg || sj_jg || yxws)
		{
			if(1)//confirm("确定要添加"+item_name+"的质控合格范围？"))
			{
				$.get('zkjs_range_set.php?ajax=1',{action:"add_item",add_item:item,item:item,jsgs:jsgs,tjxs:tjxs,sj_jg:sj_jg,yxws:yxws},function(data){
					if(data=="1"){
						window.location.href="zkjs_range_set.php";
					}else{
						alert("添加失败！");
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
		// if(name=='jsgs')
		// {
			// $(t).parent().next("td").next("td").text('');
			// $(t).parent().next("td").next("td").next("td").text('');
			// $(t).parent().next("td").next("td").next("td").next("td").text('');
			$.get('zkjs_range_set.php?ajax=1',{action:"get_jbcs",csname:name,id:id},function(data){
			$(t).append('<select name="'+name+'" id="'+name+id+'" value="'+value+'" onblur="ajax_sub(this,'+id+',\''+value+'\')" class="modi">'+data+'</sselect>');
			});
		// }
		// else
		// {
		// 	$(t).append('<input type="text" style="width:150px" name="'+name+'" id="'+name+id+'" value="'+value+'" onblur="ajax_sub(this,'+id+',\''+value+'\')" class="modi">');
		// }
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
		$.getJSON('zkjs_range_set.php?ajax=1&action=edit_item',{modi_name:name,modi_value:val,modi_id:id},function(data){
			//alert(data);
			if('1' == data.error){
				val = data.cs;
			}
			$("#"+t_id).parent().text(val).attr("onclick","ajax_modi('"+id+"','"+name+"',this)");
		});
	}
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