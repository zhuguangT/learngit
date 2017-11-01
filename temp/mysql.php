<?php
#error_reporting(7);

class DB_MySQL  {
	var $servername="localhost";
	var $dbname="water";
	var $dbusername="root";
	var $dbpassword="";
        var $isselectdb='0';
	var $id = 0;
	var $link_id = 0;
	var $query_id = 0;

	var $querycount = 0;
	var $result;
	var $record = array();
	var $rows;
	var $affected_rows = 0;
	var $insertid;
	var $r_result;   	//若SQL为select语句，返回值保存在 $r_result中(资源标识符或false)
	var $cmd_result; 	//若SQL为其它语句，返回值保存在 $cmd_result中（true或false)
	var $errno;
	var $error;
	var $querylog = array();
	var $tables;		//该数据库中所有表
	
	function list_tables(){
		if($this->isselectdb=='0') $this->selectdb();
		$this->tables=mysql_list_tables($this->dbname,$this->link_id) or $this->halt();
		return $this->tables;
	}
      function geterrdesc() {
               $this->error = @mysql_error($this->link_id);
               return $this->error;
      }

      function geterrno() {
               $this->errno = @mysql_errno($this->link_id);
               return $this->errno;
      }

      function connect(){
	      if ($this->pconnect==1)
		      if ($this->link_id = @mysql_pconnect($this->servername.':'.
					      $this->port,$this->dbusername,$this->dbpassword))
			      return $this->link_id;
		      else $this->pconnect=0;
		      if (!$this->link_id = @mysql_connect($this->servername.':'.$this->port,$this->dbusername,$this->dbpassword))
			      $this->halt("数据库链接失败");
               return $this->link_id;
      }

      function selectdb()
      {
	      global $charset;
	      if($this->link_id=='0') $this->connect();
	      switch(strtolower($charset)) {
		      case 'utf-8':
		      case 'utf8':
			      @mysql_set_charset("utf8",$this->link_id);
			      @mysql_query("SET CHARACTER SET utf8",$this->link_id);
			      @mysql_query("SET NAMES utf8",$this->link_id);
			      break;
		      case 'gbk':
		      case 'gb2312':
			      @mysql_set_charset("gbk",$this->link_id);
			      @mysql_query("SET CHARACTER SET gbk",$this->link_id);
			      break;
	      }

	      if(!mysql_select_db($this->dbname,$this->link_id))
	      {
		      $this->halt("数据库链接失败");
	      }
	      $this->isselectdb='1';

      }

	function query($sql){
		global $u,$tables;
		$flag=substr(trim(strtolower($sql)),0,6);
		if($this->isselectdb=='0') $this->selectdb();
		if($flag=='select'){
			$debugv=debug_backtrace();
			while($debuga=each($debugv))
				if(basename($debuga['value']['file'])==basename($_SERVER["SCRIPT_FILENAME"]))
				{
					break;
				}
			$str=substr(stristr($sql,'from '),5);
			$strs=explode(' ',trim($str));
			$strs[0]=strtr($strs[0],array('`'=>''));
			$strss=explode(',',$strs[0]);
			while($strs=each($strss))
				$tables[trim($strs[value])]=$debuga[value]['line'];
				
			$this->r_result=mysql_query($sql,$this->link_id) or $this->halt($sql);
			$this->rows=$this->num_rows($this->r_result);
			return $this->r_result;
		}else{
			logmsg($sql);
			$s1="表名:$table_name, SQL:".$sql;
			/*删除记录，更新记录，新增记录均保存到 system_log 中*/
			if($flag=='update'){
				$matches='';
				preg_match("/^update\s+([\w`]+)\s+set\s+(.*)\s+where\s+(.+)/i",$sql,$matches);
				if($matches){
					$table_name=$update_case=$d=$data='';
					$table_name=$matches[1];
					$update_case=$matches[3];
					$R=mysql_query("select * from ".$table_name." where ".$update_case);
					if(mysql_num_rows($R)==1) {
						$old_data=$this->fetch_one_assoc("select * from $table_name where $update_case");
					}
					$this->cmd_result=mysql_query($sql,$this->link_id) or $this->halt($sql);
					$this->affected_rows=$this->affected_rows();
					if($this->affected_rows==1){
						$new_data=$this->fetch_one_assoc("SELECT * FROM $table_name WHERE $update_case");
						$modify_item=array();
						if($new_data){
							while($aData=each($new_data))
								if($old_data[$aData['key']]!=$aData['value'] && $old_data[$aData['key']]!='') $modify_item[]=$aData['key'].'|'.$old_data[$aData['key']].'|'.$aData['value'];
						}else logmsg($s1,"system_log");
						if($modify_item) {
							$modify_items=implode("\n",$modify_item);
							if(substr($modify_items,0,10)!='lastlogout')	logmsg("记录号:$new_data[id],表名:$table_name,修改项目:$modify_items","system_log");
						}
					}elseif($this->affected_rows>1) logmsg($s1,"system_log");
					else ;
				}else{
					$this->cmd_result=mysql_query($sql,$this->link_id) or $this->halt($sql);
					$this->affected_rows=$this->affected_rows();
					logmsg($s1,"system_log");
				}
			}else{
				$this->cmd_result=mysql_query($sql,$this->link_id) or $this->halt($sql);
				$this->affected_rows=$this->affected_rows();
				#if($flag=='insert') $this->insertid=mysql_insert_id($this->link_id);
				if($flag == 'delete') logmsg($s1,"system_log");
			}
			return $this->cmd_result;
		}
	}


	function fetch_array($queryid) {
		$this->record = mysql_fetch_array($queryid);
		if (empty($queryid)){
			$this->halt("Query id 无效:".$queryid);
		}
		return $this->record;
	}

