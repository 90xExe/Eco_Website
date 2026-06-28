<?php
require_once __DIR__ . "/includes/functions.php";
$page_title = "90N.GameShop";
$body_class = "public-page";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/nav.php";

$allProducts = array_values(array_filter(products(), fn($p) => !empty($p["active"])));
$allCategories = array_values(array_filter(categories(), fn($c) => !array_key_exists("active", $c) || !empty($c["active"])));
?>
<main class="page">
    <?php show_flash(); ?>

    <section class="hero">
        <div>
            <p>GAME TOPUP STORE</p>
            <h1>90N.GameShop</h1>
            <span>Fast wallet based game top-up system.</span>
        </div>
        <a class="btn" href="<?= current_user() ? 'profile.php' : 'login.php' ?>"><?= current_user() ? 'Open Profile' : 'Login Now' ?></a>
    </section>

    <?php foreach ($allCategories as $cat): ?>
        <?php
            $items = array_values(array_filter($allProducts, fn($p) => ($p["category"] ?? "free-fire") === ($cat["slug"] ?? "")));
            if (!$items) continue;
        ?>
        <section class="panel" id="<?= e($cat["slug"]) ?>">
            <div class="section-title">
                <div><p>TOPUP CATEGORY</p><h2><?= e($cat["name"]) ?></h2></div>
                <span><?= count($items) ?> services</span>
            </div>
            <div class="product-grid">
                <?php foreach ($items as $product): ?>
                    <a class="product-card" href="topup.php?item=<?= e($product["slug"]) ?>">
                        <img src="<?= e($product["image"]) ?>" alt="<?= e($product["name"]) ?>">
                        <h3><?= e($product["name"]) ?></h3>
                        <p><?= e($product["type"] ?? "Game / Top up") ?></p>
                        <strong>Order Now</strong>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
