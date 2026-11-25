<?php
require_once __DIR__ . "/../config/error_handler.php";

require_once __DIR__ . "/../config/session.php";

if (isset($_SESSION["admin_logged_in"])) {

    header("Location: dashboard.php");

    exit;

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin Login | Yusuf Portfolio</title>

    <link
        rel="stylesheet"
        href="admin.css"
    >

</head>

<body>

<div class="login-container">

    <div class="login-card">

        <div class="logo">
            YM
        </div>

        <h1>
            Admin Login
        </h1>

        <p>
            Manage your portfolio messages
        </p>
        <?php if (isset($_GET["error"])): ?>

    <div class="error-message">

        Invalid username or password.

    </div>

<?php endif; ?>


        <form
            action="authenticate.php"
            method="POST"
        >

            <div class="form-group">

                <label>
                    Username
                </label>

                <input type="text" name="username" required >

            </div>


            <div class="form-group">

                <label>
                    Password
                </label>

                <input type="password" name="password" required >

            </div>


            <button type="submit">
                Login
            </button>

        </form>

    </div>

</div>

</body>

</html>