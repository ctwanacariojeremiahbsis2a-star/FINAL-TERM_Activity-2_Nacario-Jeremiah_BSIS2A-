<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

require 'config.php';

$stmt = $conn->prepare(
    "SELECT status, role FROM users WHERE id = ?"
);
$stmt->execute([$_SESSION["user_id"]]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || $currentUser["status"] !== "approved") {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

$_SESSION["role"] = $currentUser["role"];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Flower Shop | Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="dashboard">

    <h2>Welcome to Flower Shop!</h2>

    <p>Hello, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>

    <p>
        Account type:
        <?php echo htmlspecialchars($_SESSION["role"]); ?>
    </p>

    <?php if ($_SESSION["role"] === "admin"): ?>

        <h3>Administrator Panel</h3>

        <a class="action-link" href="users.php">User Management</a>
        <a class="action-link" href="products.php">Product Management</a>

    <?php else: ?>

        <h3>Customer Panel</h3>

        <a class="action-link" href="products.php">View Products</a>

    <?php endif; ?>

    <a class="action-link logout-link" href="logout.php">Logout</a>

</div>

</body>
</html>
