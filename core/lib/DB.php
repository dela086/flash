<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/12/1
 * Time: 下午9:48
 */

namespace core\lib;


class DB {
	static private $pdo;
	public function __construct($dsn, $username, $passwd, $options = array()) {
		if (!isset(self::$pdo) || is_null(self::$pdo) || empty(self::$pdo) || !is_object(self::$pdo)) {
			try {
				//self::$pdo = parent::__construct($dsn, $username, $passwd, $options);
				self::$pdo = new \PDO($dsn, $username, $passwd, $options);
			} catch (\PDOException $e) {
				console('数据库连接失败: ' .$e->getMessage(), 'sql');
				p($e->getMessage());
				throw new \Exception($e->getMessage());
			}
		}
	}

	//私有克隆
	private function __clone() {}


	//增加数据
	public function insert($table, array $fieldArr) {
		if (!$table || !is_string($table)) return NULL;
		if (!isset($fieldArr) || !is_array($fieldArr) || empty($fieldArr)) return NULL;

		$keys = array_keys($fieldArr);
		$fields = implode(",", $keys);
		$placeholder = substr(str_repeat('?,',count($keys)),0,-1);

		$sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholder})";
		$result = $this->executeSql($sql, array_values($fieldArr));
		if (is_array($result)) {
			$data = $result;
		} else {
			$count = $result->rowCount();
			$data = array('status' => 1, 'message'=>'success', 'data'=>$count);
		}
		return $data;
	}

	//删除数据
	protected function del($table, $where = array()) {
		if (!$table || !is_string($table)) return NULL;
		$sql = "DELETE FROM {$table} ";

		if (is_array($where)) {
			$sql_value = array();
			if (!empty($where)) {
				$sql .= " WHERE 1 = 1 ";
				foreach ($where as $key => $val) {
					$sql .= " AND {$key} = :" . $key;
					$sql_value[':' . $key] = $val;
				}
			}

			$result = $this->executeSql($sql, $sql_value);
		} else if (is_string($where)) {
			$sql .= " WHERE 1 = 1 AND " . $where;
			$result = $this->executeSql($sql);
		}
		if (is_array($result)) {
			$data = $result;
		} else {
			$count = $result->rowCount();
			$data = array('status' => 1, 'message'=>'success', 'data'=>$count);
		}
		return $data;
	}

	//修改数据
	protected function update($table, array $fieldArr, array $where = array()) {
		if (!$table || !is_string($table)) return NULL;
		if (!isset($fieldArr) || !is_array($fieldArr) || empty($fieldArr)) return NULL;

		$sql_value = array();
		$setVal = '';
		foreach ($fieldArr as $k => $v) {
			if ($setVal)
				$setVal .= ',' . $k . '=:' .$k;
			else
				$setVal .= $k . '=:' .$k;
			$sql_value[':' . $k] = $v;
		}

		$sql = "UPDATE {$table} SET " . $setVal;

		if (!empty($where)) {
			$sql .= " WHERE 1 = 1 ";
			foreach ($where as $key => $val) {
				$sql .= " AND {$key} = :" . $key;
				$sql_value[':' . $key] = $val;
			}
		}

		$result = $this->executeSql($sql, $sql_value);
		if (is_array($result)) {
			$data = $result;
		} else {
			$count = $result->rowCount();
			$data = array('status' => 1, 'message'=>'success', 'data'=>$count);
		}
		return $data;
	}

	//查找数据
	protected function select($table, array $where = array(), $fields = '*', $all = false, $limit = 0) {
		if (!$table || !is_string($table)) return NULL;
		$sql = "SELECT " . $fields . " FROM " . $table ;
		$sql_value = array();
		if (!empty($where)) {
			$sql .= " WHERE 1 = 1 ";
			foreach ($where as $key => $val) {
				$sql .= " AND {$key} = :" . $key;
				$sql_value[':' . $key] = $val;
			}
		}

		if ($all === false) {
			$sql .= " limit 1 ";
		}

		if ($limit > 0 && $all === true) {
			$sql .= " limit " . $limit;
		}

		$_stmt = $this->executeSql($sql, $sql_value);
		if (is_array($_stmt)) {
			$data = $_stmt;
		} else {
			$_result = $_stmt->fetchAll(\PDO::FETCH_ASSOC);

			/*
				$_result = array();
				while (!!$_objs = $_stmt->fetchObject()) {
				$_result[] = $_objs;
			}*/

			if (!empty($_result) && $all === false) $_result = $_result[0];

			$data = array('status' => 1, 'message'=>'success', 'data'=>$_result);
		}
		return $data;
		//return setHtmlString($_result);
	}

	//execute sql Query
	protected function sql_query($sql, array $sql_value, $all = true) {
		if (!$sql || !is_string($sql)) return NULL;
		if ($all === false ) {
			$sql .= " LIMIT 1 ";
		}

		$_stmt = $this->executeSql($sql, $sql_value);
		if (is_array($_stmt)) {
			$data = $_stmt;
		} else {
			$_result = $_stmt->fetchAll(\PDO::FETCH_ASSOC);
			if (!empty($_result) && $all === false) $_result = $_result[0];

		/*	$_result = array();
			while (!!$_objs = $_stmt->fetchObject()) {
				$_result[] = $_objs;
			}*/

			$data = array('status' => 1, 'message'=>'success', 'data'=>$_result);
		}
		return $data;
	}


	//执行SQL
	private function executeSql($sql, $sql_value = array()) {
		try {
			$_stmt = self::$pdo->prepare($sql);
			$_stmt->execute($sql_value);
			if($_stmt->errorCode() != '00000') {
				return array('status' => -1, 'message' => $_stmt->errorInfo());
			}
		} catch (PDOException  $e) {
			console('SQL语句：'.$sql.'<br />错误信息：'.$e->getMessage(), 'sql');
			throw new \Exception('SQL语句：'.$sql.'<br />错误信息：'.$e->getMessage());
		}
		return $_stmt;
	}

	protected function beginTrans() {
		self::$pdo->beginTransaction();
	}
}