<?php
include '../temp/config.php';
$sql="SELECT distinct av.value_C,av.id AS vid FROM `xmfa` AS aj JOIN `assay_value` AS av ON aj.xmid=av.id WHERE  aj.fangfa<>0 and aj.act<>0 group by av.id ORDER BY av.id ASC"; 
$av=$DB->query($sql);
$s=1;
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
temp(zhujia.html)
?>
<html>
<form action="" method="post" id='xmform'>
<br/>
<span id="close" style="position: absolute; top: 0px; left: 745px; font-size:18px;">
        <B><a href='javascript:guanbi()'>关闭</a></B>
</span>
<h3 class="header smaller center title">筛选结果批量追加删除检测项目</h3>
<table style='border:1px solid white!important;'>
  <tr style='border:1px solid white!important;'>
    <td colspan="5" align="center" style='border:1px solid white!important;'>
      目前已经选择了 <span id="checked_num1" style='color:blue;'>0</span> 个项目
    </td>
  </tr>
</table>
<table>
 <?php echo $lines;?>
   <tr style='border:1px solid white!important;'>
    <td colspan="5" align="center" style='border:1px solid white!important;'><br/><input class="btn btn-primary" type="button" value="追加" onclick="ticc('zhuijia')">&nbsp;&nbsp;&nbsp;&nbsp;<input class="btn btn-primary"  type="button" value="删除" onclick="ticc('shan')">&nbsp;&nbsp;&nbsp;&nbsp;<input class="btn btn-primary"  type="button" value="关闭" onclick="guanbi()"></td>
  </tr>
</table>
<br/>
</form>
<script> 
  $("input[flag='mb']").each(function(){//把已经选中的项目取消高亮显示
    if($(this).is(":checked")){
      $(this).parent("label").parent("td").css("background-color","#C9F2D1");
      $(this).parent("label").css("background-color","#C9F2D1");
    }
  });

  $("label[flag='mb']").mouseover(function(){//鼠标移动到项目上或者选中的项目，高亮显示
    $(this).parent("td").css({"background-color":"#C9F2D1"});
    $(this).css({"background-color":"#C9F2D1"});
  }).mouseout(function(){
    if(!$(this).children("input").is(":checked")){
      $(this).parent("td").css("background-color","");
      $(this).css("background-color","");
    }
  });

 function tj()
 {
    $("#checked_num1").html($("input[flag='mb']:checked").length);
 }

 $("input[flag='mb']").click(function(){
    tj();
  });

 function ticc(xin)
 {
    //站点处理
    var groups = xms = '';
    ids = $("tr[tjcs]:visible").find('input[group_id][group_name]:checked');
    var num = ids.length;
    for(i=0;i<num;++i){
      groups += ','+$(ids[i]).attr('group_id');
    }
    groups = groups.substr(1);
    //项目处理
    xmids = $("input[flag='mb']:checked");
    var num1 = xmids.length;
    for(j=0;j<num1;++j){
      xms += ','+$(xmids[j]).val();
    }
    xms = xms.substr(1);
    if(num1>0 && num>0){
    	if(xin == 'zhuijia'){
    		$.post("./zhuijia_ajax.php",{action:"zhuijia",groupid:groups,xms:xms},
	          function(data){
	            if(data != 'wrong'){
	            	for(var s in data){
	            		$("span[gr_id='"+s+"']").html(data[s]);
	            	}
	      			alert('追加项目成功！');
	            }else{
	            	alert('项目追加失败！');
	            }
         	},'json'); 
    	}else if(xin == 'shan'){
    		$.post("./zhuijia_ajax.php",{action:"shan",groupid:groups,xms:xms},
	          function(data){
	            if(data != 'wrong'){
	            	for(var s in data){
	            		$("span[gr_id='"+s+"']").html(data[s]);
	            	}
	      			alert('删除项目成功！');
	            }else{
	            	alert('项目追加失败！');
	            }
         	},'json'); 
    	}else{
    		alert('发生未知错误，请联系开发人员');
    	}
      
    }else{
      alert('你没有勾选项目！')
    }  
 }

</script>
</html>
