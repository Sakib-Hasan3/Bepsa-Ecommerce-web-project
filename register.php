<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    // Redirect based on role if already logged in
    if ($_SESSION['role'] === 'admin') header('Location: admin/dashboard.php');
    elseif ($_SESSION['role'] === 'vendor') header('Location: vendor/dashboard.php');
    else header('Location: customer/dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Validation ---
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error (invalid CSRF token).');
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $role = ($_POST['role'] ?? '') === 'vendor' ? 'vendor' : 'customer';

    if (!$name || !$email || !$pass) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (strlen($pass) < 5) {
        $error = "Password must be at least 5 characters.";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "An account with this email already exists.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt2->bind_param('ssss', $name, $email, $hash, $role);
            if ($stmt2->execute()) {
                $success = "Registration successful! You may now <a href='login.php'>log in</a>.";
            } else {
                $error = "Registration failed, try again.";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<h2>Register</h2>
<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php elseif ($success): ?>
    <p style="color:green;"><?= $success ?></p>
<?php endif; ?>

<form method="post" style="max-width:400px;">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <label>Name:
        <input type="text" name="name" required>
    </label>
    <label>Email:
        <input type="email" name="email" required>
    </label>
    <label>Password:
        <input type="password" name="password" required>
    </label>
    <label>Account Type:
        <select name="role">
            <option value="customer" selected>Customer</option>
            <option value="vendor">Vendor</option>
        </select>
    </label>
    <button type="submit">Register</button>
    <p style="margin-top:12px;">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</form>

<?php include 'includes/footer.php'; ?>
