//获取校正值 2015-04-20 Mr zhou
function get_jzz(v){
	//滴定管校正值
	var ddg_fw = String($(".bt_modi .ddg_fw").val()).split(',');
	var ddg_jz = String($(".bt_modi .ddg_jz").val()).split(',');
	v = parseFloat(v);
	if(!$.isNumeric(v)) return 0;
	for (var i = 0; i < ddg_fw.length && $.isNumeric(ddg_fw[i]); i++) {
		if(v<=ddg_fw[i]) return ddg_jz[i]
	};
	return 0;
}
//js数组去重
function unique(arr) {
	var res = [], hash = {};
	for(var i=0, elem; (elem = arr[i]) != null; i++){
		if (!hash[elem])
		{
			res.push(elem);
			hash[elem] = true;
		}
	}
	return res;
}
//由于js计算有bug，例如83.9792+83.9793=83.97925。但是在htmljs计算后，得出来的结果是:83.97925000000001为了解决这样的问题，增加了三个函数
/**
 ** 加法函数，用来得到精确的加法结果
 ** 说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
 ** 调用：accAdd(arg1,arg2)
 ** 返回值：arg1加上arg2的精确结果
 **/
function accAdd(arg1, arg2) {
	var r1, r2, m, c;
	try { r1 = arg1.toString().split(".")[1].length;}catch (e) {r1 = 0;}
	try { r2 = arg2.toString().split(".")[1].length;}catch (e) {r2 = 0;}
	c = Math.abs(r1 - r2);
	m = Math.pow(10, Math.max(r1, r2));
	if (c > 0) {
		var cm = Math.pow(10, c);
		if (r1 > r2) {
			arg1 = Number(arg1.toString().replace(".", ""));
			arg2 = Number(arg2.toString().replace(".", "")) * cm;
		} else {
			arg1 = Number(arg1.toString().replace(".", "")) * cm;
			arg2 = Number(arg2.toString().replace(".", ""));
		}
	} else {
		arg1 = Number(arg1.toString().replace(".", ""));
		arg2 = Number(arg2.toString().replace(".", ""));
	}
	return (arg1 + arg2) / m;
}
//给Number类型增加一个add方法，调用起来更加方便。
Number.prototype.add = function (arg) {
	return accAdd(arg, this);
};
/**
 ** 减法函数，用来得到精确的减法结果
 ** 说明：javascript的减法结果会有误差，在两个浮点数相减的时候会比较明显。这个函数返回较为精确的减法结果。
 ** 调用：accSub(arg1,arg2)
 ** 返回值：arg1加上arg2的精确结果
 **/
function accsub(arg1, arg2) {
	var r1, r2, m, n;
	try { r1 = arg1.toString().split(".")[1].length;}catch (e) {r1 = 0;}
	try { r2 = arg2.toString().split(".")[1].length;}catch (e) {r2 = 0;}
	m = Math.pow(10, Math.max(r1, r2)); //last modify by deeka //动态控制精度长度
	n = (r1 >= r2) ? r1 : r2;
	return ((arg1 * m - arg2 * m) / m).toFixed(n);
}
// 给Number类型增加一个mul方法，调用起来更加方便。
Number.prototype.sub = function (arg) {
	return accMul(arg, this);
};
/**
 ** 乘法函数，用来得到精确的乘法结果
 ** 说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。
 ** 调用：accMul(arg1,arg2)
 ** 返回值：arg1乘以 arg2的精确结果
 **/
function accMul(arg1, arg2) {
	var m = 0, s1 = arg1.toString(), s2 = arg2.toString();
	try { m += s1.split(".")[1].length;}catch (e) {}
	try { m += s2.split(".")[1].length;} catch (e) {}
	return Number(s1.replace(".", "")) * Number(s2.replace(".", "")) / Math.pow(10, m);
}
// 给Number类型增加一个mul方法，调用起来更加方便。
Number.prototype.mul = function (arg) {
	return accMul(arg, this);
};
/** 
 ** 除法函数，用来得到精确的除法结果
 ** 说明：javascript的除法结果会有误差，在两个浮点数相除的时候会比较明显。这个函数返回较为精确的除法结果。
 ** 调用：accDiv(arg1,arg2)
 ** 返回值：arg1除以arg2的精确结果
 **/
