<?php
session_start();
require 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_GET["id"])) {
    header("Location: products.php");
    exit;
}

$id = (int) $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $quantity = filter_var($_POST["quantity"], FILTER_VALIDATE_INT);
    $price = filter_var($_POST["price"], FILTER_VALIDATE_FLOAT);

    if ($name === "" || $quantity === false || $quantity < 0 ||
        $price === false || $price < 0) {

        $message = "Please enter valid product information.";

    } else {

        $update = $conn->prepare(
            "UPDATE products
             SET name = ?, description = ?, quantity = ?, price = ?
             WHERE id = ?"
        );

        $update->execute([
            $name,
            $description,
            $quantity,
            $price,
            $id
        ]);

        header("Location: products.php?updated=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <h2>Edit Product</h2>

    <?php if ($message !== ""): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="POST">

        <input type="text" name="name"
            value="<?php echo htmlspecialchars($product["name"]); ?>"
            placeholder="Product Name" required>

        <textarea name="description"
            placeholder="Description"><?php echo htmlspecialchars($product["description"] ?? ""); ?></textarea>

        <input type="number" name="quantity"
            value="<?php echo $product["quantity"]; ?>"
            min="0" required>

        <input type="number" name="price"
            value="<?php echo $product["price"]; ?>"
            min="0" step="0.01" required>

        <button type="submit">Update Product</button>

    </form>

    <p><a href="products.php">Cancel</a></p>

</div>

</body>
</html>