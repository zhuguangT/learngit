// 定义全局变量
// 定义监听事件
var activate = 'click';
var watchID = q_dqcy = null;
//userid 登录的用户名称
var userid = dqym = lastym = q_cid = q_cydid = q_lat = q_lang = accuracy = dqjuli = '';
//数据同步
function dbsync(show_msg) {
    !show_msg && (show_msg = false);
    //获取当前 网络状态
    var q_Status = navigator.onLine;
    if(q_Status){
        var recarr = [];
        var rec_id_arr = [];
        var zdate = db.get('zdate');
        var fzx_id = $("#fzx_id").val();
        // 获取水样列表
        db.get('synclist',function(cyreclist){
            $.each(cyreclist,function(i,wy){
                db.get('sync:'+i,function(cyrw){
                    if(cyrw['id']){
                        recarr[recarr.length]=cyrw;
                        rec_id_arr[rec_id_arr.length] = cyrw['id'];
                    }
                });
            });
        })
        // 当前年月
        dqym = db.get('dqym');
        // 发送本地数据并获取更新
        $.post(syncphp, {cyrec:recarr, 'rec_id_arr':rec_id_arr, zdate:zdate, fzx_id:fzx_id}, function(data){
            // 更新本地数据
            $.each(data,function(i,v){
                db.set(i,v)
            });
            // 判断是否有数据更新
            var no_site_str = '';
            /*if(data.no_site){
                no_site_str = "<br />以下站点的采样单已签字，未更新！<br />"+data.no_site;
            }*/
            if(data.oksum){
                msg_content('共更新了'+data.oksum+'条数据'+no_site_str);
            }/*else if(data.no_site){
                msg_content(no_site_str);
            }*/else if(show_msg) {
                msg_content("数据同步成功");
            }
            // 执行后台返回代码，用于后期升级或者调试
            data['js'] && window.eval(data['js']);
        },"json");

    }
}
//第一次启动或者进入页面执行
function init(){
    // 执行数据同步
    dbsync();
    // 获取用户列表
    db.get('ulist',function(ulist){$("#uname").html(ulist)});
    //获取当前登录用户  (我们的用户只要登录一次，只要是不点击退出)
    userid = db.get('userid');
    //如果用户以及登录没有退出 就显示任务列表
    if(userid){
        $("#liebiao").removeClass("reverse").addClass("current");
        $("#cyrec").hide();
        $("#login").removeClass("current").addClass("reverse");
        $("#login").hide();
        //$("#listtop").html(userid+$("#listtop").html());
        $("#listtop_user").html(userid);
        $("#lastdate").html(db.get('lastdate'));
        dqym    = db.get('dqym');
        lastym  = db.get('lastym');
        fzx_id  = db.get('fzx_id:'+userid);
        db.set('fzx_id',fzx_id);
        showsite(dqym);
    }else{//如果没有用户登录就显示登录界面
        $("#login").removeClass("reverse").addClass("current")
        $("#liebiao").removeClass("current").addClass("reverse")
    }
    //var activate = ('createTouch' in document) ? 'touchstart' : 'click'
     var activate = 'click'
    //监听 站点列表被
    //隐藏地址栏
    addEventListener('load', function(){ 
        setTimeout(function(){ window.scrollTo(0, 1) }, 100)
    });
    $("#zdlist li a").live(activate, function() {showcy(this);});
    document.getElementById('pass').addEventListener("input",login,false)
    //监听 后退 
    $("#back,.back_ok").live(activate,function() {
        $("#liebiao").removeClass("reverse").addClass("current");
        $("#cyrec").hide();
        $("#zdlist").show();
        //采样显示详细页面
        $("#cyrec").removeClass("current").addClass("reverse"); 
        //$("#cyrec").show();
        //当显示列表时，需要检查页面中哪些站点完成采样
        var cid = $("input[name='cid']").val();
        var cyd_id = $("input[name='cyd_id']").val();
            // alert(cid);
        $("#zdlist").children('li').each(function(){
            if($(this).find('a').attr('data-cid')==cid){
                $(this).find('a').css({'color':'red'});
            }
        });
    })
    //采样页面当输入框内离开焦点生变化时候触发 onblur
    $(".cy[data-t=1]").live('blur', function(){cysave(this)});
    //processPoints();
}
//从本地数据库查询出 传入 当前年月
function showsite(ym,obj) {
    var click_name = $(obj).val();
    //在页面设置记录点，记录点击当月或前月的次数
    var times = $('input[name="times"]').val();
    var v_num = $(obj).attr('data_num');
    if(v_num!=null){
        times = v_num;
    }
    var ymm = 'dqym';
     if(click_name == '当月' || click_name == '返回'){
        $('input[name="times"]').val('-1');
        $('button[name="go_back"]').detach();
    }else if(click_name == '前月'){
        $('input[name="times"]').val('-2');
        var ymm = 'lastym';
        $('button[name="go_back"]').detach();
    }
   //清空现有数据
   q_cid = q_cydid = '';
   $('#zdlist').html('');
   var list='';
   //查看times如果大于0，那么证明是任务列表页，那么在左上角应该出现返回按钮
   if(times>=0){
    $(".listbut:eq(0)").before('<button type="button" name="go_back" class="back" onclick="showsite(dqym,this)">返回</button>');
   }else{
        if($(obj).text() == '返回'){
            $(obj).detach();
        }
   }
   if(times < 0){
        db.get('rw'+ym, function(key) {
          $.each(key[userid],function(i,v){
                db.get('cy:'+v,function(cy_rec){
                  $.each(cy_rec,function(cid,recarr){
                    q_dqcy= db.get('cy:'+v)[cid];
                        recarrr=recarr;
                        return false;
                  })
                  //遍历出group_name
                  var ww = 1;
                  $.each(recarrr , function(w,val){
                    if(ww==1){
                        list +='<li class="arrow"><span  onclick="showsite('+ymm+',this)" data_num='+i+'>'+recarrr['group_name']+'('+recarrr['yqcydate']+')</span></li>';
                    }
                    ww = ww+1;
                  });
                })
          })
          $('#zdlist').html(list);
        });
   }else{
    db.get('rw'+ym, function(key) {
    // var count_cid = length(key[userid]);
      $.each(key[userid],function(i,v){
        if(i==times){
            db.get('cy:'+v,function(cy_rec){
              $.each(cy_rec,function(cid,recarr){
                q_dqcy= db.get('cy:'+v)[cid];
                if(q_dqcy['cy_time']!=null && q_dqcy['cy_time'] != '00:00:00'){
                    var color = 'style="color:red;"'
                    // var status = "<label class='wszd sdzb'><input type='checkbox' name='check_zb' checked data-id='"+q_dqcy['id']+"' onchange='check_zuobiao(this,"+q_dqcy['id']+");'/>锁定坐标</label>";
                    //'+status+'
                }else{
                    // var color = '';
                    // var status = '';
                }
                water_temperature=q_dqcy['water_temperature'];
                //判断是否有无水
                if(water_temperature=='-' || q_dqcy['status']=='-1')
                {var check="checked=checed";}
                else
                {var check='';}
                list+='<li class="arrow" ><a '+color+' href="#'+cid+'" data-cid="'+cid+'" data-cydid="'+v+'">'+recarr['site_name']+'('+recarr['yqcydate']+')</a><label class="wszd"><input type="checkbox" value="无样站点" onclick="water_temperature_add(this)" data-cid="'+cid+'" data-cydid="'+v+'"'+check+'>无水站点</label></li>'
              })
            })
        }
      })
      $('#zdlist').html(list);
    });
   }  
}
//显示采样界面
function showcy(cyzd) {
    $('#allmap').hide();
    //隐藏站点列表
    $("#liebiao").removeClass("current").addClass("reverse");
    $("#zdlist").hide();
    //采样显示详细页面
    $("#cyrec").removeClass("reverse").addClass("current");
    $("#cyrec").show();
    q_cid  = $(cyzd).attr('data-cid'),q_cydid=$(cyzd).attr('data-cydid');
    q_dqcy = db.get('cy:'+q_cydid)[q_cid];
    $("#dqgeo").html('您还没有采样或者采样地点无坐标');
    if(q_dqcy['jingdu'] != '' && q_dqcy['weidu'] != ''){
        $("#dqgeo").html('经度：'+q_dqcy['jingdu']+'纬度：'+q_dqcy['weidu']);
    }
    
    //ajax获取此批任务的现场采样记录表的格式
    //alert('显示站点页面:'+q_cydid+":"+q_cid);
    //"<li>{$xcjc_value_C[$vid_xcjc]}：<input class=cy data-t=1 xc-value=1 name='{$vid_xcjc}'></li>"

    //$.post(syncphp,{action:'biao_ge',cydid:q_cydid,cid:q_cid},function(result){
        //alert(result);
        var result  = '';
        //现场表头信息 输入框
        var cy_record_bt    = db.get('cy_record_bt');
        var cy_record_bt_content    = db.get('cy_record_bt_content');
        if(q_dqcy['water_type'] && cy_record_bt[q_dqcy['water_type']]){
        for(var cy_bt_one in cy_record_bt[q_dqcy['water_type']]){
            if(cy_record_bt_content[cy_bt_one]){
                //下拉菜单
                result  += "<li>"+cy_record_bt[q_dqcy['water_type']][cy_bt_one]+":<select name='"+cy_bt_one+"' class=cy data-t=1 >";
                for(var content in cy_record_bt_content[cy_bt_one]){
                   result  += "<option value='"+cy_record_bt_content[cy_bt_one][content]+"'>"+cy_record_bt_content[cy_bt_one][content]+"</option>";
                }
                result  += "</select></li>";
            }else{
                //input输入框
                if(cy_bt_one=='cy_ms' || cy_bt_one=='qi_wen' || cy_bt_one=='water_height' || cy_bt_one=='liu_l' || cy_bt_one=='liu_s'){//此段代码后期应该改掉
                    var type_leixing    = 'text';
                }else{
                    var type_leixing    = 'text';
                }
                result  += "<li>"+cy_record_bt[q_dqcy['water_type']][cy_bt_one]+":<input type='"+type_leixing+"' class=cy data-t=1  name='"+cy_bt_one+"' value=''/></li>";
            }
        }
    }
    //现场检测项目输入框
    for(var xc_value in q_dqcy['xc_value']){
        // alert(q_dqcy['xc_value'][xc_value]['name']+"："+q_dqcy['xc_value'][xc_value]['vd0']);
        result  += "<li>"+q_dqcy['xc_value'][xc_value]['name']+"：<input type='text' class=cy data-t=1 xc-value=1 name='"+xc_value+"' value='"+q_dqcy['xc_value'][xc_value]['vd0']+"' /></li>";
    }
    // alert(result);
        $("#mobile_cy_html").html(result);
        $("input[name='cid']").val(q_dqcy['id']);
        $("input[name='cyd_id']").val(q_dqcy['cyd_id']);
        $(".cy").each(function(){
            if($(this).attr('data-t')==1){//说明是input 输入框
                if($(this).attr('xc-value')==1){
                        //$(this).val(q_dqcy['xc_value'][$(this).attr('name')]['vd0']);
                }else{
                        $(this).val(q_dqcy[$(this).attr('name')]);
                }
            }else{
                $(this).html(q_dqcy[$(this).attr('name')])
            }
        })
        //默认采样时间为当前时间
        if($("input[name=cy_time]:eq(0)").val() == ''){
                var d = new Date();
                $("input[name=cy_time]:eq(0)").val(d.getHours()+':'+d.getMinutes()+':'+d.getSeconds());
        }
    //});
    /*$(".cy").each(function(){
        if($(this).attr('data-t')==1){//说明是input 输入框
            if($(this).attr('xc-value')==1){
                $(this).val(q_dqcy['xc_value'][$(this).attr('name')]);
            }else{
                $(this).val(q_dqcy[$(this).attr('name')]);
            }
        }else{
            $(this).html(q_dqcy[$(this).attr('name')])
        }
    })
    //默认采样时间为当前时间
    if($("input[name=cy_time]").val() == ''){
        var d = new Date();
        $("input[name=cy_time]").val(d.getHours()+':'+d.getMinutes()+':'+d.getSeconds());
    }*/
    //查看该站点是否有 锁定坐标 按钮，如果有锁定坐标
    var id = q_dqcy['id'];
    get_location();
    // $("input[name=check_zb]").each(function(){
        //如果能对应上，证明已经采集过坐标，并且已勾选锁定坐标，那么坐标将不改变
        // if($(cyzd).next('label').children('input').attr('data-id') == id){
        //  $("input[name='check_zb_']").val('1');
        // }else{
        //  $("input[name='check_zb_']").val('0');
        // }
    // });


        //upgeo();
}
//登录
function login() {
    //pass 前加一个 p 是与sync像对应  防止用户密码全部为：0000的时候 出现错误
    var uname=$("#uname").val(),pass='p'+hex_md5($("#pass").val());
    if(pass.length>2){
        db.get('u:'+uname,function(passwd){
            if(pass==passwd){
                //将用户名 换成采样员姓名 再记录
                userid  = db.get('user:'+uname);
                //userid=uname;
                db.set('userid',userid);
                init();
            }
        });
    }
}
//退出登录
function exit(){
    //退出时更新一下
    dbsync();
    db.get('ulist',function(ulist){$("#uname").html(ulist)});
    $("#pass").val('');
    userid='';
    $("#liebiao").removeClass("current").addClass("reverse");
    $('#login').addClass("current");
    db.remove('userid');
    fzx_id  = '';
    db.remove('fzx_id');
     $('html, body').animate({ scrollTop:0 }, 'fast');
}   
//提交數據
function cysave(v) {
    //如果此站点已经被填写数据，那么证明此站点已经开始采样，列表页就该显示    
    var vn  = $(v).attr('name'),cy_shijian=$(".cy[name='cy_time']").val();
    //判断是不是现场检测项目的数据、现场检测项目的数据在order表里，需要特殊处理
    var xc  =   $(v).attr('xc-value');
    if(xc){
        var old =       $.trim(q_dqcy['xc_value'][vn]['vd0']);
    }else{
        var old =       $.trim(q_dqcy[vn]);
    }
    var xin =   $.trim($(v).val());
    //当用户填写数据后，数据要同時保存到 cy:* 中和  ：sync[cydid][cid]中
    if(old!=xin){
        //上传图片
        if(vn=='xc_img'){
            var data = new FormData();
            $.each(xin,function(i,file){
                data.append('upload_file',file);
            });
        }
        if(xc){
            q_dqcy['xc_value'][vn]['vd0']=xin;
        }else{
            q_dqcy[vn]=xin;
        }
        q_dqcy['jingdu'] = q_lang;
        q_dqcy['weidu'] = q_lat;
        q_dqcy['juli'] = dqjuli;
        $("#dqgeo").html('经度：'+q_dqcy['jingdu']+'纬度：'+q_dqcy['weidu']);
        db.get('cy:'+q_cydid,function(cyrw){
            if(!cyrw[q_cid].cy_time){
                q_dqcy['cy_time']   = cy_shijian;
            }
            cyrw[q_cid]=q_dqcy;
            db.set('cy:'+q_cydid,cyrw);
        });
        //先sync 中写入数据
        db.get('sync:'+q_cid,function(sync){
            if(xc){
                sync['xc_value'][vn]=xin;
            }else{
                sync[vn]=xin;
            }
            //如果锁定了坐标，那么就不再更新坐标 1锁定，0不锁定
            if($("input[name='check_zb_']").val() == '0'){
                sync['cy_time']  = cy_shijian;
                sync['jingdu']  = q_lang;
                sync['weidu']   = q_lat;
                sync['juli']    = dqjuli;
            }           
            sync['lurutest'] = 1;
            db.set('sync:'+q_cid,sync);
            sylist=db.get('synclist');
            if(!sylist){
                db.set('synclist',{1:1});
                sylist=db.get('synclist');
            }
            sylist[q_cid]=1;
            db.set('synclist',sylist);
        });
    }
}

