<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('customer');

$customer_id = $_SESSION['user_id'];
$error = '';
$success = '';
$address = '';
$city = '';
$postal_code = '';
$phone = '';

// Fetch default address from profile
$stmt = $conn->prepare("SELECT address, city, postal_code, phone FROM users WHERE id=?");
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$stmt->bind_result($def_address, $def_city, $def_postal_code, $def_phone);
$stmt->fetch();
$stmt->close();

// Fetch and revalidate cart items before checkout
$cart_items = $conn->query(
    "SELECT c.id as cart_id, c.quantity AS cart_qty, p.* 
     FROM carts c 
     LEFT JOIN products p ON c.product_id = p.id 
     WHERE c.customer_id = $customer_id"
);

$cart_data = [];
$total = 0;
$removed_items = [];
if ($cart_items->num_rows > 0) {
    while ($item = $cart_items->fetch_assoc()) {
        if (!$item['id'] || !isset($item['name'])) {
            $conn->query("DELETE FROM carts WHERE id={$item['cart_id']}");
            $removed_items[] = "A deleted product";
            continue;
        }
        $max_stock = (int)$item['quantity'];
        $cart_qty = (int)$item['cart_qty'];
        if ($max_stock <= 0) {
            $conn->query("DELETE FROM carts WHERE id={$item['cart_id']}");
            $removed_items[] = $item['name'] . " (out of stock)";
            continue;
        }
        if ($cart_qty > $max_stock) {
            $conn->query("UPDATE carts SET quantity=$max_stock WHERE id={$item['cart_id']}");
            $cart_qty = $max_stock;
            $removed_items[] = "{$item['name']} (quantity adjusted to stock)";
        }
        $item['cart_qty'] = $cart_qty;
        $item['subtotal'] = $cart_qty * $item['price'];
        $cart_data[] = $item;
        $total += $item['subtotal'];
    }
}
if ($removed_items) {
    $error .= " The following items were removed or adjusted due to insufficient stock: " . implode(', ', $removed_items) . ".";
}

// Handle order placement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address_option = $_POST['address_option'] ?? 'default';
    if ($address_option == 'default') {
        // Use default profile address
        $address = $def_address;
        $city = $def_city;
        $postal_code = $def_postal_code;
        $phone = $def_phone;
        if (!$address || !$city || !$postal_code || !$phone) {
            $error = "Your default address is incomplete. Please update your profile or enter a new address.";
        }
    } else {
        // New address
        $address = trim($_POST['address'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $postal_code = trim($_POST['postal_code'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        if (!$address || !$city || !$postal_code || !$phone) {
            $error = "Please fill in all address fields.";
        } elseif (strlen($address) > 255 || strlen($city) > 100 || strlen($postal_code) > 20 || strlen($phone) > 30) {
            $error = "One or more address fields are too long.";
        }
    }

    if (!$error && count($cart_data)) {
        // Double-check stock before finalizing order
        foreach ($cart_data as $item) {
            $pid = $item['id'];
            $row = $conn->query("SELECT quantity FROM products WHERE id=$pid")->fetch_assoc();
            if (!$row || $row['quantity'] < $item['cart_qty']) {
                $error = "Stock changed for {$item['name']}. Please review your cart.";
                break;
            }
        }
        if (!$error) {
            // Create order with address info
            $stmt = $conn->prepare("INSERT INTO orders (customer_id, address, city, postal_code, phone, total, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param('issssd', $customer_id, $address, $city, $postal_code, $phone, $total);
            $stmt->execute();
            $order_id = $stmt->insert_id;
            $stmt->close();

            // Add order items and update product stock
            foreach ($cart_data as $item) {
                $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('iiid', $order_id, $item['id'], $item['cart_qty'], $item['price']);
                $stmt->execute();
                $stmt->close();
                // Decrement stock
                $conn->query("UPDATE products SET quantity = quantity - {$item['cart_qty']} WHERE id = {$item['id']}");
            }

            // Clear cart
            $conn->query("DELETE FROM carts WHERE customer_id = $customer_id");

            $success = "Order placed successfully! <a href='orders.php'>View Orders</a>";
            unset($cart_data); // Hide the form
        }
    }
}

include '../includes/header.php';
?>

<h2>Checkout</h2>
<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p style="color:green;"><?= $success ?></p>
<?php endif; ?>

<?php if (isset($cart_data) && count($cart_data)): ?>
    <form method="post">
        <fieldset style="margin-bottom:24px;border:1px solid #b5c6e0;padding:18px 20px 16px 20px;border-radius:7px;">
            <legend style="font-size:1.1em;font-weight:600;">Delivery Address</legend>
            <label>
                <input type="radio" name="address_option" value="default"
                    <?= (!isset($_POST['address_option']) || $_POST['address_option']=='default') ? 'checked' : '' ?>>
                Use my default address
            </label>
            <div id="default_address_block" style="margin:10px 0 18px 24px;<?= (isset($_POST['address_option']) && $_POST['address_option']=='new') ? 'display:none;' : '' ?>">
                <b>Address:</b> <?= esc($def_address) ?><br>
                <b>City:</b> <?= esc($def_city) ?><br>
                <b>Postal Code:</b> <?= esc($def_postal_code) ?><br>
                <b>Phone:</b> <?= esc($def_phone) ?>
            </div>
            <label>
                <input type="radio" name="address_option" value="new"
                    <?= (isset($_POST['address_option']) && $_POST['address_option']=='new') ? 'checked' : '' ?>>
                Enter a new delivery address
            </label>
            <div id="new_address_block" style="margin:10px 0 10px 24px;<?= (!isset($_POST['address_option']) || $_POST['address_option']=='default') ? 'display:none;' : '' ?>">
                <label>Address:<br>
                    <input type="text" name="address" value="<?= esc($address) ?>" maxlength="255" style="width:95%;">
                </label><br>
                <label>City:<br>
                    <input type="text" name="city" value="<?= esc($city) ?>" maxlength="100" style="width:60%;">
                </label><br>
                <label>Postal Code:<br>
                    <input type="text" name="postal_code" value="<?= esc($postal_code) ?>" maxlength="20" style="width:40%;">
                </label><br>
                <label>Phone:<br>
                    <input type="text" name="phone" value="<?= esc($phone) ?>" maxlength="30" style="width:60%;">
                </label>
            </div>
        </fieldset>

        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Image</th>
                    <th>Unit Price (৳)</th>
                    <th>Quantity</th>
                    <th>Subtotal (৳)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_data as $item): ?>
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
                        <td><?= (int)$item['cart_qty'] ?></td>
                        <td><?= number_format($item['subtotal'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;"><strong>Total:</strong></td>
                    <td><strong>৳<?= number_format($total, 2) ?></strong></td>
                </tr>
            </tfoot>
        </table>
        <div style="margin-top:1.5em;">
            <button type="submit" style="width:200px;height:44px;background:#1976d2;color:#fff;border:none;border-radius:7px;font-size:1.1em;">Confirm Order</button>
            <a href="cart.php" style="margin-left:24px;font-size:1em;color:#1976d2;">Back to Cart</a>
        </div>
    </form>
    <script>
    document.querySelectorAll('input[name="address_option"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'default') {
                document.getElementById('default_address_block').style.display = '';
                document.getElementById('new_address_block').style.display = 'none';
            } else {
                document.getElementById('default_address_block').style.display = 'none';
                document.getElementById('new_address_block').style.display = '';
            }
        });
    });
    </script>
<?php elseif (!$success): ?>
    <p>Your cart is empty or all items are out of stock. <a href="dashboard.php">Shop now!</a></p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
