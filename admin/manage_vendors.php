<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('admin');

// Handle delete vendor (cascades to products via DB foreign keys)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Make sure it's a vendor
    $user = $conn->query("SELECT id FROM users WHERE id=$delete_id AND role='vendor'")->fetch_assoc();
    if ($user) {
        // Delete vendor (products will cascade delete if FK set)
        $conn->query("DELETE FROM users WHERE id=$delete_id AND role='vendor'");
        // Optionally: remove all vendor's products' carts/order_items as well (done by DB FKs if set)
    }
    header("Location: manage_vendors.php");
    exit;
}

// Fetch all vendors and count their products
$res = $conn->query("
    SELECT u.id, u.name, u.email, u.created_at, COUNT(p.id) as product_count
    FROM users u
    LEFT JOIN products p ON u.id = p.vendor_id
    WHERE u.role='vendor'
    GROUP BY u.id
    ORDER BY u.created_at DESC
");

include '../includes/header.php';
?>

<h2>Manage Vendors</h2>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Date Joined</th>
            <th>Products</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($res->num_rows): $i=1; while($row = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $i++ ?></td>
                <td><?= esc($row['name']) ?></td>
                <td><?= esc($row['email']) ?></td>
                <td><?= date('Y-m-d', strtotime($row['created_at'])) ?></td>
                <td><?= (int)$row['product_count'] ?></td>
                <td>
                    <a href="manage_vendors.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this vendor and all their products?');" style="color:#c00;">Delete</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="6" style="text-align:center;">No vendors found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
