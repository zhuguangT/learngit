/*<script language="javascript" src="../js/lims/jsgs/jsgs.js"></script>
<script language="javascript" src="../js/lims/jsgs/GBT_5750.5-2006.js"></script>*/

var GBT_5750_5_2006 = {
	m9_1 :function(){
		//运算函数在lib.js
      var vid = '';
      var sc_unit = 'mg/L';
      if(sc_unit.split('/').length == 2 || sc_unit=='度'){
        var qy_v = 1;
      }else{
        var qy_v = parseFloat($("input[name=td9]").val());
      }
      var vd3_split = String(vs['vd3']).split('.');
      if(vd3_split.length==2){
        var diding_ws = vd3_split[1].length;
      }else{
        var diding_ws = 2;
      }
      if( $.isNumeric(vs['vd3']) && $.isNumeric(vs['vd4']) ){
        var vd3 = parseFloat(vs['vd3']);//E₀
        var vd4 = parseFloat(vs['vd4']);//E
        var vd2 = parseFloat(parseFloat($("input[name=td9]").val())/vs['vd1']);//稀释倍数
        var gmd = vs['vd5'] = roundjs(accsub(vd4,vd3),diding_ws+1);//获取回归方程中Y的值，即(A)-(A0)的值
        var CA  = parseFloat($('#qa').val());
        var CB  = parseFloat($('#qb').val());
        var han1  = accMul(gmd,CB); //计算公式
        var han2  = accAdd(han1,CA);
        var jg    = parseFloat(accMul(han2,vd2));
        if(vid=='108'){
          jg *= vs['vd1']/$("input[name=td9]").val();
        }else if(vid=='217'){
          jg *= 1.56;
          var shj_gs = '水合肼 ρ（N₂H₄·H₂O）= m×1.56/V';
          var td30 = $("textarea").val();
          $("textarea").val(td30.replace(shj_gs,'')+shj_gs);
        }else if(vid=='380' && '-4'!=vs['vd6']){
          //苯胺  因检测方法特殊需要蒸馏，所以需要在最终结果里乘以2倍的稀释倍数,但是单点标液是不经过这种处理的， 所以不需要乘2
          jg *= 2;
        }else if(vid=='617'){
          //做单点标液时取的是体积而不是质量，所以做法和普通样品有点区别，需要除以定容体积得出浓度，这时是mg/mL的浓度，需要转换单位为mg/L
          if('-4'==vs['vd6']){
            qy_v = parseFloat($("input[name=td9]").val());
            jg = jg*1000;
          }else{
            qy_v = 1;
            //聚合氯化铝项目：砷（As）质量分数
            var m  = jg;//从校准曲线查出的砷的质量的数值，单位为毫克(mg)
            var m0 = 10;//试料的质量的数值，单位为克(g)
            jg = ((m/1000)/(m0*10/100))*100;
            var As_gs = '砷（As）质量分数 ω₆ = [(m×10⁻³/(m₀×10/100))]×100';
            var td30 = $("textarea").val();
            $("textarea").val(td30.replace(As_gs,'')+As_gs);
          }
        }else if(vid=='619'){
          //做单点标液时取的是体积而不是质量，所以做法和普通样品有点区别，需要除以定容体积得出浓度，这时是mg/mL的浓度，需要转换单位为mg/L
          if('-4'==vs['vd6']){
            qy_v = parseFloat($("input[name=td9]").val());
            jg = jg*1000;
          }else{
            qy_v = 1;
            //聚合氯化铝项目：汞（Hg）质量分数
            var m  = jg;//从校准曲线查出的汞的质量的数值，单位为毫克(mg)
            var m0 = 25;//试料的质量的数值，单位为克(g)
            jg = (m/1000/m0)*100;
            var Hg_gs = '汞（Hg）质量分数 ω₉ = [(m×10⁻³)/m₀]×100';
            var td30 = $("textarea").val();
            $("textarea").val(td30.replace(Hg_gs,'')+Hg_gs);
          }
        }
        vs['vd0'] = jsws(accDiv(jg,qy_v));
      }else{
        vs['vd5']=vs['vd0']='';
      }
	}
};