<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/29
 * Time: 下午11:42
 */

namespace core\lib;


class conf{
	static public $conf = array();

	//加载某个配置文件的某个单一项目
	static public function get($name, $file) {
		//3、判断是否已加载过配置,如已加载过直接返回
		if(isset(self::$conf[$file])) {
			return self::$conf[$file][$name];
		} else {
			//1、判断配置文件是否存在
			$confFile = ROOT . 'config/' . $file . '.conf.php';
			if (file_exists($confFile)) {
				$conf = include $confFile;                //2、加载配置文件
				if (isset($conf[$name])) {
					self::$conf[$file] = $conf;
					return $conf[$name];
				} else {
					throw new \Exception('无此配置项: ' . $name);
				}
			} else {
				throw new \Exception('找不到配置文件: ' . $file);
			}
		}
	}

	//加载某个配置文件的全部项目
	static public function all($file) {
		//3、判断是否已加载过配置,如已加载过直接返回
		if(isset(self::$conf[$file])) {
			return self::$conf[$file];
		} else {
			//1、判断配置文件是否存在
			$confFile = ROOT . 'config/' . $file . '.conf.php';
			if (file_exists($confFile)) {
				$conf = include $confFile;                //2、加载配置文件
				self::$conf[$file] = $conf;
				return $conf;
			} else {
				throw new \Exception('找不到配置文件: ' . $file);
			}
		}
	}


}