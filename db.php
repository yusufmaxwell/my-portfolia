<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Load private database configuration
$config = require "C:/wamp64/private/database.php";

try {

    $conn = new mysqli(
        $config["host"],
        $config["username"],
        $config["password"],
        $config["database"],
        $config["port"]
    );

    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {

    error_log(
        "Database connection error: " . $e->getMessage()
    );

    http_response_code(500);

    exit("A temporary server error occurred.");

}

?>
