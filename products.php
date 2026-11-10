<?php
session_start();
require 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit;
}

$checkUser = $conn->prepare(
    "SELECT status, role FROM users WHERE id = ?"
);
$checkUser->execute([$_SESSION["user_id"]]);
$currentUser = $checkUser->fetch(PDO::FETCH_ASSOC);

if (!$currentUser || $currentUser["status"] !== "approved") {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

$_SESSION["role"] = $currentUser["role"];

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && $_SESSION["role"] === "admin") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);
    $quantity = filter_var($_POST["quantity"], FILTER_VALIDATE_INT);
    $price = filter_var($_POST["price"], FILTER_VALIDATE_FLOAT);

    if ($name === "" || $quantity === false || $quantity < 0 ||
        $price === false || $price < 0) {

        $message = "Please enter valid product information.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO products
            (name, description, quantity, price, created_by)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $name,
            $description,
            $quantity,
            $price,
            $_SESSION["user_id"]
        ]);

        header("Location: products.php?added=1");
        exit;
    }
}

$stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Management</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="page-container">

    <h2>Flower Shop Products</h2>
    <p class="subtitle">Manage and view available flowers and products.</p>

    <a class="action-link" href="dashboard.php">Back to Dashboard</a>

    <?php if (isset($_GET["added"])): ?>
        <p class="success-message">Product added successfully!</p>
    <?php endif; ?>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <?php if ($_SESSION["role"] === "admin"): ?>

        <div class="form-card">

            <h3>Add New Product</h3>

            <form method="POST">

                <input type="text" name="name"
                    placeholder="Product Name" required>

                <textarea name="description"
                    placeholder="Product Description"></textarea>

                <input type="number" name="quantity"
                    placeholder="Quantity" min="0" required>

                <input type="number" name="price"
                    placeholder="Price" min="0" step="0.01" required>

                <button type="submit">Add Product</button>

            </form>

        </div>

    <?php endif; ?>

    <h3 class="section-heading">Product List</h3>

    <div class="table-wrapper">

        <table>

            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Quantity</th>
                <th>Price</th>

                <?php if ($_SESSION["role"] === "admin"): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>

            <?php if (count($products) > 0): ?>

                <?php foreach ($products as $product): ?>

                    <tr>

                        <td><?php echo $product["id"]; ?></td>

                        <td><?php echo htmlspecialchars($product["name"]); ?></td>

                        <td><?php echo htmlspecialchars($product["description"] ?? ""); ?></td>

                        <td><?php echo $product["quantity"]; ?></td>

                        <td>₱<?php echo number_format($product["price"], 2); ?></td>

                        <?php if ($_SESSION["role"] === "admin"): ?>

                            <td>
                                <a href="editproduct.php?id=<?php echo $product["id"]; ?>">
                                    Edit
                                </a>

                                <form method="POST" action="deleteproduct.php"
                                    onsubmit="return confirm('Delete this product?');">

                                    <input type="hidden" name="id"
                                        value="<?php echo $product["id"]; ?>">

                                    <button type="submit" class="small-button danger">
                                        Delete
                                    </button>

                                </form>
                            </td>

                        <?php endif; ?>

                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="<?php echo $_SESSION["role"] === "admin" ? 6 : 5; ?>">
                        No products available.
                    </td>
                </tr>

            <?php endif; ?>

        </table>

    </div>

</div>

</body>
</html>
