<?php
session_start();
require 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

$stmt = $conn->query(
    "SELECT id, username, status, role, created_at
     FROM users ORDER BY id DESC"
);

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">

    <h2>User Management</h2>
    <p class="subtitle">Manage registered Flower Shop accounts.</p>

    <a class="action-link" href="dashboard.php">Back to Dashboard</a>

    <div class="table-wrapper">
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user["id"]; ?></td>

                <td>
                    <?php echo htmlspecialchars($user["username"]); ?>
                </td>

                <td><?php echo htmlspecialchars($user["role"]); ?></td>

                <td>
                    <span class="status">
                        <?php echo htmlspecialchars($user["status"]); ?>
                    </span>
                </td>

                <td>
                    <?php if ($user["role"] !== "admin"): ?>

                        <?php if ($user["status"] === "pending"): ?>

                            <form method="POST" action="approveuser.php">
                                <input type="hidden" name="id"
                                    value="<?php echo $user["id"]; ?>">
                                <button type="submit" class="small-button">
                                    Approve
                                </button>
                            </form>

                        <?php endif; ?>

                        <?php if ($user["status"] !== "disabled"): ?>

                            <form method="POST" action="disableuser.php"
                                onsubmit="return confirm('Disable this account?');">

                                <input type="hidden" name="id"
                                    value="<?php echo $user["id"]; ?>">

                                <button type="submit" class="small-button danger">
                                    Disable
                                </button>
                            </form>

                        <?php endif; ?>

                    <?php else: ?>
                        <span>Admin Account</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>

        </table>
    </div>

</div>

</body>
</html>
