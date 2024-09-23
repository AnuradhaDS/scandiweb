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

// Abstract class for product
abstract class Product
{
    protected $id;
    protected $sku;
    protected $name;
    protected $price;
    protected $type;

    public function __construct($id, $sku, $name, $price, $type)
    {
        $this->setId($id);
        $this->setSku($sku);
        $this->setName($name);
        $this->setPrice($price);
        $this->setType($type);
    }

    // Getters and setters for common properties
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getSku() {
        return $this->sku;
    }

    public function setSku($sku) {
        $this->sku = $sku;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getPrice() {
        return $this->price;
    }

    public function setPrice($price) {
        $this->price = $price;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
    }

    // Save the product and handle duplicate SKU
    public function save($conn) {
        // Check if SKU already exists
        $stmt = $conn->prepare("SELECT id FROM products WHERE sku = ?");
        $stmt->bind_param("s", $this->sku);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->close();
            // SKU exists, throw an exception
            throw new Exception("Duplicate SKU entry: " . $this->sku);
        }

        $stmt->close();

        // Proceed to insert the product
        $stmt = $conn->prepare("INSERT INTO products (sku, name, price, type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $this->sku, $this->name, $this->price, $this->type);
        $stmt->execute();
        $this->id = $stmt->insert_id;
        $stmt->close();
    }

    abstract public function getDetails();
}

// DVD class
class DVD extends Product
{
    private $size;

    public function __construct($id, $sku, $name, $price, $size)
    {
        parent::__construct($id, $sku, $name, $price, 'DVD');
        $this->setSize($size);
    }

    public function getSize() {
        return $this->size;
    }

    public function setSize($size) {
        $this->size = $size;
    }

    public function save($conn) {
        parent::save($conn);
        $stmt = $conn->prepare("UPDATE products SET size = ? WHERE id = ?");
        $stmt->bind_param("di", $this->size, $this->getId());
        $stmt->execute();
        $stmt->close();
    }

    public function getDetails() {
        return "Size: " . $this->getSize() . " MB";
    }
}

// Book class
class Book extends Product
{
    private $weight;

    public function __construct($id, $sku, $name, $price, $weight)
    {
        parent::__construct($id, $sku, $name, $price, 'Book');
        $this->setWeight($weight);
    }

    public function getWeight() {
        return $this->weight;
    }

    public function setWeight($weight) {
        $this->weight = $weight;
    }

    public function save($conn) {
        parent::save($conn);
        $stmt = $conn->prepare("UPDATE products SET weight = ? WHERE id = ?");
        $stmt->bind_param("di", $this->weight, $this->getId());
        $stmt->execute();
        $stmt->close();
    }

    public function getDetails() {
        return "Weight: " . $this->getWeight() . " KG";
    }
}

// Furniture class
class Furniture extends Product
{
    private $height;
    private $width;
    private $length;

    public function __construct($id, $sku, $name, $price, $height, $width, $length)
    {
        parent::__construct($id, $sku, $name, $price, 'Furniture');
        $this->setHeight($height);
        $this->setWidth($width);
        $this->setLength($length);
    }

    public function getHeight() {
        return $this->height;
    }

    public function setHeight($height) {
        $this->height = $height;
    }

    public function getWidth() {
        return $this->width;
    }

    public function setWidth($width) {
        $this->width = $width;
    }

    public function getLength() {
        return $this->length;
    }

    public function setLength($length) {
        $this->length = $length;
    }

    public function save($conn) {
        parent::save($conn);
        $stmt = $conn->prepare("UPDATE products SET height = ?, width = ?, length = ? WHERE id = ?");
        $stmt->bind_param("dddi", $this->height, $this->width, $this->length, $this->getId());
        $stmt->execute();
        $stmt->close();
    }

    public function getDetails() {
        return "Dimensions: " . $this->getHeight() . "x" . $this->getWidth() . "x" . $this->getLength() . " CM";
    }
}

// ProductFactory to create products dynamically
class ProductFactory
{
    public static function createProduct($data)
    {
        $id = null;
        $sku = $data['sku'];
        $name = $data['name'];
        $price = $data['price'];

        switch ($data['type']) {
            case 'DVD':
                return new DVD($id, $sku, $name, $price, $data['size']);
            case 'Book':
                return new Book($id, $sku, $name, $price, $data['weight']);
            case 'Furniture':
                return new Furniture($id, $sku, $name, $price, $data['height'], $data['width'], $data['length']);
            default:
                throw new Exception("Invalid product type.");
        }
    }
}

// Handling form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get product data from POST and create product
        $product = ProductFactory::createProduct($_POST);
        $product->save($conn);
        header('Location: index.php');
        exit();
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="header">
        <a href="index.php" class="btn-cancel">Cancel</a>
        <button form="product_form" type="submit" class="btn-add-product">Add Product</button>
    </div>

    <h1>Add Product</h1>
    <form method="POST" action="product.php" id="product_form">
        <div class="form-group">
            <label for="sku">SKU:</label>
            <input type="text" id="sku" name="sku" required>
        </div>
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        <div class="form-group">
            <label for="price">Price:</label>
            <input type="number" step="0.01" id="price" name="price" required>
        </div>
        <div class="form-group">
            <label for="type">Type:</label>
            <select id="type" name="type" onchange="toggleFields(this.value)" required>
                <option value="">Select a type</option>
                <option value="DVD">DVD</option>
                <option value="Book">Book</option>
                <option value="Furniture">Furniture</option>
            </select>
        </div>

        <div id="dvd-fields" style="display: none;">
            <div class="form-group">
                <label for="size">Size (MB):</label>
                <input type="number" id="size" name="size">
            </div>
        </div>
        <div id="book-fields" style="display: none;">
            <div class="form-group">
                <label for="weight">Weight (KG):</label>
                <input type="number" id="weight" name="weight">
            </div>
        </div>
        <div id="furniture-fields" style="display: none;">
            <div class="form-group">
                <label for="height">Height (CM):</label>
                <input type="number" id="height" name="height">
            </div>
            <div class="form-group">
                <label for="width">Width (CM):</label>
                <input type="number" id="width" name="width">
            </div>
            <div class="form-group">
                <label for="length">Length (CM):</label>
                <input type="number" id="length" name="length">
            </div>
        </div>
    </form>

    <script>
        function toggleFields(type) {
            document.getElementById('dvd-fields').style.display = type === 'DVD' ? 'block' : 'none';
            document.getElementById('book-fields').style.display = type === 'Book' ? 'block' : 'none';
            document.getElementById('furniture-fields').style.display = type === 'Furniture' ? 'block' : 'none';
        }
    </script>
</body>
</html>
