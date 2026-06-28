<?php
require_once __DIR__ . "/includes/functions.php";
require_login();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    [$index, $user] = find_user($_SESSION["user_email"]);
    $users = users();
    if ($index !== null && isset($users[$index])) {
        $users[$index]["name"] = clean($_POST["name"] ?? ($user["name"] ?? ""));
        $users[$index]["phone"] = clean_phone($_POST["phone"] ?? "");
        $users[$index]["address"] = clean($_POST["address"] ?? "");
        $users[$index]["game_uid"] = clean($_POST["game_uid"] ?? "");
        save_users($users);
    }
    flash("success", "Profile updated.");
    redirect("profile.php");
}

$user = current_user();
$page_title = "Profile - 90N.GameShop";
$body_class = "public-page";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/nav.php";
?>
<main class="page">
    <?php show_flash(); ?>
    <section class="grid two">
        <aside class="panel">
            <div class="avatar"><?= strtoupper(substr($user["name"] ?? "U", 0, 1)) ?></div>
            <h2><?= e($user["name"] ?? "User") ?></h2>
            <p><?= e($user["email"] ?? "") ?></p>
            <h3>Wallet: <?= money($user["balance"] ?? 0) ?></h3>
            <form action="add_money.php" method="GET" class="compact-form">
                <input type="number" name="amount" min="1" step="1" placeholder="Amount" required>
                <button class="btn" type="submit">Add Money</button>
            </form>
            <div class="quick-links">
                <a class="btn ghost" href="index.php#free-fire">Buy Topup</a>
                <a class="btn ghost" href="orders.php">Orders</a>
                <a class="btn danger" href="logout.php">Logout</a>
            </div>
        </aside>
        <section class="panel">
            <div class="section-title"><div><p>PROFILE</p><h2>Personal Details</h2></div></div>
            <form method="POST" class="form-grid">
                <label>Name</label>
                <input type="text" name="name" value="<?= e($user["name"] ?? "") ?>" required>
                <label>Phone</label>
                <input type="text" name="phone" value="<?= e($user["phone"] ?? "") ?>">
                <label>Address</label>
                <input type="text" name="address" value="<?= e($user["address"] ?? "") ?>">
                <label>Free Fire UID</label>
                <input type="text" name="game_uid" value="<?= e($user["game_uid"] ?? "") ?>">
                <button class="btn" type="submit">Save Profile</button>
            </form>
        </section>
    </section>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
