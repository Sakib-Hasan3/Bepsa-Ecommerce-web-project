<?php
require_once 'config.php';
include 'includes/header.php';
?>

<h1>Welcome to BEPSA</h1>
<p style="margin-bottom:2em;">
    Discover the best products, buy with confidence, or sell your own items as a trusted vendor!
</p>

<section class="products">
    <?php
    $res = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
    if ($res->num_rows > 0):
        while($row = $res->fetch_assoc()):
    ?>
    <div class="product">
        <?php if ($row['image']): ?>
            <img src="assets/uploads/<?= esc($row['image']) ?>" alt="<?= esc($row['name']) ?>">
        <?php else: ?>
            <img src="assets/uploads/default.png" alt="No image">
        <?php endif; ?>
        <h3><?= esc($row['name']) ?></h3>
        <p><?= esc(strlen($row['description']) > 80 ? substr($row['description'],0,77).'...' : $row['description']) ?></p>
        <p class="price">Price: $<?= number_format($row['price'],2) ?></p>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
            <form action="customer/cart.php" method="post" style="margin:0;">
                <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                <input type="hidden" name="add_to_cart" value="1">
                <button type="submit" style="width:100%;">Add to Cart</button>
            </form>
        <?php endif; ?>
    </div>
    <?php endwhile; ?>
    <?php else: ?>
        <p style="text-align:center;width:100%;">No products available yet.</p>
    <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
