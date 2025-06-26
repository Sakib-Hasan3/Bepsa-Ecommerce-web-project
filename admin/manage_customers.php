<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('admin');

// Handle delete customer (optional: you may want to use status flag instead of permanent delete)
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Prevent admin from deleting themselves or non-customer users
    $user = $conn->query("SELECT id FROM users WHERE id=$delete_id AND role='customer'")->fetch_assoc();
    if ($user) {
        $conn->query("DELETE FROM users WHERE id=$delete_id AND role='customer'");
        // Optionally: delete related carts, orders, etc.
        $conn->query("DELETE FROM carts WHERE customer_id=$delete_id");
        $conn->query("DELETE FROM orders WHERE customer_id=$delete_id");
    }
    header("Location: manage_customers.php");
    exit;
}

// Fetch all customers
$res = $conn->query("SELECT id, name, email, created_at FROM users WHERE role='customer' ORDER BY created_at DESC");

include '../includes/header.php';
?>

<h2>Manage Customers</h2>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>Date Joined</th>
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
                <td>
                    <a href="manage_customers.php?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this customer?');" style="color:#c00;">Delete</a>
                </td>
            </tr>
        <?php endwhile; else: ?>
            <tr><td colspan="5" style="text-align:center;">No customers found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
