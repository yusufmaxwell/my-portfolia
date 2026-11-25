<?php
require_once __DIR__ . "/../config/error_handler.php";

require_once __DIR__ . "/../config/session.php";

// Protect the admin dashboard
if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: login.php");
    exit;
}

// Generate CSRF token
if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once "../db.php";

// Get search and status filters
$search = trim($_GET["search"] ?? "");
$statusFilter = $_GET["status"] ?? "";
// Pagination settings
$limit = 10;

$page = isset($_GET["page"])
    ? max(1, (int)$_GET["page"])
    : 1;

$offset = ($page - 1) * $limit;

// Build SQL query
$sql = "SELECT * FROM messages WHERE 1=1";

$params = [];
$types = "";

// Search messages
if ($search !== "") {

    $sql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";

    $searchValue = "%" . $search . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "sss";
}

// Filter by status
if ($statusFilter === "read" || $statusFilter === "unread") {

    $sql .= " AND status = ?";

    $params[] = $statusFilter;
    $types .= "s";
}

// Sort messages and apply pagination
$sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";

// Add pagination parameters
$params[] = $limit;
$params[] = $offset;

$types .= "ii";

// Prepare query
$stmt = $conn->prepare($sql);

// Bind parameters
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute
$stmt->execute();

$result = $stmt->get_result();

// Number of messages currently displayed
$totalMessages = $result->num_rows;
// Calculate total matching messages
$countSql = "SELECT COUNT(*) AS total FROM messages WHERE 1=1";

$countParams = [];
$countTypes = "";

// Apply the same search filter
if ($search !== "") {

    $countSql .= " AND (name LIKE ? OR email LIKE ? OR subject LIKE ?)";

    $searchValue = "%" . $search . "%";

    $countParams[] = $searchValue;
    $countParams[] = $searchValue;
    $countParams[] = $searchValue;

    $countTypes .= "sss";
}

// Apply the same status filter
if ($statusFilter === "read" || $statusFilter === "unread") {

    $countSql .= " AND status = ?";

    $countParams[] = $statusFilter;

    $countTypes .= "s";
}

// Execute count query
$countStmt = $conn->prepare($countSql);

if (!empty($countParams)) {
    $countStmt->bind_param($countTypes, ...$countParams);
}

$countStmt->execute();

$countResult = $countStmt->get_result();

$totalFilteredMessages = $countResult->fetch_assoc()["total"];

// Calculate total pages
$totalPages = max(1, ceil($totalFilteredMessages / $limit));

// Total messages in database
$totalQuery = $conn->query(
    "SELECT COUNT(*) AS total FROM messages"
);

$totalAllMessages = $totalQuery->fetch_assoc()["total"];

// Total unread messages
$unreadQuery = $conn->query(
    "SELECT COUNT(*) AS total
     FROM messages
     WHERE status = 'unread'"
);

$unreadMessages = $unreadQuery->fetch_assoc()["total"];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Yusuf Portfolio</title>

    <link rel="stylesheet" href="admin.css">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>

<body>

<header class="admin-header">

    <div>

        <h1>Portfolio Dashboard</h1>

        <p>
            Welcome,
            <?php
            echo htmlspecialchars(
                $_SESSION["admin_username"]
            );
            ?>
        </p>

    </div>

    <a href="logout.php" class="logout-btn">
        Logout
    </a>

</header>

<main class="dashboard">

    <!-- Dashboard Statistics -->

    <div class="dashboard-stats">

        <div class="stats-card">

            <div>

                <span>Total Messages</span>

                <h2>
                    <?php echo $totalAllMessages; ?>
                </h2>

            </div>

            <div class="stats-icon">
                <i class="fas fa-envelope"></i>
            </div>

        </div>

        <div class="stats-card">

            <div>

                <span>Unread Messages</span>

                <h2>
                    <?php echo $unreadMessages; ?>
                </h2>

            </div>

            <div class="stats-icon">
                <i class="fas fa-envelope-open-text"></i>
            </div>

        </div>

    </div>


    <!-- Contact Messages -->

    <section class="messages-section">

        <div class="section-heading">

            <div>

                <h2>Contact Messages</h2>

                <p>
                    Messages submitted through your portfolio.
                </p>

            </div>

        </div>


        <!-- Search and Filter -->

        <form
            class="message-search"
            method="GET"
            action="dashboard.php"
        >

            <input
                type="text"
                name="search"
                placeholder="Search name, email or subject..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <select name="status">

                <option value="">
                    All Messages
                </option>

                <option
                    value="unread"
                    <?php
                    echo $statusFilter === "unread"
                        ? "selected"
                        : "";
                    ?>
                >
                    Unread
                </option>

                <option
                    value="read"
                    <?php
                    echo $statusFilter === "read"
                        ? "selected"
                        : "";
                    ?>
                >
                    Read
                </option>

            </select>

            <button type="submit">
                <i class="fas fa-search"></i>
                Search
            </button>

            <a href="dashboard.php" class="clear-search">
                Clear
            </a>

        </form>


        <!-- Messages Table -->

        <?php if ($totalMessages > 0): ?>

            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($message = $result->fetch_assoc()): ?>

                        <tr class="<?php
                            echo $message["status"] === "unread"
                                ? "unread-row"
                                : "";
                        ?>">

                            <td>
                                <?php echo $message["id"]; ?>
                            </td>

                            <td>

                                <?php if ($message["status"] === "unread"): ?>

                                    <span class="unread-dot"></span>

                                <?php endif; ?>

                                <?php
                                echo htmlspecialchars(
                                    $message["name"]
                                );
                                ?>

                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $message["email"]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $message["subject"]
                                );
                                ?>
                            </td>

                            <td>

                                <?php if (
                                    $message["status"] === "unread"
                                ): ?>

                                    <span class="status unread">
                                        Unread
                                    </span>

                                <?php else: ?>

                                    <span class="status read">
                                        Read
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $message["created_at"]
                                );
                                ?>
                            </td>

                            <td>

                                <div class="action-buttons">

                                    <a
                                        href="view.php?id=<?php echo $message["id"]; ?>"
                                        class="view-btn"
                                    >
                                        View
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
                                            value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="delete-btn"
                                        >
                                            Delete
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>
            <!-- Pagination Navigation -->

<?php if ($totalPages > 1): ?>

    <div class="pagination">

        <!-- Previous Button -->

        <?php if ($page > 1): ?>

            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>"
               class="page-btn">
                <i class="fas fa-chevron-left"></i>
                Previous
            </a>

        <?php endif; ?>


        <!-- Page Numbers -->

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>

            <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>"
               class="page-btn <?php echo ($page == $i) ? 'active' : ''; ?>">

                <?php echo $i; ?>

            </a>

        <?php endfor; ?>


        <!-- Next Button -->

        <?php if ($page < $totalPages): ?>

            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($statusFilter); ?>"
               class="page-btn">
                Next
                <i class="fas fa-chevron-right"></i>
            </a>

        <?php endif; ?>

    </div>

<?php endif; ?>

        <?php else: ?>

            <div class="empty-state">

                <div>
                    <i class="fas fa-envelope"></i>
                </div>

                <h3>No messages found</h3>

                <p>
                    Messages from your portfolio contact form
                    will appear here.
                </p>

            </div>

        <?php endif; ?>

    </section>

</main>

</body>
</html>
