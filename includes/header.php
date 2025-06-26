<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BEPSA - Buy Easy &amp; Pay Safe Anywhere</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/bepsa-ecommerce/assets/css/style.css">
    <style>
        body { background: #e3f2fd; }
        header {
            background: linear-gradient(90deg, #1565c0 80%, #2196f3 100%);
            color: #fff;
            box-shadow: 0 2px 10px #1976d277;
            padding: 0;
        }
        .site-title {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin: 0;
            padding: 0.6em 0 0.15em 0.7em;
            display: flex;
            align-items: center;
        }
        .site-title span {
            font-size: 1.05rem;
            font-weight: 400;
            margin-left: 12px;
            color: #bbdefb;
        }
        nav {
            background: #1976d2;
            display: flex;
            flex-wrap: wrap;
            gap: 0.3em;
            align-items: center;
            justify-content: flex-end;
            padding: 0.5em 1em;
        }
        nav a {
            color: #fff;
            background: #2196f3;
            padding: 0.5em 1.15em;
            margin-left: 0.4em;
            border-radius: 24px;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 6px #1565c018;
        }
        nav a:hover, nav a.active {
            background: #fff;
            color: #1565c0;
            box-shadow: 0 3px 8px #2196f344;
        }
        @media (max-width:700px) {
            .site-title { font-size: 1.3rem; padding-left: 0.4em; }
            nav { flex-direction: column; align-items: flex-start; padding: 0.8em 0.5em;}
            nav a { margin-bottom: 0.3em; }
        }
        main { max-width: 1000px; margin: 32px auto; background: #fff; padding: 2em 1.2em; border-radius: 15px; box-shadow: 0 3px 18px #1976d213;}
    </style>
</head>
<body>
<header>
    <div class="site-title">
        BEPSA
        <span>Buy Easy &amp; Pay Safe Anywhere</span>
    </div>
    
    <nav>
        <a href="/bepsa-ecommerce/index.php"<?= basename($_SERVER['PHP_SELF'])=='index.php'?' class="active"':''; ?>>Home</a>
        <?php if (isset($_SESSION['role'])): ?>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <a href="/bepsa-ecommerce/admin/dashboard.php">Admin Dashboard</a>
                <a href="/bepsa-ecommerce/admin/manage_vendors.php">Vendors</a>
                <a href="/bepsa-ecommerce/admin/manage_customers.php">Customers</a>
                <a href="/bepsa-ecommerce/admin/orders.php">Orders</a>
            <?php elseif ($_SESSION['role'] == 'vendor'): ?>
                <a href="/bepsa-ecommerce/vendor/dashboard.php">Vendor Dashboard</a>
                <a href="/bepsa-ecommerce/vendor/add_product.php">Add Product</a>
                <a href="/bepsa-ecommerce/vendor/orders.php">My Orders</a>
            <?php else: ?>
                <a href="/bepsa-ecommerce/customer/dashboard.php">Shop</a>
                <a href="/bepsa-ecommerce/customer/cart.php">My Cart</a>
                <a href="/bepsa-ecommerce/customer/orders.php">Orders</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['role'])): ?>
    <div class="logout-left">
        <a href="/bepsa-ecommerce/logout.php">Logout</a>
    </div>
<?php endif; ?>
        <?php else: ?>
            <a href="/bepsa-ecommerce/login.php"<?= basename($_SERVER['PHP_SELF'])=='login.php'?' class="active"':''; ?>>Login</a>
            <a href="/bepsa-ecommerce/register.php"<?= basename($_SERVER['PHP_SELF'])=='register.php'?' class="active"':''; ?>>Register</a>
        <?php endif; ?>
    </nav>
</header>
<main>
