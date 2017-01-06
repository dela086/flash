<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/29
 * Time: 下午5:50
 */

namespace core;

class flash {
	static public $classMap = array();
	//static public $action;
	public $assign;
	static public function run () {
		$route = new \core\lib\route();
		$path = $route->path;
		$ctrl = $route->ctrl;
		$action = $route->action;
		$_path = $path ? $path . '/' : '';
		$M_path = $path ? '\\' . $path : '';

		$ctrlFile = APP . $_path . 'ctrl/' . $ctrl . 'Ctrl.php';
		if (file_exists($ctrlFile)) {
			include $ctrlFile;
			$ctrlClass = '\\' . MODULE . $M_path . '\ctrl\\' . $ctrl . 'Ctrl';
			$control = new $ctrlClass();
			$control->$action();

			//调用log 类
			//\core\lib\log::init();
			//\core\lib\log::log('这里写入log 123213123123123123213');  //后期可以改成一个方法 如: console($msg, $file);
			console('The flash framework is work normally !');
		} else {
			console('找不到控制器' . $ctrl);
			throw new \Exception('找不到控制器' . $ctrl);
		}
	}

	static public function autoload($class) {
		//自动加载类库
		if (isset($classMap[$class])) return true;
		else {
			$class = str_replace('\\', '/', $class);
			$classFile = ROOT . $class . '.php';
			if (is_file($classFile)) {
				include $classFile;
				self::$classMap[$class] = $classFile;
			} else {
				return false;
			}
		}
	}


	public function assign($name, $value) {
		$this->assign[$name] = $value;
	}

	public function display($tpl = '', $ext = '') {
		if ($ext != '') {
			$ext .= '/';
			$ext = str_replace('//', '/', $ext);
		}

		if ($tpl == '') {
			//default tpl is here, please coding it...
			$file = APP . $ext .'views/index.htm;';
			$tpl = 'index.html';
		} else {
			$tplArr  = explode('.', $tpl);
			$count = count($tplArr) - 1;
			if ($count >= 1 && in_array($tplArr[$count], array('html', 'htm', 'tpl'))) {
				$file = APP . $ext .'views/' . $tpl;
			} else {
				$file = APP . $ext. 'views/' . $tpl . POSTFIX;
			}
		}

		if (is_file($file)) {

			//require_once '/path/to/lib/Twig/Autoloader.php';  //不用composer,直接引入TWIG 引擎
			\Twig_Autoloader::register();

			$loader = new \Twig_Loader_Filesystem(APP . $ext .'views');
			$twig = new \Twig_Environment($loader, array(
				'cache' => APP . 'cache/runtime/twig/',
				'debug' => DEBUG
			));
			$template = $twig->load($tpl);
			$template->display($this->assign ? $this->assign : array());
		} else {
			console('Do not find the tpl : ' . $file);
			throw new \Exception('Do not find the tpl : ' . $file);
			exit;
		}
	}







}