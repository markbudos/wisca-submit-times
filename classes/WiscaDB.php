<?php

require_once('DB.php'); // use PearDB

class WiscaDB {

	static $driver = "mysqli";
	static $db = "wisca_old";

	public static function get() {
	
		$dsn = "mysqli://".sdrowssap::$user.":".sdrowssap::$password."@".sdrowssap::$host."/".self::$db;
		$conn = DB::connect ($dsn);
		if (DB::isError ($conn))
			die ("Cannot connect: " . $conn->getMessage () . "\n");
		return $conn;
	}

}

?>
