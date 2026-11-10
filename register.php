<?php
require 'config.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm = $_POST["confirm"];

    if (empty($username) || empty($password) || empty($confirm)) {

        $message = "Please fill in all fields.";

    } elseif ($password !== $confirm) {

        $message = "Passwords do not match.";

    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE username = ?"
        );

        $check->execute([$username]);

        if ($check->fetch()) {

            $message = "Username already exists.";

        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "INSERT INTO users (username, password, status, role)
                 VALUES (?, ?, 'pending', 'user')"
            );

            $stmt->execute([$username, $hashedPassword]);

            $message = "Registration successful! Please wait for admin approval.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Flower Shop | Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>Create Account</h2>
    <p class="subtitle">Join our Flower Shop community.</p>

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

        <input type="password"
            name="confirm"
            placeholder="Confirm Password"
            required>

        <button type="submit">Create Account</button>

    </form>

    <p>
        Already have an account?
        <a href="index.php">Sign In here</a>
    </p>

</div>

</body>
</html>
