/*
 * 文件名：zhikong.js
 * 功能：化验单质控操作
 * 作者: Mr Zhou
 * 日期: 2015-05-29
 * 描述：实现对化验单添加删除，室内平行，添加删除修改加标，空白等质控操作
*/
(function($) {
	$.fn.extend({
		zhikong : function(tid,options) {
		$.fn.zhikong.set = $.extend({}, $.fn.zhikong.set, options);
		$.fn.get_span = function(orid){
			return $(this).find(".hydzk[data-orid='"+orid+"']");
		}
		$(this).each(function(){
			$(this).zhikong_init(tid,$.fn.zhikong.set);
		});
		},
		get_zk_button : function(action){
			var zky_name = $.fn.zhikong.set.zky_name;
			var zk_title = {
				'2':'添加空白','-2':'删除空白','22':'修改空白',
				'4':'添加'+zky_name,'-4':'删除'+zky_name,'44':'修改'+zky_name,
				'7':'添加常规平行样','-7':'删除常规平行样',
                '8':'添加0.2C和0.8C','-8':'删除0.2C和0.8C','88':'修改0.2C和0.8C',
				'20':'添加平行','-20':'删除平行',
				'40':'添加加标','-40':'删除加标','4040':'修改加标'
			}
            if( !$.fn.zhikong.set['has_zk7'] ){
                delete(zk_title['7']);
            }
            if( !$.fn.zhikong.set['02C08C'] ){
                delete(zk_title['8']);
            }
            $.fn.zhikong.set.zk_title = zk_title;
			var current_form = $.fn.zhikong.current_form;
			return ( undefined == zk_title[action] ) ? '' : 
				'<button type="button" class="order_button" data-action="'+action+'" data-tid="'+current_form.tid+'" data-orid="'+$(this).attr("data-orid")+'">'+zk_title[action]+'</button>';
		},
		zhikong_init : function(tid,o){
			o.tid	= tid;
			o.form	= $(this);
			$.fn.zhikong.current_form = o;
			//如果找不到flag标识则不进行质控初始化
			if(!o.form.find(".hydzk[data-flag]").length){
				return false;
			}
			var has_zk7 = (o.form.find("input[name='has_zk7']").length) ? true : false;
			//在页面添加两个div存放 分别存放加标和自控样所需数据
			o.form.append('<div style="display:none" id="jiaBiaoData_'+o.tid+'">,,</div><div style="display:none" id="ziKong_'+o.tid+'">,,</div>');
			//质控操作按钮
			{
				o.form.find(".hydzk").each(function(action){
					this.id			= $(this).attr("data-orid");	//assay_order表id
					this.flag		= $(this).attr("data-flag");	//质控标识
					this.code 		= $(this).attr("data-code");	//样品编号
					this.tit=titleXc=weizhi=title2=weizhi1=weizhi2=titleH='';
					switch(this.flag)
					{
						//没有做质控的原样
						case '0':/*正常原样*/
						case '1':/*全程空白*/
						case '3':/*质控样*/
						case '5':/*现场平行A样*/
						case '-6':/*现场平行B样*/
						{
							tit  = $(this).get_zk_button('20');//添加平行
							tit += $(this).get_zk_button('40');//添加加标
							tit += $(this).get_zk_button( '2');//添加空白
							tit += $(this).get_zk_button( '4');//添加自控样
							tit += $(this).get_zk_button( '8');//添加02C08C
							if(true == has_zk7){
								tit += $(this).get_zk_button('7');
							}
						}break;
						case '-7':/*常规平行样（不同稀释倍数的统一样品）*/
						{
							tit  = $(this).get_zk_button( '7');//删除常规平行样
							tit += $(this).get_zk_button('-7');//删除常规平行样
						}break;
						//做了室内平行的原样
						case '20':/*正常样做室内平行*/
						case '21':/*全程空白做室内平行*/
						case '23':/*质控样做室内平行*/
						case '25':/*现场平行A样做室内平行*/
						{
							tit  = $(this).get_zk_button('-20');//删除平行
							tit += $(this).get_zk_button( '40');//添加加标
							tit += $(this).get_zk_button(  '2');//添加空白
							tit += $(this).get_zk_button(  '4');//添加自控样
							tit += $(this).get_zk_button( '8');//添加02C08C
							if(true == has_zk7){
								tit += $(this).get_zk_button('7');
							}
						}break;
						//室内平行样
						case '-20':/*正常样之室内平行样*/
						case '-26':/*现场平行B样之室内平行*/
						{
							tit  = $(this).get_zk_button('-20');//删除平行
							// tit += $(this).get_zk_button( '40');//添加加标
							if(true == has_zk7){
								tit += $(this).get_zk_button('7');
							}
							//现场平行B样之室内平行
							if('-26' == this.flag)
							{
								//现场平行B样得判断是否做了室内平行
								//这个是现场平行B样 做了室内平行 将原样上添加平行的按钮替换为删除平行的按钮
								if(this.code.indexOf('P')>0)
								{
									var code  = this.code.replace('P','');//去除编号后面的“P”号
									//P_B 是指现场平行B样
									var P_B_obj	= o.form.find(".hydzk[data-code='"+code+"'][data-flag='-6']");//获取当前的id
									var titleXc	= P_B_obj.attr('title');//获取当前的title
									if(''!=titleXc)
									{
										var P_B_tit  = P_B_obj.get_zk_button('-20');//删除平行
										var weizhi 	 = titleXc.indexOf('</button>')+9;//9是向后移动 字符“</button>”所占的位置
										var title2   = P_B_tit+titleXc.substring(weizhi);//
										P_B_obj.attr('title',title2);//属性定义
									}
								}
							}
						}break;
						//做了加标的原样
						case '40':/*正常样做加标*/
						case '41':/*全程空白做加标*/
						case '43':/*质控样做加标*/
						case '45':/*现场平行A样做加标*/
						{
							tit  = $(this).get_zk_button( '20');//添加平行
							tit += $(this).get_zk_button('-40');//删除加标
							tit += $(this).get_zk_button(  '2');//添加空白
						}break;
						//加标样
						case '-40':/*正常样之加标样*/
						case '-42':/*室内空白之加标样*/
						case '-46':/*现场平行B样之加标样*/
						case '-60':/*室内平行B之加标样*/
						case '-66':/*现场平行B样室内平行B样之加标样*/
						{
							this.vd28	= $(this).attr("data-vd28") ? $(this).attr("data-vd28") : '';	//水体积	vd28
							this.vd29	= $(this).attr("data-vd29") ? $(this).attr("data-vd29") : '';	//标样		vd29
							this.vd30	= $(this).attr("data-vd30") ? $(this).attr("data-vd30") : '';	//加标量	vd30
							this.vd31	= $(this).attr("data-vd31") ? $(this).attr("data-vd31") : '';	//标样单位	vd31
							this.vd32	= $(this).attr("data-vd32") ? $(this).attr("data-vd32") : '';	//加标量单位vd32
							$("#jiaBiaoData_"+this.tid).html(this.vd28+','+this.vd29+','+this.vd30);	//加入默认值

							tit  = $(this).get_zk_button( '-40');//删除加标
							tit += $(this).get_zk_button('4040');//修改加标
							//tit += '原水样体积：'+this.vd28+'mL，&nbsp;标液浓度：'+this.vd29+this.vd31+'，&nbsp;加标量：'+this.vd30+this.vd32+'&nbsp;';
							//
							var code  = this.code.substring(0,this.code.length-1);
							if('-46' == this.flag){
								//现场平行B样之加标样，需要将现场平行B样(-6)的'添加加标'按钮改为'删除加标'
								var hy_flag	= -6;
							}else if('-60' == this.flag){
								//室内平行B样之加标样，需要将室内平行B样(-20)的'添加加标'按钮改为'删除加标'
								var hy_flag	= -20;
							}else if('-66' == this.flag){
								//现场平行B样之室内平行B样的加标样，需要将现场平行B样之室内平行B样(-26)的'添加加标'按钮改为'删除加标'
								var hy_flag	= -26;
							}else{
								var hy_flag = o.form.find(".hydzk[data-code='"+this.code+"']").attr('data-flag');
							}
							var titleXc  = o.form.find(".hydzk[data-code='"+this.code+"'][data-flag='"+hy_flag+"']").attr('title');
							if(titleXc){
								var titleTemp= titleXc.split('</button>');
								var title2   = titleTemp[0]+'</button>'+ $(this).get_zk_button('-40');
								for(var i=2;i<titleTemp.length;i++){
									title2   += titleTemp[i]+'</button>';
								}
								o.form.find(".hydzk[data-code='"+this.code+"']").attr('title',title2);
							}
						}break;
						//室内平行和加标都做的原样
						case '60':/*正常样做室内平行+加标*/
						case '61':/*全程空白做室内平行+加标*/
						case '63':/*质控样做室内平行+加标*/
						case '65':/*现场平行A样做室内平行+加标*/
						{
							tit  = $(this).get_zk_button('-20');	//删除平行
							tit += $(this).get_zk_button('-40');	//删除加标
							tit += $(this).get_zk_button(  '2');	//添加空白
							tit += $(this).get_zk_button(  '4');	//添加自控样
							tit += $(this).get_zk_button(  '8');	//添加02C08C
						}break;
						case '-2':		//室内空白
						{
							this.vd28	= $(this).attr("data-vd28") ? $(this).attr("data-vd28") : '';	//信号值	vd28
							tit  = $(this).get_zk_button('-2');	//删除空白
							tit += $(this).get_zk_button('22');	//修改空白
							tit += $(this).get_zk_button('40');	//添加加标
							//tit += '信号值：'+this.vd28
						}break;
						case '-4':
						case '-8':
							this.vd28	= $(this).attr("data-vd28") ? $(this).attr("data-vd28") : '';	//批号			vd28
							this.vd29	= $(this).attr("data-vd29") ? $(this).attr("data-vd29") : '';	//标准值		vd29
							this.vd30	= $(this).attr("data-vd30") ? $(this).attr("data-vd30") : '';	//不确定度		vd30
							this.vd31	= $(this).attr("data-vd31") ? $(this).attr("data-vd31") : '';	//标液单位		vd31
							this.vd32	= $(this).attr("data-vd32") ? $(this).attr("data-vd32") : '';	//不确定度单位	vd32
							$("#ziKong_"+this.tid).html(this.vd28+','+this.vd29+','+this.vd30);			//加入默认值
                            if( '-4' == this.flag ){
                                tit   = $(this).get_zk_button('-4'); //删除自控样
                                tit  += $(this).get_zk_button('44'); //修改自控样
                            }else{
                                tit   = $(this).get_zk_button('-8'); //删除0.2C0.8C
                                tit  += $(this).get_zk_button('88'); //修改0.2C0.8C
                            }
							break;
						default:
							tit  = '样品标识有误';
					}
					$(this).attr('title',tit);
				});
				o.form.find(".hydzk").zkTooltip().trigger("mouseover").trigger("mouseout");
			}//End

			//数据留舍
			if(true == has_zk7){
				var code = '';
				var bh_arr = new Array();
				o.form.find('span[data-flag=-7].hydzk').each(function(code){
					if(code != $(this).html()){
						var code = $(this).html();
						bh_arr[bh_arr.length] = code;
					}
				});
				for (var i = 0; i < bh_arr.length; i++) {
					o.form.find('span[data-code='+bh_arr[i]+'].hydzk').each(function(){
						var data_orid = $(this).attr('data-orid');
						var checked = (1==$(this).attr('data-reli'))?'checked':'';
						$(this).html(bh_arr[i]+'<input name="reliable['+data_orid+']" '+checked+' class="ace ace-switch ace-switch-8" type="checkbox" value="'+data_orid+'" /><span class="lbl"></span>');
					})
				}
			}//End
			//为质控按钮绑定事件
			//普通质控操作
			var action_select = [
				".order_button[data-action='-2' ]",	//删除空白
				".order_button[data-action='-4' ]",	//删除自控样
				".order_button[data-action='7'  ]",	//添加常规平行样
				".order_button[data-action='-7' ]",	//删除常规平行样
                ".order_button[data-action='-8' ]", //删除0.2C和0.8C
				".order_button[data-action='20' ]",	//添加平行
				".order_button[data-action='-20']",	//删除平行
				".order_button[data-action='-40']"];//删除加标
			$(action_select.join(",")).click(function(){
				var action = $(this).attr('data-action');
				//如果type小于1证明执行的是删除操作 做删除提示
				if(action < 1 && !confirm("确定要删除吗？"))
					return false;
				var orid = $(this).attr('data-orid');
				var flag = o.form.find(".hydzk[data-orid='"+orid+"']").attr('data-flag');
				var action_url = './zhikong.php?id='+orid+'&action='+action+'&flag='+flag;
				o.form.find("input[name='goto_url']").val(action_url);
				o.form.find("form[name='as_form_"+o.tid+"']").submit();
			});
			//空白的添加及修改
			$(".order_button[data-action='2'],.order_button[data-action='22']").click(function(){
				var orid	= $(this).attr('data-orid');
				var action	= $(this).attr('data-action');
				var span_o	= o.form.get_span(orid);
				var flag	= span_o.attr('data-flag');
				var xhz		= span_o.attr('data-vd28');
				var data_type = "kongbai";
				var modal = $("#modal_zhikong");
				modal.find(".modal-body").hide();
				modal.find("[data-type='"+data_type+"']").show();
				modal.find(".zhikong_title").html("添加空白");
				if(''!=xhz&&undefined!=xhz){
					modal.find("[name='xhz']").val(xhz);
				}
				modal.find("[name='tid']").val(o.tid);
				modal.find("[name='action']").val(action);
				modal.find("[name='data-orid']").val(orid);
				modal.find("[name='data-type']").val(data_type);
				modal.modal("show");
				setTimeout('$("#modal_zhikong [data-type='+data_type+'] [type=text]").eq(0).select()',500);
				setTimeout('$("#modal_zhikong").enter_next_input({find:"[data-type='+data_type+'] [type=text]"})',500);
			})
			//自控样添加及修改
			$( [".order_button[data-action='4']",
                ".order_button[data-action='44']",
                ".order_button[data-action='8']",
                ".order_button[data-action='88']"
                ].join(",")).click(function(){
				var orid	= $(this).attr('data-orid');
				var action	= $(this).attr('data-action');
				var span_o	= o.form.find(".hydzk[data-orid='"+orid+"']");
				var flag	= span_o.attr('data-flag');
				var piHao	= span_o.attr('data-vd28');
				var bzz		= span_o.attr('data-vd29');
				var bqdd	= span_o.attr('data-vd30');
				var bzzdw	= span_o.attr('data-vd31');
				var bqdddw	= span_o.attr('data-vd32');

				var data_type = "zky";
				var modal = $("#modal_zhikong");
				var bar_code = o.form.find("span[data-orid='"+orid+"']").html();
				modal.find(".modal-body").hide();
				modal.find("[data-type='"+data_type+"']").show();
				modal.find(".zky_addNum,.zky_editAll").addClass("hide");
				if($.inArray(action, ['44', '88']) >= 0){
					modal.find("[name='piHao']").val(piHao);
					modal.find("[name='bzz']").val(bzz);
					modal.find("[name='bqdd']").val(bqdd);
                    if('44' == action){
                        modal.find(".zky_editAll").removeClass("hide");
                    }
					modal.find(".zhikong_title").html(bar_code+"修改");
					modal.find("[name='bzzdw'] [value='"+bzzdw+"']").attr('selected',true);
					modal.find("[name='bqdddw'] [value='"+bqdddw+"']").attr('selected',true);
				}else if(orid!=modal.find("[name='data-orid']").val()){
					var zky_data = $("#ziKong_"+o.tid).html().split(',');
					modal.find("[name='piHao']").val(zky_data[0]);
					modal.find("[name='bzz']").val(zky_data[1]);
					modal.find("[name='bqdd']").val(zky_data[2]);
				}
				if($.inArray(action, ['4', '8']) >= 0){
                    // 自控样执行批量添加，0.2C和0.8C不执行批量操作
                    if('4' == action){
                        modal.find(".zky_addNum").removeClass("hide");
                    }
					modal.find(".zhikong_title").html($.fn.zhikong.set.zk_title[action]);
				}
				modal.find("[name='flag']").val(flag);
				modal.find("[name='tid']").val(o.tid);
				modal.find("[name='action']").val(action);
				modal.find("[name='data-orid']").val(orid);
				modal.find("[name='data-type']").val(data_type);
				modal.modal("show");
				setTimeout('$("#modal_zhikong [data-type='+data_type+'] [type=text]").eq(0).select()',500);
				setTimeout('$("#modal_zhikong").enter_next_input({find:"[data-type='+data_type+'] [type=text]"})',500);
			})
			//加标的添加及修改
			$(".order_button[data-action='40'],.order_button[data-action='4040']").click(function(){
				var orid	= $(this).attr('data-orid');
				var action	= $(this).attr('data-action');
				var span_o	= o.form.find(".hydzk[data-orid='"+orid+"']");
				var flag	= span_o.attr('data-flag');
				var qyv		= span_o.attr('data-vd28');
				var byc		= span_o.attr('data-vd29');
				var byv		= span_o.attr('data-vd30');
				var bycdw	= span_o.attr('data-vd31');
				var byvdw	= span_o.attr('data-vd32');

				var data_type = "jiabiao";
				var modal = $("#modal_zhikong");
				var bar_code = o.form.find("span[data-orid='"+orid+"']").html();
				modal.find(".modal-body").hide();
				modal.find("[data-type='"+data_type+"']").show();
				if('4040'==$(this).attr('data-action')){
					modal.find("[name='qyv']").val(qyv);
					modal.find("[name='byc']").val(byc);
					modal.find("[name='byv']").val(byv);
					modal.find(".zhikong_title").html(bar_code+"修改");
					modal.find("[name='bycdw'] [value='"+bycdw+"']").attr('selected',true);
					modal.find("[name='byvdw'] [value='"+byvdw+"']").attr('selected',true);
				}else if(orid!=modal.find("[name='data-orid']").val()){
					var jia_data = $("#jiaBiaoData_"+o.tid).html().split(',');
					modal.find("[name='qyv']").val(jia_data[0]);
					modal.find("[name='byc']").val(jia_data[1]);
					modal.find("[name='byv']").val(jia_data[2]);
				}
				if('40'==$(this).attr('data-action')){
					modal.find(".zhikong_title").html(bar_code+"添加加标");
				}
				modal.find("[name='flag']").val(flag);
				modal.find("[name='tid']").val(o.tid);
				modal.find("[name='action']").val(action);
				modal.find("[name='data-orid']").val(orid);
				modal.find("[name='data-type']").val(data_type);
				modal.modal("show");
				setTimeout('$("#modal_zhikong [data-type='+data_type+'] [type=text]").eq(0).select()',500);
				setTimeout('$("#modal_zhikong").enter_next_input({find:"[data-type='+data_type+'] [type=text]"})',500);
			})
			//分光法(在表格中存在截距和斜率的化验单)的自动增加自控样
			var sc_ca = o.form.find("input[name='CA']").length;
			var sc_cb = o.form.find("input[name='CB']").length;
			if(true==o.sc_need_zky&&1==sc_ca&&1==sc_cb){
				//是否添加了自控样
				if( !o.form.find("span.hydzk[data-flag='-4']").length ) {
					$(".order_button[data-action='4']").trigger("click");
				}
			}
		}
	});
	$.fn.zhikong.set = {
		zky_name : "自控样",	//自控样|单点标液
		sc_need_zky : false,	//分光法是否需要自控样
	};
})(jQuery);

