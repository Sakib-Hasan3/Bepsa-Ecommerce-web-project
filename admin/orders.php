<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('admin');

$orders = $conn->query(
    "SELECT o.*, u.name AS customer_name, u.email AS customer_email
     FROM orders o
     JOIN users u ON o.customer_id = u.id
     ORDER BY o.created_at DESC"
);

include '../includes/header.php';
?>

<h2>All Orders</h2>
<?php if ($orders->num_rows): ?>
    <table>
        <thead>
            <tr>
                <th>#Order ID</th>
                <th>Customer</th>
                <th>Address</th>
                <th>Order Items</th>
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
                    <?= esc($order['customer_name']) ?><br>
                    <span style="font-size:0.95em;color:#555;"><?= esc($order['customer_email']) ?></span>
                </td>
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
                             WHERE oi.order_id = {$order['id']}"
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
    <p>No orders found.</p>
<?php endif; ?>

<?php include '../includes/footer.php'; ?>
