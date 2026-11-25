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


require_once "../db.php";


$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);


if (!$id) {

    header("Location: dashboard.php");

    exit;

}


$stmt = $conn->prepare(
    "SELECT *
     FROM messages
     WHERE id = ?
     LIMIT 1"
);


$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$result =
    $stmt->get_result();


$message =
    $result->fetch_assoc();


$stmt->close();


if (!$message) {

    header("Location: dashboard.php");

    exit;

}


/*
    Mark the message as read.
*/

$update = $conn->prepare(
    "UPDATE messages
     SET status = 'read'
     WHERE id = ?"
);

$update->bind_param(
    "i",
    $id
);

$update->execute();

$update->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        View Message | Yusuf Portfolio
    </title>

    <link
        rel="stylesheet"
        href="admin.css"
    >

</head>

<body>

<header class="admin-header">

    <div>

        <h1>
            Message
        </h1>

        <p>
            View contact request
        </p>

    </div>


    <a
        href="dashboard.php"
        class="logout-btn"
    >
        Back
    </a>

</header>


<main class="message-page">

    <div class="message-card">

        <div class="message-header">

            <div>

                <h2>
                    <?php
                    echo htmlspecialchars(
                        $message["subject"]
                    );
                    ?>
                </h2>

                <p>
                    <?php
                    echo htmlspecialchars(
                        $message["created_at"]
                    );
                    ?>
                </p>

            </div>

            <span class="status read">
                Read
            </span>

        </div>


        <div class="sender-info">

            <div>

                <strong>
                    Name
                </strong>

                <p>
                    <?php
                    echo htmlspecialchars(
                        $message["name"]
                    );
                    ?>
                </p>

            </div>


            <div>

                <strong>
                    Email
                </strong>

                <p>
                    <?php
                    echo htmlspecialchars(
                        $message["email"]
                    );
                    ?>
                </p>

            </div>

        </div>


        <div class="message-body">

            <h3>
                Message
            </h3>

            <p>
                <?php
                echo nl2br(
                    htmlspecialchars(
                        $message["message"]
                    )
                );
                ?>
            </p>

        </div>


        <div class="message-actions">

            <a
                href="mailto:<?php echo htmlspecialchars($message["email"]); ?>"
                class="reply-btn"
            >
                Reply by Email
            </a>

           <form
    action="delete.php"
    method="POST"
    onsubmit="return confirm('Delete this message?');"
>

    <input
        type="hidden"
        name="id"
        value="<?php echo $message["id"]; ?>"
    >

    <input
        type="hidden"
        name="csrf_token"
        value="<?php echo $_SESSION["csrf_token"]; ?>"
    >

    <button
        type="submit"
        class="delete-btn"
    >
        Delete
    </button>

</form>

        </div>

    </div>

</main>

</body>

</html>