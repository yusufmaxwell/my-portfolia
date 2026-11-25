<?php
require_once __DIR__ . "/../config/error_handler.php";

require_once __DIR__ . "/../config/session.php";

require_once __DIR__ . "/../db.php";

// Allow POST requests only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

// Get login details
$username = trim($_POST["username"] ?? "");
$password = ($_POST["password"] ?? "");

// Check empty fields
if ($username === "" || $password === "") {
    header("Location: login.php?error=1");
    exit;
}

// Find administrator
$stmt = $conn->prepare(
    "SELECT id, username, password_harsh
     FROM admins
     WHERE username = ?
     LIMIT 1"
);


// Execute query
$stmt->bind_param("s", $username);
$stmt->execute();

$result = $stmt->get_result();
$admin = $result->fetch_assoc();

$stmt->close();

// Verify password
if (
    $admin &&
    password_verify($password, $admin["password_harsh"])
) {
    session_regenerate_id(true);

    $_SESSION["admin_logged_in"] = true;
    $_SESSION["admin_id"] = $admin["id"];
    $_SESSION["admin_username"] = $admin["username"];

    header("Location: dashboard.php");
    exit;
}

// Login failed
header("Location: login.php?error=1");
exit;

?>
