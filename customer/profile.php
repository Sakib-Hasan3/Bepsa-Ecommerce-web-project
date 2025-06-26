<?php
require_once '../config.php';
include '../includes/auth.php';
require_role('customer');

$customer_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch profile info
$stmt = $conn->prepare("SELECT name, email, address, city, postal_code, phone FROM users WHERE id=?");
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$stmt->bind_result($name, $email, $address, $city, $postal_code, $phone);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error (invalid CSRF token).');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!$name || !$email) {
        $error = "Name and email are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($address) > 255 || strlen($city) > 100 || strlen($postal_code) > 20 || strlen($phone) > 30) {
        $error = "One or more address fields are too long.";
    } else {
        // Check for duplicate email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND id<>?");
        $stmt->bind_param('si', $email, $customer_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "This email is already in use.";
        } else {
            $stmt2 = $conn->prepare("UPDATE users SET name=?, email=?, address=?, city=?, postal_code=?, phone=? WHERE id=?");
            $stmt2->bind_param('ssssssi', $name, $email, $address, $city, $postal_code, $phone, $customer_id);
            if ($stmt2->execute()) {
                $success = "Profile updated successfully!";
            } else {
                $error = "Failed to update profile. Try again.";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

include '../includes/header.php';
?>

<h2>My Profile</h2>
<?php if ($success): ?>
    <p style="color:green;"><?= $success ?></p>
<?php elseif ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>

<form method="post" style="max-width:420px;">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <label>Name:
        <input type="text" name="name" value="<?= esc($name) ?>" required maxlength="100">
    </label>
    <label>Email:
        <input type="email" name="email" value="<?= esc($email) ?>" required maxlength="100">
    </label>
    <fieldset style="margin-bottom:12px;padding:8px 10px 8px 10px;border-radius:7px;">
        <legend style="font-size:1.05em;">Default Delivery Address</legend>
        <label>Address:
            <input type="text" name="address" value="<?= esc($address) ?>" maxlength="255">
        </label>
        <label>City:
            <input type="text" name="city" value="<?= esc($city) ?>" maxlength="100">
        </label>
        <label>Postal Code:
            <input type="text" name="postal_code" value="<?= esc($postal_code) ?>" maxlength="20">
        </label>
        <label>Phone:
            <input type="text" name="phone" value="<?= esc($phone) ?>" maxlength="30">
        </label>
    </fieldset>
    <button type="submit" style="width:100%;">Update Profile</button>
</form>

<?php include '../includes/footer.php'; ?>
