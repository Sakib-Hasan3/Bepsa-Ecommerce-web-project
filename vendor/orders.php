<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('vendor');

$vendor_id = $_SESSION['user_id'];

// Fetch orders containing at least one of this vendor's products
$orders = $conn->query(
    "SELECT DISTINCT o.*
     FROM orders o
     JOIN order_items oi ON o.id = oi.order_id
     JOIN products p ON oi.product_id = p.id
     WHERE p.vendor_id = $vendor_id
     ORDER BY o.created_at DESC"
);

include '../includes/header.php';
?>

<h2>Orders for My Products</h2>
<?php if ($orders->num_rows): ?>
    <table>
        <thead>
            <tr>
                <th>#Order ID</th>
                <th>Delivery Address</th>
                <th>Your Items in Order</th>
                <th>Total (৳)</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php while($order = $orders->fetch_assoc()): ?>
            <tr>
                <td><?= $order['id'] ?></td>
                <td>
                    <?= esc($order['address']) ?><br>
                    <?= esc($order['city']) ?>, <?= esc($order['postal_code']) ?><br>
                    <b>Phone:</b> <?= esc($order['phone']) ?>
                </td>
                <td>
                    <ul style="padding-left:16px;margin:0;">
                    <?php
                        $items = $conn->query(
                            "SELECT oi.*, p.name 
                             FROM order_items oi
                             JOIN products p ON oi.product_id = p.id
                             WHERE oi.order_id = {$order['id']} AND p.vendor_id = $vendor_id"
                        );
                        while ($item = $items->fetch_assoc()):
                    ?>
                        <li>
                            <?= esc($item['name']) ?> — <?= (int)$item['quantity'] ?> x ৳<?= number_format($item['price'], 2) ?> = <b>৳<?= number_format($item['quantity'] * $item['price'], 2) ?></b>
                        </li>
                    <?php endwhile; ?>
                    </ul>
                </td>
                <td><b>৳<?= number_format($order['total'], 2) ?></b></td>
                <td><?= esc(ucfirst($order['status'])) ?></td>
                <td><?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>No orders containing your products yet.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
