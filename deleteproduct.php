<?php
session_start();
require 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {

    $id = (int) $_POST["id"];

    $stmt = $conn->prepare(
        "DELETE FROM products WHERE id = ?"
    );

    $stmt->execute([$id]);
}

header("Location: products.php");
exit;
?>