function accDiv(arg1, arg2) {
	return arg1/arg2;
	var t1 = 0, t2 = 0, r1, r2;
	try { t1 = arg1.toString().split(".")[1].length;}catch (e) {}
	try { t2 = arg2.toString().split(".")[1].length;}catch (e) {}
	with (Math) {
		r1 = Number(arg1.toString().replace(".", ""));
		r2 = Number(arg2.toString().replace(".", ""));
		return (r1 / r2) * pow(10, t2 - t1);
	}
}

//给Number类型增加一个div方法，调用起来更加方便。
Number.prototype.div = function (arg) {
	return accDiv(this, arg);
};
/*
1. 拟舍弃数字的最左一位数字小于5时则舍去，即保留的各位数字不变。
2.拟舍弃数字的最左一位数字大于5；或等于5，而其后跟有并非全部为0的数字时则进一即保留的末位数字加1。（指定“修约间隔”明确时，以指定位数为准。）
3.拟舍弃数字的最左一位数字等于5，而右面无数字或皆为0时，若所保留的末位数字为奇数则进一，为偶数（包含0）则舍弃。
4.四舍六入五单双！
*/
function roundjs(afloat,c)
{
	var x=afloat;
	var y=c;
	x1=accMul(x,Math.pow(10,y))
	x2=Math.round(x1);
	if(Math.floor(x1)%2==0)
	{
		s=x2-x1;
		if(s==0.5)
		{
			x2=x2-1;
		}
	}
	result=accDiv(x2,Math.pow(10,y));
	s=result.toString();
	var rs=s.indexOf('.');
	if(rs<0)
	{
		rs=s.length;
		if(y>0){
			s+='.';
		}
		for(i=0;i<y;i++)
		{
			s+='0';
		}
	}
	else
	{
		r=y-(s.length-rs-1);
		for(i=0;i<r;i++)
		{
			s+='0';
		}
	}
	return s;
}

//四舍五入函数
function round( aFloat, digits ) {
	aFloat = String( Math.round( aFloat * Math.pow( 10, digits ) ) / Math.pow( 10, digits ) );
	temp = aFloat.split( '.' );
	if( temp.length == 1 ){
		aFloat += '.';
		for( i=0; i < digits; i++ ){
			aFloat += '0';
		}
	}else{
		for( i=0; i < digits - temp[1].length; i++ ){
			aFloat += '0';
		}
	}
	return aFloat;
}

function modi( url, text, Defaulttext ) { 
	data = prompt( text, Defaulttext );
	if( data && data != Defaulttext ){
		location.replace( url + data );
	}
}
//#12463
$("input[onclick='_aItem(this)']").dblclick(function(){
	_aItem(this,true);
})
function _aItem(aInput) 
{
	var dblclick = arguments[1] ? true : false;
	if( dblclick == false && (!aInput.name || '' != aInput.value) ){ return false; }
	aSn = aInput.name.split( '[' );
	if( 2 != aSn.length ){ return false; }
	aSn[1] = parseInt( aSn[1].substr( 0, aSn[1].length - 1 ) );
	//默认该name值下的第一个数值
	var mr_v = $(aInput.form).find("input[name^='"+aSn[0]+"[']:first").val();
	var time = new Date().getTime();
	var aItem_html = '<div id="_aItem'+time+'" class="hide">'
		+'<div style="margin:2em;">'
			+'输入数据：<input type="text" name="_aItem" value="" /><br /><br />'
			+'批量输入：<label><input type="checkbox" class="ace ace-switch ace-switch-5" name="add_all" value="1" checked /><span class="lbl"></span></label>'
			+'批量修改：<label><input type="checkbox" class="ace ace-switch ace-switch-5" name="edit_all" value="1" /><span class="lbl"></span></label>'
			//+'允许清空：<label><input type="checkbox" class="ace ace-switch ace-switch-5" name="allow_del" value="1" checked /><span class="lbl"></span></label>'
		+'</div>'
	+'</div>';
	$("body").append(aItem_html);
	var dialog = $( "#_aItem"+time ).removeClass('hide').dialog({
		modal: true,title_html: true,width: 350,
		title: '',
		buttons: [
			{
				text: "确定",
				"class" : "btn btn-primary btn-xs _aItem_ok_button_"+time,
				click: function() {
					var in_val		= $( "#_aItem"+time+" input[name=_aItem]" ).val();
					var add_all		= $( "#_aItem"+time+" input[name=add_all]:checked" ).length;
					var edit_all	= $( "#_aItem"+time+" input[name=edit_all]:checked" ).length;
					var allow_del	= 1;//$( "#_aItem"+time+" input[name=allow_del]:checked" ).length;
					//当前输入框允许修改
					aInput.value = in_val;
					if(1==allow_del || '' != in_val){
						if(add_all==1){
							if( 1 == edit_all){
								$(aInput.form).find("input[name^='"+aSn[0]+"[']").val(in_val);
							}else{
								$(aInput.form).find("input[name^='"+aSn[0]+"[']").each(function(){
									if( '' == $(this).val()){
										$(this).val(in_val);
									}
								});
							}
						}else{
							$(aInput).val(in_val);
						}
					}else{
						$( "#_aItem"+time+" input[name=_aItem]" ).val(mr_v).focus();
						return false;
					}
					$( this ).dialog( "close" );
					$("#_aItem"+time).remove();
				}
			},
			{
				text: "取消","class" : "btn btn-xs",
				click: function() {
					$( this ).dialog( "close" );
					$("#_aItem"+time).remove();
				}
			}
		]
	});
	$(".ui-dialog[aria-describedby=_aItem"+time+"] span.ui-dialog-title").html('<div class="modal-header">批量处理数据</div>');
	//填充默认值，默认选中，监听回车键触发确认按钮
	$( "#_aItem"+time+" input[name=_aItem]" ).val(mr_v).select().keydown(function(e) {
		var keyCode = e.keyCode ? e.keyCode : e.which ? e.which : e.charCode;
		//13 回车键 37左方向键 38上方向键 39右方向键 40下方向键
		if (keyCode == 13){
			$("button._aItem_ok_button_"+time).trigger("click");
		}
	});
}
//
function setdata(name,data)
{
	document.getElementById(name).value=data;
	return false;
}
//v value
function v(id){
	return document.getElementById( id ).value;
}

