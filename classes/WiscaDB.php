<?php

require_once('DB.php'); // use PearDB

class WiscaDB {

	static $driver = "mysqli";
	static $password = "wisca";
	//static $host = "nhstccom.startlogicmysql.com";
	//static $db = "wisca";
	static $user = "wisca_app";
	static $host = "wiscaorg.ipagemysql.com";
	static $db = "wisca_old";

	public static function get() {
	
		$dsn = "mysqli://".self::$user.":".self::$password."@".self::$host."/".self::$db;
		$conn =& DB::connect ($dsn);
		if (DB::isError ($conn))
			die ("Cannot connect: " . $conn->getMessage () . "\n");
		return $conn;
	}

}

?>