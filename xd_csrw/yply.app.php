<?php
/**
 * 功能：原始记录表
 * 作者：Mr Zhou
 * 日期：2017-03-05
 * 描述：
 */
class YplyApp extends LIMS_Base {

    /**
     * 构造函数
     */
    function __construct() {
        parent::__construct();
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2017-03-05
     * 功能描述：
    */
    public function index(){
        global $rooturl;
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $cyd_id = intval($_GET['cyd_id']);
        // 采样日期，采样人，批名/委托单位，采样容器名称，容器规格，接收样品时间，接收人签字（电子签名）
        $sql = "SELECT 
                `rec`.`cy_date`, `rec`.`json`, 
                `cy`.`cy_user`, `cy`.`cy_user2`, `cy`.`group_name`, 
                `ao`.`vid`, `ao`.`bar_code`, `ao`.`hy_flag` ,
                `ap`.`start_time`, `ap`.`userid`, `ap`.`assay_element`
            FROM `cy_rec` AS `rec`
            LEFT JOIN `cy` ON `cy`.`id` = `rec`.`cyd_id` 
            LEFT JOIN `assay_order` AS `ao` ON `ao`.`cid`=`rec`.`id` 
            LEFT JOIN `assay_pay` AS `ap` ON `ap`.`id`=`ao`.`tid` 
            WHERE 1 
                AND `rec`.`cyd_id`='{$cyd_id}' AND `cy`.`fzx_id`='{$fzx_id}' 
                AND `ap`.`start_time` != '' AND `ap`.`start_time` IS NOT NULL AND `ap`.`is_xcjc`!='1' 
                AND (`hy_flag` >= '0' OR `hy_flag` IN('-6','-1')) 
            ORDER BY CONVERT( `ap`.`userid` USING gbk )";
        $sql = $DB->query($sql);
        // 使用到的容器
        $rq_ids = array();
        $yply_list = array();
        $xuhao = 0;
        while($row = $DB->fetch_assoc($sql)){
            $row['xuhao'] = ++$xuhao;
            $row['json'] = json_decode($row['json'], true);
            $row['start_time'] = empty($row['start_time']) ? '' : substr($row['start_time'], 0, 10);
            // 记录使用的容器
            $rq_ids = array_unique(array_merge($rq_ids, array_keys($row['json']['rq'])));
            $yply_list[] = $row;
        }
        $rongqi = $this->get_rongqi($rq_ids);
        $this->disp('yply_list',get_defined_vars());
    }
    /**
     * 功能：
     * 作者：Mr Zhou
     * 日期：2017-03-05
     * 功能描述：获取采样容器，键值为vid，value为容器ID
    */
    private function get_rongqi($rq_ids){
        $u = $this->_u;
        $DB = $this->_db;
        $fzx_id = $this->fzx_id;
        $ids = implode("','", $rq_ids);
        $sql = "SELECT `id`, `rq_name`, `rq_size`, `vid` FROM `rq_value` WHERE `vid` != '' AND `fzx_id`='{$fzx_id}' AND `id` IN('{$ids}') ORDER BY `id`";
        $query = $DB->query($sql);
        $rongqi = array();
        while ($row = $DB->fetch_assoc($query)) {
            $vids = explode(',', $row['vid']);
            foreach ($vids as $key => $vid) {
                if($u['admin'] && isset($rongqi[$vid])){
                    echo $vid;
                    print_rr($rongqi[$vid]);
                    print_rr($row);
                }
                $rongqi[$vid] = $row;
            }
        }
        return $rongqi;
    }
}