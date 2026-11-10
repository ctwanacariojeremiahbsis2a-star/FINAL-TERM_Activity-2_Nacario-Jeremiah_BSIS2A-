<?php
session_start();
require 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {

    $id = (int) $_POST["id"];

    if ($id !== (int) $_SESSION["user_id"]) {

        $stmt = $conn->prepare(
            "UPDATE users SET status = 'disabled'
             WHERE id = ? AND role = 'user'"
        );

        $stmt->execute([$id]);
    }
}

header("Location: users.php");
exit;
?>