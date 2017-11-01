<?php
/**
 * 功能：检测任务列表
 * 作者：Mr Zhou
 * 日期：2017-03-01
 * 描述：
 */
class Jcrw_listApp extends LIMS_Base {
    /**
     * 构造函数
     */
    function __construct() {
        parent::__construct();
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2017-03-01
     * 功能描述：
    */
    public function index(){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$trade_global,$rooturl,$current_url;
        // 导航（前面部分在config.php中赋值）
        $trade_global['daohang'][] = array('icon'=>'','html'=>'检测任务列表','href'=>'./xd_csrw/xd_csrw_list.php');
        // 记录下本页面的导航到 session中
        $_SESSION['daohang']['ahlims']  = $trade_global['daohang'];
        $this->disp('xd_csrw_list',get_defined_vars());
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2017-03-01
     * 功能描述：获取检测任务列表数据
    */
    public function jcrw_list($fid=0){
        $u      = $this->_u;
        $DB     = $this->_db;
        $fzx_id = $this->fzx_id;
        global $global,$rooturl;
        //定义筛选条件信息
        $sql_where_arr = array();
        //定义所有sql组合元素
        $sql_select = $sql_from = $sql_where = $sql_search = $sql_sort = $sql_order = $sql_limit = '';
        //SELECT查询的所有字段信息
        $sql_select = "SELECT `cy`.* ";
        //FROM的表连接信息
        $sql_from   = " FROM `cy`";
        $sql_where_arr[] = "WHERE `cy`.`status` >= '5'";
        $sql_where_arr[] = "`cy`.`fzx_id` >= '{$fzx_id}'";
        // WHERE条件
        // 任务性质
        if( isset($_GET['site_type']) && intval($_GET['site_type']) ){
            $sql_where_arr[] = "`cy`.`site_type` = '{$_GET['site_type']}'";
        }
        // 采样日期
        if( !isset($_GET['year']) || !intval($_GET['year']) ){
            $year = date('Y');
        }else{
            $year = intval($_GET['year']);
        }
        // 年
        $sql_where_arr[] = "YEAR(`cy`.`cy_date`) = '{$year}'";
        // 月
        if( isset($_GET['year']) && intval($_GET['month']) ){
            $sql_where_arr[] = "MONTH(`cy`.`cy_date`) = '{$_GET['month']}'";
        }
        //定义搜索条件
        $search_str = trim($_GET['search']);
        if( !empty($search_str) ){
            $sql_like = array();
            if( preg_match("/^[a-zA-Z]{2,}/",$search_str) || preg_match("/\d{4,}/",$search_str) ){
                // 样品编号搜索
                $sql_where = implode(' AND ', $sql_where_arr);
                // 清空sql_where条件，避免在实际查询时重复搜索条件
                $sql_where_arr = array(' WHERE 1 ');
                $search_cyd_id_by_code = "SELECT `cy`.`id` 
                                            FROM `cy`
                                            LEFT JOIN `cy_rec` ON `cy`.`id`=`cy_rec`.`cyd_id` 
                                        {$sql_where} AND `cy_rec`.`bar_code` LIKE '%{$search_str}%'";
                $sql_like[] = "`cy`.`id` IN( {$search_cyd_id_by_code} )";
            }else{
                //根据可能搜索的字段依次在项目名称，采样单号，采样人，批次名称中进行检索
                $sql_like[] = "`cyd_bh` = '{$search_str}'";
                $sql_like[] = "`cy_user` = '{$search_str}'";
                $sql_like[] = "`group_name` LIKE '%{$search_str}%'";
            }
            $sql_search = ' AND (' . implode(' OR ', $sql_like) . ')';
        }
        //组合WHERE条件
        $sql_where = implode(' AND ', $sql_where_arr);
        //指定递增排序还是递减排序
        $order = !isset($_GET['sort']) ? 'ASC' : strtoupper(trim($_GET['order']));
        !in_array( $order, array('ASC', 'DESC') ) && ( $order = 'ASC' );
        //指定排序列
        $sort = (isset($_GET['sort']) && !empty($_GET['sort']) ) ? trim($_GET['sort']) : 'cy_date';
        switch ($sort) {
            case 'cy_user':
            case 'group_name':
                $sort = "CONVERT( `cy`.`{$sort}` USING gbk ) {$order}";
                break;
            default:
                $sort = "`cy`.`{$sort}` {$order}";
                break;
        }
        $sql_sort_order = "ORDER BY {$sort} , `cy`.`id` ASC ";
        //定义分页信息，必须同时传递offset和limit参数并且limit数据必须大于0
        if( isset($_GET['offset']) && intval($_GET['limit']) ){
            $sql_limit = 'LIMIT '.intval($_GET['offset']).' , '.intval($_GET['limit']);
        }
        $i = intval($_GET['offset']);
        $data_list = array();
        //统计总行数
        $total = $DB->num_rows($DB->query("SELECT `cy`.`id` {$sql_from} {$sql_where} {$sql_search}"));
        //查询详细信息
        $query = $DB->query( $sql_select . $sql_from . $sql_where . $sql_search . $sql_sort_order . $sql_limit );
        while ($row = $DB->fetch_assoc($query)) {
            $row['xuhao'] = ++$i;
            // 让admin可以方便的看到cyd_id，方便维护
            $row['xuhao'] .=  !$u['admin'] ? '' : "&nbsp;<font color='#D88376'>(id:{$row['id']})</font>";
            // 操作选项
            $operation =  array();
            // 获取批次内的有效水样编号
            $row['bar_code'] = $this->get_bar_code($row['id']);
            if(empty($row['bar_code']['total'])){
                $operation[] = '<div><font color="red">该任务所有站点无水</font></div>';
            }else{
                if( $row['status'] == '5' ){  //生成化验单前
                    if($u['xd_csrw']){
                        $operation[] = "<a href=\"fp_csrw.php?cyd_id={$row['id']}\">分配测试任务</a>";
                    }
                }else{  //已生成化验单
                    $operation[] = "<a href=\"fp_csrw.php?cyd_id={$row['id']}\">修改测试任务</a>";
                    $operation[] = "<a href=\"{$rooturl}/xd_csrw/rwjs.php?cyd_id={$row['id']}\" target=\"_blank\">任务接收单</a>";
                    $operation[] = "<a href=\"{$rooturl}/xd_csrw/yply.php?cyd_id={$row['id']}\" target=\"_blank\">样品领用单</a>";
                    $operation[] = "<a href=\"{$rooturl}/huayan/ahlims.php?app=pay_list&cyd_id={$row['id']}&year={$_GET['year']}&month={$_GET['month']}\">查看化验单</a>";
                }
                $return = '';
                if($row['status'] == '6' || $u['admin']){
                    $return = "<a  href=\"{$rooturl}/xd_csrw/return.php?action=return&did={$row['id']}\" onclick=\"return confirm('状态一旦后退,上次生成的化验单将全部删除,是否删除')\">&lt;&lt;</a>&nbsp;&nbsp;"; 
                }
                if( $u['system_admin'] || (($u['xd_cs_rw'] || $u['xd_cy_rw']) && $row['status'] <= '5' )){
                    $operation[] = "<a class=\"red icon-remove bigger-130\" title=\"删除本批次任务\" href=\"javascript:if(confirm('一旦删除，整批任务都将被删除且无法恢复，你确实要删除吗？')){location='{$rooturl}/cy/modify_cyd.php?action=删除&cyd_id={$row['id']}';}\"></a>";
                }
            }
            $row['option'] = implode('|', $operation);
            // 转换成对应的状态说明
            $row['status'] = $return . $global['status'][$row['status']];
            $data_list[] = $row;
        }
        echo json_encode(array('total'=>$total,'rows'=>$data_list));
    }
    // 获取批次内水样编号
    public function get_bar_code($cyd_id){
        global $u, $DB, $fzx_id, $global;
        $bar_code = array();
        $total = 0;
        // 只查询取到水的水样编号
        $sql = "SELECT `bar_code` FROM `cy_rec` WHERE `cyd_id`='{$cyd_id}' AND `status` !='-1' ORDER BY RIGHT(`bar_code`,4)";
        $query = $DB->query($sql);
        while ($row = $DB->fetch_assoc($query)) {
            $total++;
            $bar_code[] = $row['bar_code'];
        }
        include_once INC_DIR.'/cy_func.php';
        $short_barcode = '<b>样品编号（'.$total.'）：</b><br />'.str_replace(array('、', '"'), array('<br />', "'"), get_short_barcode($bar_code));
        $short_barcode = str_replace('～', ' <span class=\'red\' style=\'font-weight:900;font-size:2rem;\'>～</span> ', $short_barcode);
        return array(
            'total' => $total,
            'info' => $short_barcode
        );
    }
}