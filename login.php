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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- CSRF Validation ---
    if (!isset($_POST['csrf_token']) || !verify_csrf($_POST['csrf_token'])) {
        die('Security error (invalid CSRF token).');
    }

    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';

    if (!$email || !$pass) {
        $error = "Please fill in both fields.";
    } else {
        // Use prepared statements for security
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            if (password_verify($pass, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                // Regenerate CSRF token for new session (recommended)
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                if ($user['role'] === 'admin') header('Location: admin/dashboard.php');
                elseif ($user['role'] === 'vendor') header('Location: vendor/dashboard.php');
                else header('Location: customer/dashboard.php');
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<h2>Login</h2>
<?php if ($error): ?>
    <p class="error"><?= $error ?></p>
<?php endif; ?>
<form method="post" style="max-width:350px;">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <label>Email:
        <input type="email" name="email" required autofocus>
    </label>
    <label>Password:
        <input type="password" name="password" required>
    </label>
    <button type="submit">Login</button>
    <p style="margin-top:12px;">
        New to BEPSA? <a href="register.php">Register here</a>
    </p>
</form>

<?php include 'includes/footer.php'; ?>
