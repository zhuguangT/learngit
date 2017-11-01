var GBT_5750_7_2006 = {
	m295_1_1 : function(){
		var v2    = parseFloat($("#kv").val());
		var k		  = 10/parseFloat(v2);
		var kb    = parseFloat($("#kb").val());
		var mr_v  = parseFloat($("input[name=td9]").val());
		if(''==$("#c_v").val()){
			$("#c_v").val('0.01');
		}
		var C   = parseFloat($("#c_v").val());
		mr_v = $.isNumeric(mr_v) ? mr_v : 100;
		//默认体积与实际取样体积比较判断是否稀释
		if(mr_v!=vs['vd1'] && 0!=kb&&(''==kb || !$.isNumeric(kb))){
			$("#kb").focus(); return false;
		}
		if(''==k || !$.isNumeric(k)){
			$("#kv").focus(); return false;
		}
		var vd3_split = String(vs['vd3']).split('.');
		if(vd3_split.length==2){
			var diding_ws = vd3_split[1].length;
		}else{
			var diding_ws = 2;
		}
		if($.isNumeric(vs['vd3'])&&$.isNumeric(vs['vd4'])){
			vs['vd5'] = roundjs(accsub(parseFloat(vs['vd4']),parseFloat(vs['vd3'])),diding_ws);
		}else{
			vs['vd5'] = '';
		}
		if(''!==vs['vd5']){
			//计算公式
			//默认体积与实际取样体积比较判断是否稀释
			// C 草酸钠标准溶液 0.010mol/L
			//系数   k*C*8000/v3
			var jjz = get_jzz(vs['vd4']);//获取校正值
			//填充表头中校正值
			var result =[];
			$("input[name*='vd4[']").each(function(i){
			  result.push(get_jzz($(this).val()));
			})
			result = unique(result);
			$("#ddg_jzz").val(result.join(','));
			if(mr_v==vs['vd1']){
			  //不稀释 (10+v1)*k*C*8000/v3
			  var xishu = 0.8;//parseFloat(k*8000*C/parseFloat(vs['vd1']));
			  vs['vd0'] = jsws(((10+parseFloat(parseFloat(vs['vd5'])+parseFloat(jjz)))*k-10)*xishu);
			}else{
			  //稀释 {[(10+v1)*k-10]-[(10+v0)*k-10]*f}*C*8000/V3
			  var xishu = parseFloat(8000*C/parseFloat(vs['vd1']));
			  var f   = (mr_v-parseFloat(vs['vd1']))/mr_v;
			  var p1  = (10+parseFloat(parseFloat(vs['vd5'])+parseFloat(jjz)))*k-10;
			  var p2  = ((10+kb)*k-10)*f;
			  var p3  = (p1-p2)*xishu;
			  vs['vd0'] = jsws(p3);
			}
		}
	}
}