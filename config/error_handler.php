<?php

// Disable displaying errors to website visitors
ini_set("display_errors", "0");
ini_set("display_startup_errors", "0");

// Enable private error logging
ini_set("log_errors", "1");

// Set reporting level
error_reporting(E_ALL);

// Use a private log directory
$logDirectory = dirname(__DIR__) . "/private_logs";

// Create directory if it does not exist
if (!is_dir($logDirectory)) {

    mkdir($logDirectory, 0750, true);

}

// Configure error log
ini_set(
    "error_log",
    $logDirectory . "/php-errors.log"
);

?>
