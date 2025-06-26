<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('vendor');

// Fetch vendor info
$vendor_id = $_SESSION['user_id'];

// Fetch vendor products
$products = $conn->query("SELECT * FROM products WHERE vendor_id = $vendor_id ORDER BY created_at DESC");

// Fetch orders for this vendor's products
$orders = $conn->query("
    SELECT o.id AS order_id, o.created_at, o.status, u.name AS customer_name, 
           oi.product_id, oi.quantity, oi.price, p.name AS product_name
    FROM orders o
    JOIN order_items oi ON oi.order_id = o.id
    JOIN products p ON p.id = oi.product_id
    JOIN users u ON o.customer_id = u.id
    WHERE p.vendor_id = $vendor_id
    ORDER BY o.created_at DESC
    LIMIT 10
");

include '../includes/header.php';
?>

<h2>Vendor Dashboard</h2>

<section>
    <a href="add_product.php" class="button">+ Add New Product</a>
</section>

<section>
    <h3>Your Products</h3>
    <?php if ($products->num_rows): ?>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price (৳)</th>
                <th>Quantity</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($p = $products->fetch_assoc()): ?>
            <tr>
                <td>
                  <?php if ($p['image']): ?>
                    <img src="../assets/uploads/<?= esc($p['image']) ?>" style="width:60px; height:50px; object-fit:cover;">
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </td>
                <td><?= esc($p['name']) ?></td>
                <td><?= esc($p['description']) ?></td>
                <td><?= number_format($p['price'], 2) ?></td>
                <td><?= (int)$p['quantity'] ?></td>
                <td>
                    <a href="edit_product.php?id=<?= $p['id'] ?>">Edit</a> |
                    <a href="delete_product.php?id=<?= $p['id'] ?>" class="delete">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No products added yet.</p>
    <?php endif; ?>
</section>

<section>
    <h3>Recent Orders for Your Products</h3>
    <?php if ($orders->num_rows): ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Customer</th>
                <th>Product</th>
                <th>Qty</th>
                <th>Unit Price (৳)</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
        <?php while($o = $orders->fetch_assoc()): ?>
            <tr>
                <td><?= date('Y-m-d', strtotime($o['created_at'])) ?></td>
                <td><?= esc($o['customer_name']) ?></td>
                <td><?= esc($o['product_name']) ?></td>
                <td><?= (int)$o['quantity'] ?></td>
                <td><?= number_format($o['price'],2) ?></td>
                <td><?= esc(ucfirst($o['status'])) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>No recent orders found for your products.</p>
    <?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>
