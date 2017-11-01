$(function(){
	//操作和提示区域随着页面的变化而浮动或者紧邻表格
	var fudong	= $("#float-button").offset();
	var main_height	= document.documentElement.clientHeight;
		//操作区域的位置大于浏览器高度是就浮动起来
	if(fudong.top >= (main_height - 60)){
		$("#float-button").css({'position':'fixed','width':'100%','bottom':'0','padding-right':'23px'});
	}
	//标签数量选择
	$("#sel").change(function(){
		var sel_val	= $(this).val();
		var old_val	= $(this).attr('old_val');
		$(this).attr("old_val",sel_val);
		if(sel_val==''){
			$("select.bh_sum").each(function(){
				var bh_old_val	= $(this).attr("old_val");
				$(this).val(bh_old_val);
			});
		}else{
			if(old_val==''){
				//$("select.bh_sum").val(sel_val);
				$("select.bh_sum").each(function(){
					var bh_old_val	= $(this).attr("old_val");
					if(this.value==bh_old_val){
						$(this).val(sel_val);
					}
				});
			}else{
				//$("select.bh_sum[value='"+old_val+"'].val(sel_val);
				$("select.bh_sum option:selected[value='"+old_val+"']").parent("select").val(sel_val);
			}
		}
	});
	//打印标签
	$("input[type='submit']").click(function(){
    var clicked = $(this).attr("clicked");
    if(clicked=='yes'){
      alert('已经在连接打印机，请勿重复操作！');
      return false;
    }
    var loading_html  = $("#loading_html").html();
		var html	= "正在连接打印机"+loading_html;
		$("#content_tixing").html(html).css({"background-color":"#89C9F7","cursor":"unset"}).attr("can_click","no").show();
    $(this).attr("clicked",'yes');
		//获取POST信息
		var options = {
			//type:'post',
			dataType: "json",
			success:function(result){
				if(result.jieGuo == 'yes'){
					var color	= "#8EFA9D";
				}else{
					var color	= "#F96D3B";
				}
				$("#content_tixing").html(result.html).css({"background-color":color,"cursor":"pointer"}).attr("can_click","yes");
        $("input[type='submit']").attr("clicked",'');
			},
			error:function(error){ 
				$("#content_tixing").html('打印机连接失败！请检查打印机及打印服务器的网线连接是否正常!').css({"background-color":"#F96D3B","cursor":"pointer"}).attr("can_click","yes");
        $("input[type='submit']").attr("clicked",'');
			},
			timeout:10000
		};
		$("form").ajaxSubmit(options);//ajax提交
		return false;
	});
	//提示区域点击后可隐藏
	$("#content_tixing").click(function(){
		var can_click	= $(this).attr("can_click");
		if(can_click == 'yes'){
			$(this).hide();
		}
	});
});
function gt(va){
  //如果是第二次打开，先清除错误提示
  $("#yztishi").html("");
  //获取信息
  $("#newbar").val($(va).html());
  var zong = $("tr[tongji='tong']").length;
  var op = '';
  xian = $(va).attr('shunxu');
  $("#newbar").attr('xian',xian);
  yu = zong-xian;
  for(i=0;i<=yu;++i){
    op += '<option>'+i+'</option>';
  }
  $('#jia').html(op);
  $("#cover").show();
}
function guanbi(){
  $("#cover").hide();
}
function fa(){
  n = $('#jia').val();//获取顺延几个
  xian = $("#newbar").attr('xian');//获取现在是第几个
  newbar = $("#newbar").val();//获取新的编号
 // if(!newbar.match(/[A-Z]{2}\d{6}-\d{4}/)){
  if(!newbar.match(/[A-Z]{2}\d{6}\d{4}/)){
      alert("样品编号格式有误！");
      return false;
  }
  cid = $("td[shunxu="+xian+"]").attr('cid');
  yuan = $("td[shunxu="+xian+"]").html();
  $("td[shunxu="+xian+"]").html(newbar);
  quan = new Array();
  quan[0] = 'a:'+yuan+'.'+newbar+'.'+cid;
  if(n != 0){
    for(i=1;i<=n;++i){
      qu = Number(xian)+Number(i);//现在的个数加上i，就是依次顺延的
      ibar = xiucode(newbar,i);
      quan[i] = i+':';
      quan[i] += $("td[shunxu="+qu+"]").html()+'.';
      quan[i] += ibar+'.';//每个顺延的对应的新编号
      quan[i] += $("td[shunxu="+qu+"]").attr('cid');
      // if(quan[i] != 'undefined'){
      //   quanstr += ','+quan[i];
      // }
      $("td[shunxu="+qu+"]").html(ibar);
    }
  }
  console.log(quan);
  $("td[cid]").parent().find("font[color]").html('');
  for(var ss in quan){
    $.post("modi_ypbh_ajax.php?ajax=1",{quanstr:quan[ss]},function(data){
      if(data !='ok'){
        for(var s in data){
          da = data[s].split(",");
          $("td[cid="+s+"]").html(da[1]);
          $("td[cid="+s+"]").parent().find("font[color]").html('样品编号'+da[0]+'已存在!');
        }
      }
    },'json');
  }
  guanbi();
}
function xiucode(code,i){
        liu = code.split('-');
        ibar = Number(liu[1])+Number(i);
        console.log(String(ibar).length);
        aa = 4-String(ibar).length;
        if(aa>0){
                for(j=1;j<=aa;j++){
                        ibar = "0"+String(ibar);
                }
        }
        code = liu[0]+'-'+ibar;
        return code;
}
function yzcode(va){
  var code = va.value;
  //if(!code.match(/^[A-Z]{2}\d{6}-\d{4}$/)){
     if(!code.match(/^[A-Z]{2}\d{6}\d{4}$/)){
      $(va).css('color','red');
      $("#yztishi").html("<font color='red'>样品编号格式有误！</font>");
  }else{
      $(va).css('color','');
      $("#yztishi").html("");
  }
}