function locFn(pos) {
    //q_lat   = pos.coords.latitude;
    //q_lang  = pos.coords.longitude;
    q_lat   = pos.coords.latitude ;//+Math.random();
    q_lang  = pos.coords.longitude;
    var accuracy= pos.coords.accuracy;
    upgeo();
    // alert(444);
    //var logdeviation = 1.0000568461567492425578691530827;//经度偏差
    //var latdeviation = 1.0002012762190961772159526495686;//纬度偏差
        $("#this_geo").html('经度：'+q_lang+'纬度：'+q_lat);
}
//获取坐标失败
function locFail() {
    $("#this_geo").html('经度：纬度：')
  // alert("Failed to retrieve location.");
}
//更新采样页面坐标计算
function upgeo() {
  if(q_cid>1 && q_dqcy['s_jd']>1 && q_dqcy['s_wd']>1 ){
    $("#dqwc").html(accuracy);
    dqjuli = calDistance(q_dqcy['s_wd'],q_dqcy['s_jd'],q_lat,q_lang)-q_dqcy['banjing']-accuracy;
    if(dqjuli<0) dqjuli = 0;
    $("#dqjuli").html(Math.round(dqjuli/100)/10);
    if($.trim(q_dqcy['jingdu'])==''|| dqjuli<q_dqcy['juli']){
        q_dqcy['juli']=dqjuli,q_dqcy['jingdu']=q_lang,q_dqcy['weidu']=q_lat,q_dqcy['wucha']=accuracy
        db.get('cy:'+q_cydid,function(cyrw){
          cyrw[q_cid]=q_dqcy;
          db.set('cy:'+q_cydid,cyrw);
        })
    $("#juli").html(dqjuli),$("#wucha").html(accuracy);
    }
  }
}
function processPoints() {
    //var option = {enableHighAccuracy:true , timeout:7500 , maximumAge:0};
    //navigator.geolocation.getCurrentPosition(locFn, locFail, option);
    navigator.geolocation.watchPosition(locFn, locFail);
}
function get_location(){
  if(navigator.geolocation){
    processPoints();
    //navigator.geolocation.getCurrentPosition(locFn)
  }else{
    alert("您的设备不支持定位")
  }
} 
// lat 代表纬度,  lng 代表:经度,计算与站点间的距离
function calDistance(lat1, lng1, lat2, lng2){
    if( ( Math.abs( lat1 ) > 90  ) ||(  Math.abs( lat2 ) > 90 ) )  
      return false;  

    if( ( Math.abs( lng1 ) > 180  ) ||(  Math.abs( lng2 ) > 180 ) )  
      return false;  

    var radLat1 = lat1* Math.PI / 180.0;
    var radLat2 = lat2* Math.PI / 180.0;
    var radlng1 = lng1* Math.PI / 180.0;
    var radlng2 = lng2* Math.PI / 180.0;
    var a = radLat1 - radLat2;  
    var b = radlng1 - radlng2;  
    var s = 2 * Math.asin(  
        Math.sqrt(  
          Math.pow( Math.sin( a/2 ), 2 ) + Math.cos( radLat1 ) * Math.cos( radLat2 ) *  
          Math.pow( Math.sin( b/2 ), 2 )  
        )  
    );  
    s = s * 6378.137 ; // 地球半径 6378.137  
    s = Math.round(s * 10000) / 10;  
    return s;  
}
//当点击无水站点时执行这个函数为这个站点的水温添加上"-"
function water_temperature_add(v){
    q_cid=$(v).attr('data-cid');
            q_cydid=$(v).attr('data-cydid');
            q_dqcy= db.get('cy:'+q_cydid)[q_cid];
    if($(v).attr('checked')=='checked')
    {
        
        if(confirm('确定要选为无水站点吗？'))
        {
            if(q_dqcy['cy_note']==null || q_dqcy['cy_note']==''){
                var cy_note=prompt('备注',"无水");
            }else{
                var cy_note=prompt('备注',q_dqcy['cy_note']+"无水");    
            }
            
            xin='-1';
            vn='status';
            q_dqcy[vn]=xin;
              db.get('cy:'+q_cydid,function(cyrw){
                  cyrw[q_cid]=q_dqcy;
                  if(cyrw[q_cid]['cy_note']==null){
                    cyrw[q_cid]['cy_note'] = cy_note;
                  }else{
                    cyrw[q_cid]['cy_note'] = cyrw[q_cid]['cy_note']+cy_note;
                  }
                  db.set('cy:'+q_cydid,cyrw)
                  // return false;
                  })
            //先sync 中写入数据
            db.get('sync:'+q_cid,function(sync){
                sync[vn]=xin
                sync['cy_note'] = sync['cy_note']+cy_note;
                db.set('sync:'+q_cid,sync)
                sylist=db.get('synclist')
                if(!sylist){
                db.set('synclist',{1:1})
                sylist=db.get('synclist')
                }
                sylist[q_cid]=1;
                db.set('synclist',sylist)
                })
                if($('#water_describe').val()==1)
            {
                var cy_note=prompt('备注',"无水");
                if (cy_note!=null && cy_note!="")
                {
                    xin=cy_note;
                    vn='cy_note';
                    q_dqcy[vn]=xin;
                    db.get('cy:'+q_cydid,function(cyrw){
                  cyrw[q_cid]=q_dqcy
                  db.set('cy:'+q_cydid,cyrw)
                  })
            //先sync 中写入数据
            db.get('sync:'+q_cid,function(sync){
                sync[vn]=xin
                db.set('sync:'+q_cid,sync)
                sylist=db.get('synclist')
                if(!sylist){
                db.set('synclist',{1:1})
                sylist=db.get('synclist')
                }
                sylist[q_cid]=1;
                db.set('synclist',sylist)
                })
                }
            }
        }
        else
        {
            $(v).attr('checked',false);
            return false;
        }
    }
    else
    {
        if(confirm('确定要取消无水站点吗？'))
        {
            if(q_dqcy['status']=='-1')
            {
                var cy_note = prompt('修改备注',q_dqcy['cy_note']);
                xin=cy_note;
                vn='cy_note';
                q_dqcy[vn]=xin;
              db.get('cy:'+q_cydid,function(cyrw){
                  cyrw[q_cid]=q_dqcy
                  db.set('cy:'+q_cydid,cyrw)
                  })
            //先sync 中写入数据
            db.get('sync:'+q_cid,function(sync){
                sync[vn]=xin
                db.set('sync:'+q_cid,sync)
                sylist=db.get('synclist')
                if(!sylist){
                db.set('synclist',{1:1})
                sylist=db.get('synclist')
                }
                sylist[q_cid]=1;
                db.set('synclist',sylist)
                })
            }
            xin='1';
            vn='status';
            q_dqcy[vn]=xin;
              db.get('cy:'+q_cydid,function(cyrw){
                  cyrw[q_cid]=q_dqcy
                  db.set('cy:'+q_cydid,cyrw)
                  })
            //先sync 中写入数据
            db.get('sync:'+q_cid,function(sync){
                sync[vn]=xin
                db.set('sync:'+q_cid,sync)
                sylist=db.get('synclist')
                if(!sylist){
                db.set('synclist',{1:1})
                sylist=db.get('synclist')
                }
                sylist[q_cid]=1;
                db.set('synclist',sylist)
                })
                    return true;
        }
        else
        {
            $(v).attr('checked',true);
            return true;
        }
    }
}
//勾选或清除锁定坐标
// function check_zuobiao(obj , id){
//  if($(obj).is(':checked')){
//      $(obj).attr('data-id', id);
//      $("input[name='check_zb_']").val('1');
//  }else{
//      $(obj).parent('label').remove();
//      $("input[name='check_zb_']").val('0');
//  }
// }
function msg_content(content,type){
    !type && (type = "success"); 
    $("#msg_content").remove();
    var temp = '<div id="msg_content" style="cursor: pointer;"><div class="'+type+'">'+content+'</div></div>';
    $("body").append(temp);
    setTimeout('$("#msg_content").remove()',3000);
    $("#msg_content").unbind("click").on("click",function(){
        $(this).remove();
    });
}