//质控操作弹窗
$(document).ready(function(){
	$("#zhikong_modal_submit").click(function(){
		var empty_error = false;
		var modal = $("#modal_zhikong");
		var tid = modal.find("[name='tid']").val();
		modal.i=function(name){
			return $(this).find("[name='"+name+"']");
		};
		modal.v=function(name){
			return $(this).find("[name='"+name+"']").val();
		};
		var FormError = modal.find(".ui-state-error");
		var data_type = modal.i("data-type").val();
		var action_url = './zhikong.php?id='+modal.v("data-orid")+'&action='+modal.v('action');
		if('kongbai'==data_type){
			if('NULL'==modal.v("xhz")){
				empty_error = true;
				modal.i("xhz").select();
				FormError.html(modal.i("xhz").parent("div").prev("div").html()+'不能为空').show();
			}else{
				action_url += "&vd28="+modal.v("xhz");
			}
		}else if('zky'==data_type){
			/*if(''==modal.v("bzz")){
				empty_error = true;
				modal.i("bzz").select();
				FormError.html(modal.i("bzz").parent("div").prev("div").html()+'不能为空').show();
			}else if(''==modal.v("bqdd")){
				empty_error = true;
				modal.i("bqdd").select();
				FormError.html(modal.i("bqdd").parent("div").prev("div").html()+'不能为空').show();
			}else*/{
				action_url += '&addZkyGs='+modal.v('addZkyGs')+'&add_all='+modal.find('[name=add_all]:checked').length+'&piHao='+modal.v('piHao')+'&biaoZhunZhi='+modal.v('bzz')+'&buQueDingDu='+modal.v('bqdd')+'&vd31='+modal.v('bzzdw')+'&vd32='+modal.v('bqdddw');
			}
		}else if('jiabiao'==data_type){
			if(''==modal.v("qyv")){
				empty_error = true;
				modal.i("qyv").select();
				FormError.html(modal.i("qyv").parent("div").prev("div").html()+'不能为空').show();
			}else if(''==modal.v("byc")){
				empty_error = true;
				modal.i("byc").select();
				FormError.html(modal.i("byc").parent("div").prev("div").html()+'不能为空').show();
			}else if(''==modal.v("byv")){
				empty_error = true;
				modal.i("byv").select();
				FormError.html(modal.i("byv").parent("div").prev("div").html()+'不能为空').show();
			}else{
				action_url += '&flag='+modal.v('flag')+'&vd28='+modal.v('qyv')+'&vd29='+modal.v('byc')+'&vd30='+modal.v('byv')+'&vd31='+modal.v('bycdw')+'&vd32='+modal.v('byvdw');
			}
		}else{
			empty_error==true;
			FormError.html('参数错误，请刷新页面后重试');
		}
		if(empty_error==true){return false;}
		$("form[name='as_form_"+tid+"'] input[name='goto_url']").val(action_url);
		$("form[name='as_form_"+tid+"']").submit();
		modal.modal("hide");
	})
});
//zkTooltip插件
(function(a) {
    function c(b, c, d) {
        var e = d.relative ? b.position().top: b.offset().top,
        f = d.relative ? b.position().left: b.offset().left,
        g = d.position[0];
        e -= c.outerHeight() - d.offset[0],
        f += b.outerWidth() + d.offset[1],
        /iPad/i.test(navigator.userAgent) && (e -= a(window).scrollTop());
        var h = c.outerHeight() + b.outerHeight();
        g == "center" && (e += h / 2),
        g == "bottom" && (e += h),
        g = d.position[1];
        var i = c.outerWidth() + b.outerWidth();
        return g == "center" && (f -= i / 2),
        g == "left" && (f -= i),
        {
            top: e,
            left: f
        }
    }
    function d(d, e) {
        var f = this,
        g = d.add(f),
        h,
        i = 0,
        j = 0,
        k = d.attr("title"),
        l = d.attr("data-zkTooltip"),
        m = b[e.effect],
        n,
        o = d.is(":input"),
        p = o && d.is(":checkbox, :radio, select, :button, :submit"),
        q = d.attr("type"),
        r = e.events[q] || e.events[o ? p ? "widget": "input": "def"];
        if (!m) throw 'Nonexistent effect "' + e.effect + '"';
        r = r.split(/,\s*/);
        if (r.length != 2) throw "zkTooltip: bad events configuration for " + q;
        d.bind(r[0],
        function(a) {
            clearTimeout(i),
            e.predelay ? j = setTimeout(function() {
                f.show(a)
            },
            e.predelay) : f.show(a)
        }).bind(r[1],
        function(a) {
            clearTimeout(j),
            e.delay ? i = setTimeout(function() {
                f.hide(a)
            },
            e.delay) : f.hide(a)
        }),
        k && e.cancelDefault && (d.removeAttr("title"), d.data("title", k)),
        a.extend(f, {
            show: function(b) {
                if (!h) {
                    l ? h = a(l) : e.tip ? h = a(e.tip).eq(0) : k ? h = a(e.layout).addClass(e.tipClass).appendTo(document.body).hide().append(k) : (h = d.next(), h.length || (h = d.parent().next()));
                    if (!h.length) throw "Cannot find zkTooltip for " + d
                }
                if (f.isShown()) return f;
                h.stop(!0, !0);
                var o = c(d, h, e);
                e.tip && h.html(d.data("title")),
                b = a.Event(),
                b.type = "onBeforeShow",
                g.trigger(b, [o]);
                if (b.isDefaultPrevented()) return f;
                o = c(d, h, e),
                h.css({
                    position: "absolute",
                    top: o.top,
                    left: o.left
                }),
                n = !0,
                m[0].call(f,
                function() {
                    b.type = "onShow",
                    n = "full",
                    g.trigger(b)
                });
                var p = e.events.zkTooltip.split(/,\s*/);
                return h.data("__set") || (h.unbind(p[0]).bind(p[0],
                function() {
                    clearTimeout(i),
                    clearTimeout(j)
                }), p[1] && !d.is("input:not(:checkbox, :radio), textarea") && h.unbind(p[1]).bind(p[1],
                function(a) {
                    a.relatedTarget != d[0] && d.trigger(r[1].split(" ")[0])
                }), e.tip || h.data("__set", !0)),
                f
            },
            hide: function(c) {
                if (!h || !f.isShown()) return f;
                c = a.Event(),
                c.type = "onBeforeHide",
                g.trigger(c);
                if (c.isDefaultPrevented()) return;
                return n = !1,
                b[e.effect][1].call(f,
                function() {
                    c.type = "onHide",
                    g.trigger(c)
                }),
                f
            },
            isShown: function(a) {
                return a ? n == "full": n
            },
            getConf: function() {
                return e
            },
            getTip: function() {
                return h
            },
            getTrigger: function() {
                return d
            }
        }),
        a.each("onHide,onBeforeShow,onShow,onBeforeHide".split(","),
        function(b, c) {
            a.isFunction(e[c]) && a(f).bind(c, e[c]),
            f[c] = function(b) {
                return b && a(f).bind(c, b),
                f
            }
        })
    }
    a.tools = a.tools || {
        version: "1.2.6"
    },
    a.tools.zkTooltip = {
        conf: {
            effect: "toggle",
            fadeOutSpeed: "fast",
            predelay: 0,
            delay: 30,
            opacity: 0.7,
            tip: 0,
            fadeIE: !1,
            position: ["center", "right"],
            offset: [0, 0],
            relative: !1,
            cancelDefault: !0,
            events: {
                def: "mouseenter,mouseleave",
                input: "focus,blur",
                widget: "focus mouseenter,blur mouseleave",
                zkTooltip: "mouseenter,mouseleave"
            },
            layout: "<div/>",
            tipClass: "zk_tooltip"
        },
        addEffect: function(a, c, d) {
            b[a] = [c, d]
        }
    };
    var b = {
        toggle: [function(a) {
            var b = this.getConf(),
            c = this.getTip(),
            d = b.opacity;
            d < 1 && c.css({
                opacity: d
            }),
            c.show(),
            a.call()
        },
        function(a) {
            this.getTip().hide(),
            a.call()
        }],
        fade: [function(b) {
            var c = this.getConf(); ! a.browser.msie || c.fadeIE ? this.getTip().fadeTo(c.fadeInSpeed, c.opacity, b) : (this.getTip().show(), b())
        },
        function(b) {
            var c = this.getConf(); ! a.browser.msie || c.fadeIE ? this.getTip().fadeOut(c.fadeOutSpeed, b) : (this.getTip().hide(), b())
        }]
    };
    a.fn.zkTooltip = function(b) {
        var c = this.data("zkTooltip");
        return c ? c: (b = a.extend(!0, {},
        a.tools.zkTooltip.conf, b), typeof b.position == "string" && (b.position = b.position.split(/,?\s/)), this.each(function() {
            c = new d(a(this), b),
            a(this).data("zkTooltip", c)
        }), b.api ? c: this)
    }
}) (jQuery)