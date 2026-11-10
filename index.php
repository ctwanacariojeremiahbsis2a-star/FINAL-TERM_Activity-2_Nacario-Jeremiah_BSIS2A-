<?php
session_start();
require 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT * FROM users WHERE username = ?"
    );

    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {

        // Support existing plain-text accounts and hashed passwords
        $validPassword = password_verify($password, $user["password"])
            || $password === $user["password"];

        if ($validPassword) {

            if ($user["status"] !== "approved") {

                if ($user["status"] === "disabled") {
                    $message = "Your account has been disabled.";
                } else {
                    $message = "Your account is waiting for admin approval.";
                }

            } else {

                // Upgrade old plain-text passwords after successful login
                if (!password_verify($password, $user["password"])) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                    $update = $conn->prepare(
                        "UPDATE users SET password = ? WHERE id = ?"
                    );
                    $update->execute([$hashedPassword, $user["id"]]);
                }

                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["role"] = $user["role"];

                header("Location: dashboard.php");
                exit;
            }

        } else {
            $message = "Invalid username or password.";
        }

    } else {
        $message = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Flower Shop | Sign In</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>Welcome Back</h2>
    <p class="subtitle">Sign in to your Flower Shop account.</p>

    <?php if ($message != ""): ?>
        <div class="message">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input type="text"
            name="username"
            placeholder="Enter Username"
            required>

        <input type="password"
            name="password"
            placeholder="Enter Password"
            required>

        <button type="submit">Sign In</button>

    </form>

    <p>
        Don't have an account?
        <a href="register.php">Register here</a>
    </p>

</div>

</body>
</html>
