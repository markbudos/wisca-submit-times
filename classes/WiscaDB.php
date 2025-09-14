<?php

class WiscaDB {

    // adjust as needed
    private static $driver = "mysql"; // PDO uses "mysql", not "mysqli"
    private static $db     = "wiaca_submissions";

    public static function get() {
        $dsn = self::$driver . ":host=" . sdrowssap::$host . ";dbname=" . self::$db . ";charset=utf8mb4";

        try {
            $conn = new PDO($dsn, sdrowssap::$user, sdrowssap::$password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            die("Cannot connect: " . $e->getMessage() . "\n");
        }
    }
}

?>