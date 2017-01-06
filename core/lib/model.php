<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/29
 * Time: 下午10:51
 */

namespace core\lib;
use core\lib\conf;

class model extends DB  {
	public function __construct()
	{
		$DB = conf::all('db');   //加载数据库配置文件
		parent::__construct($DB['DSN'], $DB['USERNAME'], $DB['PASSWORD']);
	}



	public function get_row($table, array $where = array(), $fields = '*') {
		if (!$table || !is_string($table)) return NULL;
		return $this->select($table, $where, $fields);
	}


	public function get_all($table, array $where = array(), $fields = '*') {
		if (!$table || !is_string($table)) return NULL;
		return $this->select($table, $where, $fields, true);
	}

	public function get_limit_result($table, array $where = array(), $fields = '*', $limit = 20) {
		if (!$table || !is_string($table)) return NULL;
		return $this->select($table, $where, $fields, true, $limit );
	}

	public function get_row_sql($sql, $sql_value) {
		if (!$sql || !is_string($sql)) return NULL;
		return $this->sql_query($sql, $sql_value, false);
	}

	public function get_page_result($sql, $sql_value = array(), $pageNo = 1, $pageSize = 20) {
		if (!$sql || !is_string($sql)) return NULL;
		$total = $this->sql_query($sql, $sql_value);

		$pageNo = ($pageNo -1) * $pageSize;
		$rows_sql = $sql . " LIMIT " . $pageNo . "," . $pageSize;
		$rows = $this->sql_query($rows_sql, $sql_value);
		return array('total'=>count($total['data']), 'rows' => $rows['data']);
	}

	public function update_by($table, array $data = array(), array $where = array()) {
		if (!$table || !is_string($table)) return NULL;
		return $this->update($table, $data, $where);
	}

	public function del_by ($table, $where) {
		if (!$table || !is_string($table)) return NULL;
		return $this->del($table, $where);
	}
}
