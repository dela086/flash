<?php

/**
 * Created by PhpStorm.
 * User: dela_xu
 * Date: 2016/12/4
 * Time: 下午3:57
 */
include APP . "models/api/taobao/TopSdk.php";
ini_set('memory_limit','256M');
require_once APP . 'models/api/taobao/Autoloader.php';
class tbk{
	static $tbkModel ;
	static $tabItem;
	static $itemInfo;
	public function __construct(){
/*
		if (!file_exists($apiFile)) {
			console("Taobaoke SDK not found" . $apiFile);
			throw new \Exception("Taobaoke SDK not found" . $apiFile);
			return false;
		}

		require_once $apiFile;*/
	}

	public function get_TopClient () {
		if (!isset(self::$tbkModel) || is_null(self::$tbkModel) || !is_object(self::$tbkModel)) {
			self::$tbkModel = new \TopClient();
		}
		return self::$tbkModel;
	}


	public function get_tbkItem() {
		if (!isset(self::$tabItem) || is_null(self::$tabItem) || !is_object(self::$tabItem)) {
			self::$tabItem = new \TbkItemGetRequest();
		}
		return self::$tabItem;
	}

	public function get_ItemInfo() {
		if (!isset(self::$itemInfo) || is_null(self::$itemInfo) || !is_object(self::$itemInfo)) {
			self::$itemInfo = new \TbkItemInfoGetRequest();
		}
		return self::$itemInfo;
	}
}