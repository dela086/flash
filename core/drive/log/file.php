<?php

/**
 * 日志驱动类   文件日志
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/30
 * Time: 上午9:33
 */
namespace core\drive\log;

use core\lib\conf;

class file{
	public $logPath;
	public function __construct()
	{
		$conf =  conf::get('OPTION', 'log');
		$this->logPath = $conf['PATH'];
	}

	public function log($message, $file = 'log') {

		if (!is_dir($this->logPath)) {
			mkdir($this->logPath, '0777', true);
		}

		$logPath = $this->logPath . date('YmdH') . '-' . $file . '.log';
		$message = date('Y-m-d H:i:s') . '    ' . PHP_EOL . $message;
		return file_put_contents($logPath, $message . PHP_EOL . PHP_EOL, FILE_APPEND);
	}
}