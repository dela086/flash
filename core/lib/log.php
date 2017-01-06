<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/30
 * Time: 上午9:26
 */

namespace core\lib;

use core\lib\conf;
class log
{
	static $logModel;
	static public function init() {
		$drive = conf::get('DRIVE', 'log');
		$class = '\core\drive\log\\' . $drive;
		//if (!isset(self::$logModel) || !is_object(self::$logModel) || is_null(self::$logModel)) self::$logModel = new $class;
		self::$logModel = new $class;
	}


	static public function log($message, $file = 'log') {
		self::$logModel->log($message, $file);
	}


}