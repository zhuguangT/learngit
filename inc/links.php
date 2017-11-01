<?php
    $m['yp_baifang'] = "<a style='color:#B22222;' href='$rooturl/cy/yp_baifang.php?cyd_id=$row[id]'>样品摆放</a>";
    $m['ypjs'] = "<a style='color:#B22222;' href='$rooturl/cy/yp_zbjs.php?cyd_id=$row[id]'>样品接收</a>";
    
    
    $m['hy_tzd'] = "<a href='$rooturl/xd_csrw/csrw_tzd.php?cyd_id=$row[id]'>打印任务</a>";
    $m['hyd'] = "<a href='$rooturl/huayan/assay_pay_list.php?cyd_bh=$row[cyd_bh]&year=$_GET[year]&month=$_GET[month]'>化验单</a>";
    $m['yp_detail'] = "$rooturl/mission_list.php?cyd_id=$row[id]&cy_date=$row[cy_date]";
    $m['yp_jsd'] = "<a href='$rooturl/cy/yp_jsd.php?cyd_id=$row[id]' target='_blank'>接收单</a>";
    $m['create_report'] = "<a style='color:#B22222;' href='$rooturl/create_report.php?cyd_id=$row[id]'>生成报告</a>";
    $m['view_report'] = "<a href='$rooturl/report_view.php?action=查看&cyd_id=$row[id]' target='_blank'>检测报告</a>";
    $m['cgb'] = "<a href='$rooturl/data/view_group_result.php?cyd_id=$row[id]' target='_blank'>成果表</a>";

    $m['add_bzyp'] = "<a style='color:#B22222;' href='$rooturl/xd_csrw/add_bzyp.php?cyd_id=$row[id]'>添加标样</a>";
    $m['modify_zhi_kong'] = "<a style='color:#B22222;' href='$rooturl/xd_csrw/modi_zk.php?cyd_id=$row[id]&action=添加室内质控'>添加质控</a>";
    $m['create_hyd'] = "<a style='color:#B22222;' href='$rooturl/xd_csrw/create_hyd.php?cyd_id=$row[id]' title='生成化验单'>生成化验</a>";
    $m['modify_csrw'] = "<a style='color:#B22222;' href='$rooturl/xd_csrw/csrw_tzd.php?cyd_id=$row[id]'>修改检测任务</a>";
    $m['js_csrw'] = "<a href='$rooturl/huayan/hy_tzd.php?cyd_id=$row[id]' target='_blank'>接受检测任务</a>";
    $m['xd_csrw'] = "<a href='$rooturl/huayan/hy_tzd.php?cyd_id=$row[id]' target='_blank'>修改检测任务</a>";
    $m['add_huayan_item'] = "<a href='$rooturl/huayan/add_huayan_item.php?cyd_id=$row[id]' target='_blank'>添加删除化验项目</a>";
   // $m['xiaodui'] = "<a href='$rooturl/guanli/xiaodui.php?cyid=$row[id]' target='_blank'>校对</a>";
    $m['xiaodui'] = "<a href='$rooturl/shuju_hl.php?cyid=$row[id]'>数据合理对比</a>";
  /*
        李晓坤添加 2012-11-28 am.
        修改采样单  链接
    */
    $m['xiugai'] = "<a href='$rooturl/cy/cyxg.php?cyd_id=$row[id]'>修改</a>";



?>
