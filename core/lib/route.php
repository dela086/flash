<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/29
 * Time: 下午9:34
 */

namespace core\lib;
use core\lib\conf;
class route {
	public $ctrl;
	public $action;
	public $path = '';

	public function __construct() {
		//1、隐藏index.php
		//2、获取参数部分
		//3、返回控制器和方法
		if (RE_URL == 1) {
			if (isset($_SERVER['REQUEST_URI']) && !in_array($_SERVER['REQUEST_URI'], array('/', '/index.php', '/index.php/'))) {
				$_SERVER['REQUEST_URI'] = str_replace('?', '/', $_SERVER['REQUEST_URI']);
				$_SERVER['REQUEST_URI'] = str_replace('&', '/', $_SERVER['REQUEST_URI']);
				$_SERVER['REQUEST_URI'] = str_replace('=', '/', $_SERVER['REQUEST_URI']);
				$_SERVER['REQUEST_URI'] = str_replace('//', '/', $_SERVER['REQUEST_URI']);

				$paramArr = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
				if ($paramArr[0] == 'index.php') array_splice($paramArr, 0, 1);

				//进去后台控制器 域名/index.php/admin/index/index
				if(isset($paramArr[0]) && $paramArr[0] == 'admin') {
					$this->path = 'admin';
					array_splice($paramArr, 0, 1);
				}

				$this->ctrl = isset($paramArr[0]) ? $paramArr[0] : conf::get('CTRL', 'route');

				if (isset($paramArr[1])) {
					$this->action = $paramArr[1];
					unset($paramArr[1]);
				} else $this->action = conf::get('ACTION', 'route');
				unset($paramArr[0]);

				$count = count($paramArr) + 2;
				$i = 2;
				while ($i < $count) {
					if (isset($paramArr[$i + 1])) {
						$_GET[$paramArr[$i]] = $paramArr[$i + 1];
					}
					$i += 2;
				}
			} else {
				$this->ctrl = conf::get('CTRL', 'route');
				$this->action = conf::get('ACTION', 'route');
			}
		} else if (RE_URL == 2) {
			if (isset($_GET['c']) && $_GET['c']) {
				$this->ctrl = trim($_GET['c']);
			} else {
				$this->ctrl = conf::get('CTRL', 'route');
			}

			if (isset($_GET['a']) && $_GET['a']) {
				$this->action = trim($_GET['a']);
			} else {
				$this->action = conf::get('ACTION', 'route');
			}

		}
	}






}