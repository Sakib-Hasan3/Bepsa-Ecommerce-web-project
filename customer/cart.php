<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('customer');

$customer_id = $_SESSION['user_id'];
$error = '';
$success = '';

if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $conn->query("DELETE FROM carts WHERE id=$cart_id AND customer_id=$customer_id");
    $success = "Item removed from cart.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'], $_POST['cart_id'])) {
    $cart_id = intval($_POST['cart_id']);
    $qty = max(1, intval($_POST['quantity']));

    $prod = $conn->query("SELECT p.quantity, c.product_id FROM carts c JOIN products p ON c.product_id=p.id WHERE c.id=$cart_id AND c.customer_id=$customer_id")->fetch_assoc();
    if (!$prod) {
        $conn->query("DELETE FROM carts WHERE id=$cart_id AND customer_id=$customer_id");
        $error = "Product was deleted. Removed from cart.";
    } elseif ($prod['quantity'] == 0) {
        $conn->query("DELETE FROM carts WHERE id=$cart_id AND customer_id=$customer_id");
        $error = "Product is now out of stock. Removed from cart.";
    } elseif ($prod['quantity'] < $qty) {
        $conn->query("UPDATE carts SET quantity={$prod['quantity']} WHERE id=$cart_id AND customer_id=$customer_id");
        $error = "Stock changed: quantity set to {$prod['quantity']}.";
    } else {
        $conn->query("UPDATE carts SET quantity=$qty WHERE id=$cart_id AND customer_id=$customer_id");
        $success = "Cart quantity updated.";
    }
}

$cart_items_raw = $conn->query(
    "SELECT c.id as cart_id, c.quantity AS cart_qty, p.* 
     FROM carts c 
     LEFT JOIN products p ON c.product_id = p.id 
     WHERE c.customer_id = $customer_id"
);

$cart_items = [];
$total = 0;
$removed_items = [];
if ($cart_items_raw->num_rows) {
    while ($item = $cart_items_raw->fetch_assoc()) {
        if (!$item['id'] || !isset($item['name'])) {
            $conn->query("DELETE FROM carts WHERE id={$item['cart_id']}");
            $removed_items[] = "A deleted product";
            continue;
        }
        if ((int)$item['quantity'] == 0) {
            $conn->query("DELETE FROM carts WHERE id={$item['cart_id']}");
            $removed_items[] = $item['name'] . " (out of stock)";
            continue;
        }
        $cart_qty = min((int)$item['cart_qty'], (int)$item['quantity']);
        $item['cart_qty'] = $cart_qty;
        $item['subtotal'] = $cart_qty * $item['price'];
        $cart_items[] = $item;
        $total += $item['subtotal'];
    }
}
if ($removed_items) {
    $error .= " The following items were removed from your cart: " . implode(', ', $removed_items) . ".";
}

include '../includes/header.php';
?>

<h2>My Cart</h2>
<?php if ($success): ?>
    <p style="color:green;"><?= $success ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<?php if (count($cart_items)): ?>
    <form method="post" action="checkout.php">
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Image</th>
                <th>Unit Price (৳)</th>
                <th>Quantity</th>
                <th>Subtotal (৳)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($cart_items as $item): ?>
            <tr>
                <td><?= esc($item['name']) ?></td>
                <td>
                    <?php if ($item['image']): ?>
                        <img src="../assets/uploads/<?= esc($item['image']) ?>" style="width:60px;height:45px;object-fit:cover;">
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </td>
                <td><?= number_format($item['price'], 2) ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                        <input type="number" name="quantity" value="<?= $item['cart_qty'] ?>" min="1" max="<?= (int)$item['quantity'] ?>" style="width:55px;">
                        <button type="submit" name="update_qty" style="padding:2px 10px;font-size:12px;">Update</button>
                        <span style="font-size:12px;color:#1976d2;">(Max: <?= (int)$item['quantity'] ?>)</span>
                    </form>
                </td>
                <td><?= number_format($item['subtotal'], 2) ?></td>
                <td>
                    <a href="cart.php?remove=<?= $item['cart_id'] ?>" onclick="return confirm('Remove item from cart?');" style="color:#c00;">Remove</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
                <td colspan="2"><strong>$<?= number_format($total, 2) ?></strong></td>
            </tr>
        </tfoot>
    </table>
    <div style="margin-top:1.5em;">
        <button type="submit" style="width:180px;height:50px;background:#1976d2;color:#fff;border:none;border-radius:7px;font-size:0.8em;" <?= $total == 0 ? 'disabled' : '' ?>>Proceed to Checkout</button>
    </div>
    </form>
<?php else: ?>
    <p>Your cart is empty. <a href="dashboard.php">Shop now!</a></p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