	function fetch_assoc($queryid) {
		$this->record = mysql_fetch_assoc($queryid);
		if (empty($queryid)){
			$this->halt("Query id 无效:".$queryid);
		}
		return $this->record;
	}
	function fetch_row($queryid) {
		$this->record = mysql_fetch_row($queryid);
		if (empty($queryid)){
		    $this->halt("queryid 无效:".$queryid);
		}
		return $this->record;
	}

	function fetch_one_array($query) {

	       $this->result =  $this->query($query);
	       $this->record = $this->fetch_array($this->result);
	       if (empty($query)){
	           $this->halt("Query id 无效:".$query);
	       }
	       return $this->record;

	}

	function fetch_one_assoc($query) {

	       $this->result =  $this->query($query);
	       $this->record = $this->fetch_assoc($this->result);
	       return $this->record;

	}

	function num_rows($queryid) {

	       $this->rows = @mysql_num_rows($queryid);

	       if (empty($queryid)){
	           $this->halt("Query id 无效:".$queryid);
	       }
	       return $this->rows;
	}

	function affected_rows() {
	       $this->affected_rows = mysql_affected_rows($this->link_id);
	       return $this->affected_rows;
	}

	function free_result($query){
	       if (!mysql_free_result($query)){
	            $this->halt("释放结果集内存失败");
	       }
	}

	function insert_id(){
	       $this->insertid = mysql_insert_id($this->link_id);
	       if (!$this->insertid){
	            $this->halt("未得到新记录的ID，检查最近的insert操作");
	       }
	       return $this->insertid;
	}

	function close() {
		@mysql_close($this->link_id);

		$this->link_id=0;
		$this->isselectdb = 0;

	}


	function halt($msg){
		global $technicalemail,$debug,$u;
	$content = "<span onclick='history.go(-1);'><font color=blue>返回</font></span><br><p>数据库出错:</p><pre><b>".htmlspecialchars($msg)."</b></pre>\n";
		$content .= "<b>mysql错误号:</b>: ".$this->geterrno()."\n<br>";
		$content .= "<b>mysql错误信息:</b>: ".$this->geterrdesc()."\n<br>";
		$content .= "<b>日期时间</b>: ".date("Y-m-d @ H:i")."\n<br>";
		$content .= "<b>脚本</b>: http://".$_SERVER[HTTP_HOST].urldecode(getenv("REQUEST_URI"))."\n<br>";
		$debugv=debug_backtrace();
		$content .= "子程序调用: \n<br>";
		for($aa=0;$aa<count($debugv);$aa++){     
			$adebug=$debugv[$aa];
			$content .= "$adebug[file]:[$adebug[line]]function:$adebug[function]\n<br><br>";
		}
	$content .= "<b>Referer</b>: ".getenv("HTTP_REFERER")."\n<br><br>";

	           $message .= $content;
	       $message .= "<p>请尝试刷新你的浏览器,如果仍然无法正常显示,请联系<a href=\"mailto:$technicalemail\">管理员</a>.</p>";
	       $message .= "</body>\n</html>";
	       echo $message;

	       $headers = "From: =?utf-8?B?".base64_encode($u[userid])."?= <$technicalemail>\r\n";

	       $content = strip_tags($content);
	       @mail($technicalemail,'=?utf-8?B?'.base64_encode('数据库出错').'?=',$content,$headers);

	       exit;
	}

    //备份表结构 若提供文件句柄，则输出到文件，否则返回生成表的字符串
    function backup_stru($table_name,$file_handle=''){
        $sql_string="DROP TABLE IF EXISTS `$table_name`;\n";
	$rec=$this->fetch_one_array("SHOW CREATE TABLE `$table_name`");
        $sql_string.=$rec[1].";\n\n\n";
	if($file_handle) 
            return fwrite($file_handle,$sql_string);
        else 
            return $sql_string;
    }
    //备份表数据，必须提供文件句柄
    function backup_data($table_name,$file_handle){
        if(!$file_handle) return false;
        $re=$this->query("SELECT * FROM `$table_name`");
        while($row = $this->fetch_row($re)){
            for($i=0;$i<count($row);$i++)
                $row[$i]= (isset($row[$i])) ? "'".mysql_real_escape_string($row[$i])."'" : 'NULL';
            $data = "INSERT INTO `$table_name` VALUES(".implode(',',$row).");\n";
            fwrite($file_handle,$data);
        }
        fwrite($file_handle,"\n\n");
        return true;
    }
    //备份数据库
    function backup_db( &$file_handle ) {
        if(!$file_handle) return false;
        set_time_limit(0);
        $ti=time();
	echo '1';
        $rex=$this->list_tables($this->dbname);
	for ($i = 0; $i < $this->num_rows($rex); $i++){
	  $one_table= mysql_tablename($rex, $i);
	    $this->backup_stru( $one_table, $file_handle );
            $this->backup_data( $one_table, $file_handle );
     }
        fclose($file_handle);
        $ti=time()-$ti;
        echo '<!--备份成功,共耗时'.$ti.'秒!-->';
        return $ti;
    }
    //恢复数据库
    function restore_db( &$file_handle ){
        if( !$file_handle ) 
            return false;
        set_time_limit( 0 );
        $_time=time();
        while(!feof($file_handle)){
            $aline=trim(fgets($file_handle));
            if(!$aline || $aline{0}=='#') continue; //这是一行注释或者是空行
            if( substr ( $aline, -1 ) == ';' ) {
                $query_str .= $aline;
                $this->query( $query_str );
                $query_str = "";
            }
            else $query_str.=$aline;
        }
        fclose($file_handle);
        $_time=time()-$_time;
        return $_time;
    }
}
?>
