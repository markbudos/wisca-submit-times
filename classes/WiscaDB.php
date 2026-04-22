<?php

class WiscaDB {

    // adjust as needed
    private static $driver = "mysql"; // PDO uses "mysql", not "mysqli"
    private static $db     = "wisca_old";

    public static function get() {
        $host = getenv('DB_HOST') ?: sdrowssap::$host;
        $db   = getenv('DB_NAME') ?: self::$db;
        $user = getenv('DB_USER') ?: sdrowssap::$user;
        $pass = getenv('DB_PASS') ?: sdrowssap::$password;
        $dsn  = self::$driver . ":host=" . $host . ";dbname=" . $db . ";charset=utf8mb4";

        try {
            $conn = new PDO($dsn, $user, $pass, [
                PDO::ATTR_TIMEOUT => 3,
            ]);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (PDOException $e) {
            return null;
        }
    }
}

?>