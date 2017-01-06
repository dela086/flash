<?php
/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/11/29
 * Time: 下午5:40
 */

define('ROOT', dirname(__FILE__) . '/');    //根路径
define('CORE', ROOT.'core/');			//核心文件路径
define('APP', ROOT.'app/');				//APP路径
define('MODULE', 'app');				//命名空间app路径,专业写法

define('POSTFIX', '.html');				//自定义view层文件后缀,可以为 .tpl  .htm

define('DEBUG', true);

define('RE_URL', 1);					//URL 模式, 1为PATHINFO 如:http://yhq.pillowabc.com/index.php/index/getUserInfo
										// 2 为 普通模式 http://域名/项目名/入口文件?m=模块名&a=方法名&键1=值1&键2=值2

date_default_timezone_set('Asia/Shanghai'); // 设置默认时区

if (DEBUG) {
	ini_set('display_errors', 'On');
} else {
	ini_set('display_errors', 'Off');
}

session_start();

include CORE . 'common/common.php';
include CORE . 'flash.php';
include ROOT . 'vendor/autoload.php';
spl_autoload_register('\core\flash::autoload');

\core\flash::run();