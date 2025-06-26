<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('vendor');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}
$product_id = intval($_GET['id']);
$vendor_id = $_SESSION['user_id'];

// Confirm the product belongs to the vendor
$stmt = $conn->prepare("SELECT image FROM products WHERE id=? AND vendor_id=? LIMIT 1");
$stmt->bind_param('ii', $product_id, $vendor_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$result->num_rows) {
    $stmt->close();
    header('Location: dashboard.php');
    exit;
}
$product = $result->fetch_assoc();
$stmt->close();

// Delete image file if it exists
if ($product['image']) {
    $img_path = '../assets/uploads/' . $product['image'];
    if (file_exists($img_path)) {
        unlink($img_path);
    }
}

// Delete the product
$stmt = $conn->prepare("DELETE FROM products WHERE id=? AND vendor_id=?");
$stmt->bind_param('ii', $product_id, $vendor_id);
$stmt->execute();
$stmt->close();

header('Location: dashboard.php');
exit;
