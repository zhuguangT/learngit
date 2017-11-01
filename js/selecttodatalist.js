//此文件作用  为将select下拉列表 改成 可输入文字搜索的下拉列表  详细登陆 项目管理 任务#3907
//页面加载完毕执行
$(function(){
  select_tolist();
});
//查找select 更加情况 进行 转换
function select_tolist(str){
	var str = arguments[0] ? arguments[0] : 'old';
	//声明一个全局变量用于记录焦点是否是在input 和select 之间切换
	Q_select_show=true;
	yuanVal = '';//全局变量  记录最原始的 select默认值
	if(str=='old'){$("select.inputSelect").each(function(i,v) {
		var optionsum=$(v).children().length;
		if(optionsum<20)return null;
		var position = $(v).position(),swidth=$(v).css("width");
		var objpos={ "position":"absolute","top":position.top+100,"left":position.left,"min-width":swidth};
		$(v).css(objpos).hide().attr('size',20).attr("data-i",i).addClass("select_hide").prepend("<optgroup></optgroup>");
		$('<input type="text" style="position:static;padding-right:15px;cursor:pointer;width:'+swidth+'" class="selecttolist"  data-i='+i+' value="'+$(v).find("option:selected").text()+'"/><label class="labelFuHao" style="cursor:pointer;margin-left: -15px;">&nabla;</label>').insertBefore(v);
	});
	}
 	//监听 input 文本变化
	$(".labelFuHao").click(function(){
		$(this).prev("input.selecttolist").focus();
	});
	$(".selecttolist").bind({
		"keyup":function(e){
			select_show($(this).attr('data-i'),this.value,this);
		},
		"blur":function(){
			//setTimeout("select_hide("+$(this).attr('data-i')+")",0);
			if(Q_select_show==true){
				//alert('feng');
				select_hide($(this).attr('data-i'));
			}
		},
		"focus":function(){
			var v=$.trim(this.value);
			if(v=='全部')$(this).val(''),v='';
			if(!yuanVal)yuanVal = v;
			select_show($(this).attr('data-i'),v,this);
		}
	});
	$(".select_hide").mouseover(function(){
		Q_select_show=false;
	}).mouseout(function(){
		Q_select_show=true;
	});
	//监听隐藏的select选中事件
	$(".select_hide").click(function() {
		var i=$(this).attr('data-i');
		var selected = $(this).find("option:selected").text();
		var option1  = this.options[0].text;
		if(selected!=yuanVal&&selected==option1)$(this).change();
		$(".selecttolist[data-i="+i+"]").val(selected);//$(this).find("option:selected").text());
		yuanVal = selected;//$(this).find("option:selected").text();
		select_hide(i);
	});
	$('.select_hide').blur(function(){
		$(this).hide();
	});
}
//显示下列选项 给出I 这个页面的第几个 特殊下列列表的序号，v是查询字符
function select_show(i,v,d){
	var domid    = $(".select_hide[data-i="+i+"]");
	var selectPosition = $(d).position();
        var css = {"left":selectPosition.left};
	domid.css(css).show().find("optgroup").html('');
	var selectSize = domid.children(":visible").length;
	if(selectSize>20)selectSize=20;
	domid.attr('size',selectSize);
    	v=$.trim(v);
	var addopt=thisop=null;
	if(v=='全部' ||v=='')
		return null;
	else{
		domid.find("option:visible:contains("+v+")").each(function() {//搜索出所有含有v文字的option
			thisop=$(this);
			addopt+="<option value='"+thisop.val()+"'>"+thisop.text()+"</option>";//加到optgroup中
		});
	}
	domid.find("optgroup").html(addopt);
	var selectSize = $(".select_hide[data-i="+i+"] option:visible").length;
	if(selectSize>20)selectSize=20;
	domid.attr('size',selectSize).show();
	domid[0].options[0].selected = true;//显示到下拉列表的最上面
	
}
//隐藏下列选项
function select_hide(i){
	var thisSelect = $(".select_hide[data-i="+i+"]");
	thisSelect.hide();
	//yuanVal = $(".select_hide[data-i="+i+"] option:selected").text();
	thisSelect[0].options[0].selected = true;
	var v = $(".selecttolist[data-i="+i+"]").val();
	if(v==''&&yuanVal=='')$(".selecttolist[data-i="+i+"]").val(thisSelect[0].options[0].text);
	else if(v==''&&yuanVal!='')$(".selecttolist[data-i="+i+"]").val(yuanVal);
}
