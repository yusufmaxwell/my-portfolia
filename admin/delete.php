<?php
require_once __DIR__ . "/../config/error_handler.php";

require_once __DIR__ . "/../config/session.php";


if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {

    header("Location: login.php");

    exit;

}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: dashboard.php");

    exit;

}


if (
    !isset($_POST["csrf_token"]) ||
    !hash_equals(
        $_SESSION["csrf_token"],
        $_POST["csrf_token"]
    )
) {

    http_response_code(403);

    exit("Invalid request.");

}


require_once "../db.php";


$id = filter_input(
    INPUT_POST,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id) {

    header("Location: dashboard.php");

    exit;

}


$stmt = $conn->prepare(
    "DELETE FROM messages WHERE id = ?"
);


$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$stmt->close();

$conn->close();


header("Location: dashboard.php");

exit;

?>