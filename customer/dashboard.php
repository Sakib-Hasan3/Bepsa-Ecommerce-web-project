<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('customer');

$success = '';
$error = '';

// Handle add to cart from this page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'], $_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    $qty = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    // Check if enough quantity is available
    $prod = $conn->query("SELECT quantity FROM products WHERE id=$product_id")->fetch_assoc();
    if (!$prod) {
        $error = "Product not found.";
    } elseif ($prod['quantity'] <= 0) {
        $error = "Product is out of stock.";
    } elseif ($prod['quantity'] < $qty) {
        $error = "Insufficient stock. Only {$prod['quantity']} left.";
    } else {
        // Add to cart logic
        $customer_id = $_SESSION['user_id'];
        $existing = $conn->query("SELECT id, quantity FROM carts WHERE customer_id=$customer_id AND product_id=$product_id")->fetch_assoc();
        if ($existing) {
            $new_qty = $existing['quantity'] + $qty;
            if ($prod['quantity'] < $new_qty) {
                $error = "Not enough in stock for your total quantity (current in cart: {$existing['quantity']}, available: {$prod['quantity']}).";
            } else {
                $conn->query("UPDATE carts SET quantity=$new_qty WHERE id={$existing['id']}");
                $success = "Cart updated!";
            }
        } else {
            $conn->query("INSERT INTO carts (customer_id, product_id, quantity) VALUES ($customer_id, $product_id, $qty)");
            $success = "Product added to cart!";
        }
    }
}

// Fetch all products
$res = $conn->query("SELECT * FROM products ORDER BY created_at DESC");

include '../includes/header.php';
?>

<h2>Shop Products</h2>
<?php if ($success): ?>
    <p style="color:green;"><?= $success ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<section class="products">
<?php
if ($res->num_rows > 0):
    while($row = $res->fetch_assoc()):
        $stock = (int)$row['quantity'];
?>
    <div class="product">
        <?php if ($row['image']): ?>
            <img src="../assets/uploads/<?= esc($row['image']) ?>" alt="<?= esc($row['name']) ?>">
        <?php else: ?>
            <img src="../assets/uploads/default.png" alt="No image">
        <?php endif; ?>
        <h3><?= esc($row['name']) ?></h3>
        <p><?= esc(strlen($row['description']) > 80 ? substr($row['description'],0,77).'...' : $row['description']) ?></p>
        <p class="price">Price: $<?= number_format($row['price'],2) ?></p>
        <p style="color:#1976d2;font-weight:500;">Available: <?= $stock ?></p>
        <?php if ($stock > 0): ?>
        <form method="post" style="margin-top:0.7em;">
            <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
            <input type="number" name="quantity" value="1" min="1" max="<?= $stock ?>" style="width:52px;">
            <input type="hidden" name="add_to_cart" value="1">
            <button type="submit" style="width:100%;background:#1976d2;color:#fff;">Add to Cart</button>
        </form>
        <?php else: ?>
            <span style="color:#c00;font-weight:500;">Out of Stock</span>
        <?php endif; ?>
    </div>
<?php endwhile; else: ?>
    <p>No products available yet.</p>
<?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>
