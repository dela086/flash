<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/29
 * Time: 下午5:41
 */

function p($var) {
	if (is_bool($var)) {
		var_dump($var);
	} else if (is_null($var)) {
		var_dump($var);
	} else {
		echo "<pre style='position:relative;z-index:1000;padding:10px;border-radius:5px;background:#F5F5F5;border:1px solid #aaa;;
		font-size:14px;line-height:18px;opacity:0.9;'>" . print_r($var, true) . "</pre>";
	}

}

function M($class_name = 'model', $is_admin = '', $is_new_obj = false) {
	if ($class_name == '') return NULL;
	if (!is_string($class_name)) return NULL;
	static $models;

	if (isset($models[$is_admin.$class_name]) && $is_new_obj == false) {
		$obj = $models[$is_admin.$class_name];
	} else {
		if ($class_name == 'model'){
			$obj = new \core\lib\model();
			$models[$is_admin.$class_name] = $obj;
		} else {
			$app_path = $is_admin != '' ? $is_admin . '/' : '';
			$class_path = APP . $app_path . 'models/' . strtolower($class_name) . '.mdl.php';
			if (file_exists($class_path)) {
				require_once $class_path;
				$obj = new $class_name();
				$models[$is_admin.$class_name] = $obj;
			} else {
				console("未找到模型: {$class_path}");
				throw new Exception("未找到模型: {$class_path}");
				return false;
			}
		}
	}
	return $obj;
}

function redirect($url) {
	header('location:' . $url);
	exit;
}

function post($name, $filter = false, $default = false ) {
	if (isset($_POST[$name])) {
		if ($filter) {
			switch ($filter) {
				case 'int' :
					if (is_numeric($_POST[$name])) return $_POST[$name];
					else return $default;
					break;
				default:
					return $_POST[$name];
			}
		} else {
			return $_POST[$name];
		}
	}

	return $default;
}

function get($name, $filter = false, $default = false ) {
	if (isset($_GET[$name])) {
		if ($filter) {
			switch ($filter) {
				case 'int' :
					if (is_numeric($_GET[$name])) return $_GET[$name];
					else return $default;
					break;
				default:
					return $_GET[$name];
			}
		} else {
			return $_GET[$name];
		}
	}

	return $default;
}

function console($message, $file = 'log') {
	\core\lib\log::init();
	\core\lib\log::log($message, $file);
}

/*第三方接入
* @url 第三方接口url
* @type 传值类型
* @res 返回的数据格式
* @arr post传值时的数据
*/
function curlAPI($url, $type = 'get', $res = 'json', $arr = '')
{
	//1.初始化curl
	$ch = curl_init();

	//2.设置curl的参数
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if ($type=='post') {
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$arr);
	}
	//3.采集
	$output = curl_exec($ch);

	if($res=='json'){
		if(curl_errno($ch)){
			$output = curl_error();
		}else{
			$output = json_decode($output,true);
		}
	}

	//4.关闭
	curl_close($ch);

	return $output;
}

//转义HTML 标签
//预定义的字符转换为 HTML 实体
function htmlspecialchar($value, $flags = ENT_COMPAT, $character = ''){
	if (empty($value)){
		return $value;
	}else{
		return is_array($value) ? array_map('htmlspecialchar', $value) :
			htmlspecialchars($value, $flags, $character);
	}
}

//函数把预定义的 HTML 实体转换为字符 , 与上面 htmlspecialchar 功能相反
function htmlspecialchar_decode($value, $flags = ENT_COMPAT){
	if (empty($value)){
		return $value;
	}else{
		return is_array($value) ? array_map('htmlspecialchar_decode', $value) :
			htmlspecialchars_decode($value, $flags);
	}
}


/**
 * 数组 转 对象
 *
 * @param array $arr 数组
 * @return object
 */
function array_to_object($arr)
{
	if (gettype($arr) != 'array')
	{
		return;
	}
	foreach ($arr as $k => $v)
	{
		if (gettype($v) == 'array' || gettype($v) == 'object')
		{
			$arr[$k] = (object)array_to_object($v);
		}
	}

	return (object)$arr;
}

/**
 * 对象 转 数组
 *
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj)
{
	$obj = (array)$obj;
	foreach ($obj as $k => $v)
	{
		if (gettype($v) == 'resource')
		{
			return;
		}
		if (gettype($v) == 'object' || gettype($v) == 'array')
		{
			$obj[$k] = (array)object_to_array($v);
		}
	}

	return $obj;
}

//获取静态资源
function get_static($string) {
	if (!is_string($string)) {
		console('获取静态资源,必须传入字符串!');
		return false;
	}
	$template = ROOT . 'public/';
	$strArr = explode(',', $string);
	foreach ($strArr as $item) {
		if (preg_match('/\.css$/', $item)) {
			$result[] = "<link rel='stylesheet' type='text/css' href='{$template}{$item}' />\r\n";
		} elseif (preg_match('/\.js$/', $item)) {
			$result[] = "<script type='text/javascript' src='{$template}{$item}'></script>\r\n";
		} else {
			$result[] = "{$template}{$item}";
		}
	}
	return implode('', $result);
}

//获取跳转地址
function get_redirect_url($act, $params = null) {
	if (!isset($act) || !$act) return false;
	$get = '';
	if (is_array($params)) {
		foreach ($params as $key => $value) {
			$get .= '/' . $key . '/' . $value;
		}
	} else {
		$param = trim($params);
		if (strlen($param) > 0 && (strpos($param, '/', 1) !== false)) {
			$get .= '/' . $param;
			$get = rtrim($get, '/');
		}
	}

	$url = get_domain() . $act . $get;
	return $url;
}

function get_domain(){
	/* 协议 */
	$protocol = (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';

	/* 域名或IP地址 */
	if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
		$host = $_SERVER['HTTP_X_FORWARDED_HOST'];
	} elseif (isset($_SERVER['HTTP_HOST'])) {
		$host = $_SERVER['HTTP_HOST'];
	} else {
		/* 端口 */
		if (isset($_SERVER['SERVER_PORT'])) {
			$port = ':' . $_SERVER['SERVER_PORT'];

			if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol)) {
				$port = '';
			}
		} else {
			$port = '';
		}

		if (isset($_SERVER['SERVER_NAME'])) {
			$host = $_SERVER['SERVER_NAME'] . $port;
		} elseif (isset($_SERVER['SERVER_ADDR'])) {
			$host = $_SERVER['SERVER_ADDR'] . $port;
		}
	}
	return $protocol . $host . '/';
}

//获取管理员登录信息
function manager_info() {
	return isset($_SESSION['manager']) ? $_SESSION['manager'] : array();
}

function return_value($status = 1, $msg = '', $data = array()) {
	return $result = array(
		'status' => $status,
		'msg' => $msg,
		'data' => $data
	);
}