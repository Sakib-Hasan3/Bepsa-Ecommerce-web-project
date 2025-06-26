<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('vendor');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Validation ---
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error (invalid CSRF token).');
    }

    $name = esc(trim($_POST['name'] ?? ''));
    $description = esc(trim($_POST['description'] ?? ''));
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $vendor_id = $_SESSION['user_id'];
    $image = null;

    // Validate fields
    if (!$name || !$description || $price <= 0 || $quantity < 0) {
        $error = "Please fill in all fields with valid values.";
    } else {
        // Prevent duplicate product name for same vendor
        $check = $conn->prepare("SELECT id FROM products WHERE vendor_id = ? AND name = ?");
        $check->bind_param('is', $vendor_id, $name);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "You already have a product with this name.";
        }
        $check->close();
    }

    // Handle image upload
    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] !== 4) {
        if ($_FILES['image']['error'] !== 0) {
            $error = "Image upload error: {$_FILES['image']['error']}";
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = "Only JPG, PNG, GIF, or WEBP files are allowed.";
            } else {
                $image = uniqid('prod_', true) . '.' . $ext;
                $upload_path = '../assets/uploads/' . $image;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $error = "Failed to upload image. Check folder permissions.";
                }
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("INSERT INTO products (vendor_id, name, description, price, quantity, image, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param('issdis', $vendor_id, $name, $description, $price, $quantity, $image);
        if ($stmt->execute()) {
            $success = "Product added successfully!";
        } else {
            $error = "Database error. Please try again.";
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<h2>Add New Product</h2>
<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php elseif ($success): ?>
    <p style="color:green;"><?= $success ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" style="max-width:420px;">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <label>Product Name:
        <input type="text" name="name" required maxlength="100">
    </label>
    <label>Description:
        <textarea name="description" rows="3" required maxlength="500"></textarea>
    </label>
    <label>Price (৳):
        <input type="number" name="price" min="1" step="0.01" required>
    </label>
    <label>Quantity:
        <input type="number" name="quantity" min="0" step="1" required>
    </label>
    <label>Product Image:
        <input type="file" name="image" accept="image/*" required>
    </label>
    <button type="submit" style="width:100%;">Add Product</button>
</form>

<?php include '../includes/footer.php'; ?>
