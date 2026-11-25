<?php

// Prevent session configuration changes after starting
if (session_status() === PHP_SESSION_NONE) {

    // Detect HTTPS
    $isSecure = (
        isset($_SERVER["HTTPS"]) &&
        $_SERVER["HTTPS"] !== "off"
    );

    // Session security settings
    ini_set("session.use_only_cookies", "1");
    ini_set("session.use_strict_mode", "1");
    ini_set("session.cookie_httponly", "1");
    ini_set("session.cookie_secure", $isSecure ? "1" : "0");

    // SameSite protection
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "domain" => "",
        "secure" => $isSecure,
        "httponly" => true,
        "samesite" => "Strict"
    ]);

    // Start session
    session_start();
}

?>
