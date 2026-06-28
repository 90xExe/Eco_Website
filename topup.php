<?php
require_once __DIR__ . "/includes/functions.php";

$slug = clean($_GET["item"] ?? "");
[, $product] = find_product($slug);
if (!$product || empty($product["active"])) {
    flash("error", "Product not found.");
    redirect("index.php");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = current_user();
    if (!$user) {
        flash("error", "Please login to purchase.");
        redirect("login.php");
    }

    if (($user["status"] ?? "active") !== "active") {
        flash("error", "Your account is not active.");
        redirect("profile.php");
    }

    $packageIndex = (int)($_POST["package"] ?? -1);
    $uid = clean($_POST["player_uid"] ?? "");
    $packages = $product["packages"] ?? [];

    if (!isset($packages[$packageIndex]) || $uid === "") {
        flash("error", "Package and UID are required.");
        redirect("topup.php?item=" . urlencode($slug));
    }

    $pkg = $packages[$packageIndex];
    $price = (float)($pkg["price"] ?? 0);
    $couponCode = strtoupper(clean($_POST["coupon_code"] ?? ""));
    $discount = 0;
    $coupon = null;
    if ($couponCode !== "") {
        [$discount, $coupon] = coupon_discount($price, $couponCode);
        if (!$coupon) {
            flash("error", "Invalid coupon code.");
            redirect("topup.php?item=" . urlencode($slug));
        }
    }
    $finalPrice = max(0, $price - $discount);

    [$userIndex, $freshUser] = find_user($user["email"]);
    $users = users();
    $balance = (float)($freshUser["balance"] ?? 0);
    if ($balance < $finalPrice) {
        flash("error", "Not enough wallet balance.");
        redirect("profile.php");
    }

    $users[$userIndex]["balance"] = $balance - $finalPrice;
    save_users($users);

    $orders = orders();
    $orders[] = [
        "id" => make_id("ORD"),
        "user_email" => $user["email"],
        "user_name" => $user["name"],
        "product" => $product["name"],
        "product_slug" => $product["slug"],
        "package" => $pkg["name"],
        "price" => $finalPrice,
        "original_price" => $price,
        "discount" => $discount,
        "coupon_code" => $coupon["code"] ?? "",
        "player_uid" => $uid,
        "payment" => "Wallet Balance",
        "status" => "pending",
        "created_at" => date("d M Y, h:i A")
    ];
    save_orders($orders);

    flash("success", "Order placed successfully.");
    redirect("orders.php");
}

$page_title = $product["name"] . " - 90N.GameShop";
$body_class = "public-page";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/nav.php";
$user = current_user();
?>
<main class="page">
    <?php show_flash(); ?>
    <section class="hero compact">
        <div class="hero-product">
            <img src="<?= e($product["image"]) ?>" alt="<?= e($product["name"]) ?>">
            <div><p>TOPUP SERVICE</p><h1><?= e($product["name"]) ?></h1><span><?= e($product["type"] ?? "") ?></span></div>
        </div>
    </section>

    <form method="POST" class="grid two">
        <section class="panel">
            <div class="section-title"><div><p>STEP 01</p><h2>Select Package</h2></div></div>
            <div class="package-grid">
                <?php foreach (($product["packages"] ?? []) as $i => $pkg): ?>
                    <label class="package">
                        <input type="radio" name="package" value="<?= e($i) ?>" required>
                        <b><?= e($pkg["name"] ?? "") ?></b>
                        <strong><?= money($pkg["price"] ?? 0) ?></strong>
                    </label>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="panel">
            <div class="section-title"><div><p>STEP 02</p><h2>Account Info</h2></div></div>
            <label>Free Fire UID</label>
            <input type="text" name="player_uid" value="<?= e($user["game_uid"] ?? "") ?>" required>
            <label>Coupon Code</label>
            <input type="text" name="coupon_code" placeholder="Optional">
            <?php if ($user): ?>
                <p>Wallet Balance: <b><?= money($user["balance"] ?? 0) ?></b></p>
                <button class="btn" type="submit">Buy With Wallet</button>
            <?php else: ?>
                <a class="btn" href="login.php">Login to Buy</a>
            <?php endif; ?>
        </aside>
    </form>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
