<?php
// Database connection
$host = 'fdb1032.awardspace.net'; 
$db = '4489210_mydatabase'; 
$user = '4489210_mydatabase';      
$pass = '[ZY*H]2)4}JpC0B1';          

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle mass delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    if (!empty($_POST['product_ids'])) {
        $ids = implode(',', array_map('intval', $_POST['product_ids']));
        $query = "DELETE FROM products WHERE id IN ($ids)";
        $conn->query($query);
    } else {
        echo "No products selected for deletion.";
    }
}

// Fetch all products
$products = $conn->query("SELECT * FROM products ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product List</title>
    <a href="/all" id="view-all-products"></a>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <a href="product.php" class="btn-add-product" id="add-product-link">Add Product</a> <!-- Added id for QA -->
        <form method="POST" action="index.php" id="mass-delete-form">
            <button type="submit" name="delete" class="btn-delete" id="mass-delete-button">Mass Delete</button>
        </form>
    </div>

    <h1 id="product-list-title">Product List</h1> <!-- Added id for QA -->
    
    <div class="tiles-container">
        <?php while ($product = $products->fetch_assoc()): ?>
            <div class="product-tile" data-testid="product-<?= strtolower(str_replace(' ', '-', $product['name'])) ?>"> <!-- Added data-testid -->
                <input form="mass-delete-form" type="checkbox" name="product_ids[]" value="<?= $product['id'] ?>" class="delete-checkbox">
                <p class="product-sku" id="product-sku-<?= $product['id'] ?>">SKU: <?= $product['sku'] ?></p> <!-- Added id for QA -->
                <p class="product-name" id="product-name-<?= $product['id'] ?>">Name: <?= $product['name'] ?></p> <!-- Added id for QA -->
                <p class="product-price" id="product-price-<?= $product['id'] ?>">Price: $<?= $product['price'] ?></p> <!-- Added id for QA -->
                <p>Type: <?= $product['type'] ?></p>

                <?php if ($product['type'] == 'DVD'): ?>
                    <p class="product-attribute size" id="product-size-<?= $product['id'] ?>">Size: <?= $product['size'] ?> MB</p> <!-- Added id for QA -->
                <?php elseif ($product['type'] == 'Book'): ?>
                    <p class="product-attribute weight" id="product-weight-<?= $product['id'] ?>">Weight: <?= $product['weight'] ?> KG</p> <!-- Added id for QA -->
                <?php elseif ($product['type'] == 'Furniture'): ?>
                    <p class="product-attribute dimensions" id="product-dimensions-<?= $product['id'] ?>">Dimensions: <?= $product['height'] ?>x<?= $product['width'] ?>x<?= $product['length'] ?> CM</p> <!-- Added id for QA -->
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

</body>
</html>

<?php $conn->close(); ?>