//e element
function e(id){
	return document.getElementById( id );
}

function write_html(name,msg)
{
	document.getElementById(name).innerHTML=msg;
}

function echoif(msg,iff)
{
	if('a'+iff!='a')
	{
		document.write(msg);
	}
}


function modifram(url,text,Defaulttext)
{
	if(data=prompt(text,Defaulttext))
		if( data != Defaulttext)
		{
			document.getElementById("ifram").contentDocument.location.href=url+data;
		}
		else return false;
}
function gotourl(url)
{
	location.replace(url);
}

function GoTo(url)
{
	location.replace(url);
}

function gotoif(url,msg)
{
	if(url!='')
	{
		if(msg!='')
		{
			if(confirm(msg))
				location.replace(url);
		}
		else location.replace(url);
	}
}

// 用正则表达式将前后空格用空字符串替代。
String.prototype.trim = function() {
	return this.replace( /(^s*)|(s*$)/g, "" );
}
var popUpWin=0;
function popUpWindow(URLStr, left, top, width, height){
	if(popUpWin){
		if(!popUpWin.closed) popUpWin.close();
	}
	popUpWin = open(URLStr,'popUpWin','toolbar=no,location=no,directories=no,status=no,menub ar=no,scrollbar=no,resizable=no,copyhistory=yes,width='+width+',height='+height+',left='+left+',top='+top+',screenX='+left+',screenY='+top+'');
}
function unhide(id)
{
    ele=document.getElementById(id);
    if(ele)
    {
        ele.style.visibility="visible";
        ele.style.display="";
    }
}
function hide(id)
{
    ele=document.getElementById(id);
    if(ele)
    {
        ele.style.visibility="hidden";
        ele.style.display="none";
    }
}
/**
 * 功能：计算位数修约
 * 作者：
 * 日期：2014-10-25
 * 参数：vd0 float 计算的原始结果
 * 参数：wsint 可选参数，保留位数
 * 返回值：vd0 float修约后的位数
 * 功能描述： 此修约并不是最终修约，而是为了避免计算结果的位数过长而设置的统一修约函数
*/
function jsws(vd0){
	var ws	= arguments[1] ? arguments[1]:8;
	if(!$.isNumeric(vd0)){
		return '';
	}else{
		var r=vd0.toString().split('.');
		if(r.length==2&&r[1].length>ws){
			return roundjs(parseFloat(vd0),ws);
		}else{
			return parseFloat(vd0);
		}
	}
}