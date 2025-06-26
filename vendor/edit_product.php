<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('vendor');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid product.');
}
$vendor_id = $_SESSION['user_id'];
$product_id = intval($_GET['id']);

$error = '';
$success = '';

// Fetch current product
$stmt = $conn->prepare("SELECT * FROM products WHERE id=? AND vendor_id=?");
$stmt->bind_param('ii', $product_id, $vendor_id);
$stmt->execute();
$result = $stmt->get_result();
if (!$product = $result->fetch_assoc()) {
    die('Product not found or unauthorized.');
}
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Validation ---
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error (invalid CSRF token).');
    }

    $name = esc(trim($_POST['name'] ?? ''));
    $description = esc(trim($_POST['description'] ?? ''));
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $image = $product['image']; // default: keep old image

    // Validate fields
    if (!$name || !$description || $price <= 0 || $quantity < 0) {
        $error = "Please fill in all fields with valid values.";
    } else {
        // Prevent duplicate product name for same vendor (except current product)
        $check = $conn->prepare("SELECT id FROM products WHERE vendor_id = ? AND name = ? AND id != ?");
        $check->bind_param('isi', $vendor_id, $name, $product_id);
        $check->execute();
        $check->store_result();
        if ($check->num_rows > 0) {
            $error = "You already have a product with this name.";
        }
        $check->close();
    }

    // Handle image upload (optional)
    if (!$error && isset($_FILES['image']) && $_FILES['image']['error'] !== 4) {
        if ($_FILES['image']['error'] !== 0) {
            $error = "Image upload error: {$_FILES['image']['error']}";
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($ext, $allowed)) {
                $error = "Only JPG, PNG, GIF, or WEBP files are allowed.";
            } else {
                // Remove old image if exists
                if ($image && file_exists("../assets/uploads/$image")) {
                    @unlink("../assets/uploads/$image");
                }
                $image = uniqid('prod_', true) . '.' . $ext;
                $upload_path = '../assets/uploads/' . $image;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $error = "Failed to upload image. Check folder permissions.";
                }
            }
        }
    }

    if (!$error) {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, quantity=?, image=? WHERE id=? AND vendor_id=?");
        $stmt->bind_param('ssdisii', $name, $description, $price, $quantity, $image, $product_id, $vendor_id);
        if ($stmt->execute()) {
            $success = "Product updated successfully!";
            // Fetch updated data for form re-display
            $product['name'] = $name;
            $product['description'] = $description;
            $product['price'] = $price;
            $product['quantity'] = $quantity;
            $product['image'] = $image;
        } else {
            $error = "Database error. Please try again.";
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<h2>Edit Product</h2>
<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php elseif ($success): ?>
    <p style="color:green;"><?= $success ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" style="max-width:420px;">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <label>Product Name:
        <input type="text" name="name" required maxlength="100" value="<?= esc($product['name']) ?>">
    </label>
    <label>Description:
        <textarea name="description" rows="3" required maxlength="500"><?= esc($product['description']) ?></textarea>
    </label>
    <label>Price (৳):
        <input type="number" name="price" min="1" step="0.01" required value="<?= esc($product['price']) ?>">
    </label>
    <label>Quantity:
        <input type="number" name="quantity" min="0" step="1" required value="<?= esc($product['quantity']) ?>">
    </label>
    <label>Product Image:
        <input type="file" name="image" accept="image/*">
        <?php if ($product['image']): ?>
            <br>
            <img src="../assets/uploads/<?= esc($product['image']) ?>" alt="Current Image" style="width:70px; margin-top:8px; border-radius:8px;">
        <?php endif; ?>
        <span style="font-size:12px;color:#1976d2;">Leave blank to keep current image.</span>
    </label>
    <button type="submit" style="width:100%;">Update Product</button>
</form>

<?php include '../includes/footer.php'; ?>
