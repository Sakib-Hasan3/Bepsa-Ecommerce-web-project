<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('admin');

// Quick stats
$num_vendors = $conn->query("SELECT COUNT(*) FROM users WHERE role='vendor'")->fetch_row()[0];
$num_customers = $conn->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetch_row()[0];
$num_products = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$num_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];

include '../includes/header.php';
?>

<h2>Admin Dashboard</h2>
<div style="display:flex; flex-wrap:wrap; gap:30px; margin-bottom:2.5em; justify-content:center;">
    <div style="background:#1976d2; color:#fff; padding:2em 2em 1.3em 2em; border-radius:15px; min-width:180px; box-shadow:0 2px 18px #1565c033;">
        <div style="font-size:2.2em;font-weight:700;"><?= $num_vendors ?></div>
        <div style="font-size:1.1em;">Vendors</div>
    </div>
    <div style="background:#1565c0; color:#fff; padding:2em 2em 1.3em 2em; border-radius:15px; min-width:180px; box-shadow:0 2px 18px #1565c033;">
        <div style="font-size:2.2em;font-weight:700;"><?= $num_customers ?></div>
        <div style="font-size:1.1em;">Customers</div>
    </div>
    <div style="background:#2196f3; color:#fff; padding:2em 2em 1.3em 2em; border-radius:15px; min-width:180px; box-shadow:0 2px 18px #1565c033;">
        <div style="font-size:2.2em;font-weight:700;"><?= $num_products ?></div>
        <div style="font-size:1.1em;">Products</div>
    </div>
    <div style="background:#0d47a1; color:#fff; padding:2em 2em 1.3em 2em; border-radius:15px; min-width:180px; box-shadow:0 2px 18px #1565c033;">
        <div style="font-size:2.2em;font-weight:700;"><?= $num_orders ?></div>
        <div style="font-size:1.1em;">Orders</div>
    </div>
</div>

<div style="display:flex; flex-wrap:wrap; gap:20px; justify-content:center;">
    <a href="manage_vendors.php" style="background:#1976d2; color:#fff; padding:1em 2em; border-radius:10px; text-decoration:none; font-size:1.08em; font-weight:500;">Manage Vendors</a>
    <a href="manage_customers.php" style="background:#1565c0; color:#fff; padding:1em 2em; border-radius:10px; text-decoration:none; font-size:1.08em; font-weight:500;">Manage Customers</a>
    <a href="orders.php" style="background:#2196f3; color:#fff; padding:1em 2em; border-radius:10px; text-decoration:none; font-size:1.08em; font-weight:500;">View Orders</a>
    <!-- Optionally add a products page for admin -->
</div>

<?php include '../includes/footer.php'; ?>
