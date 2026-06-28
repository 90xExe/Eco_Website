<?php
require_once __DIR__ . "/includes/functions.php";
require_login();

$user = current_user();
$userOrders = array_reverse(user_orders($user["email"]));
$userDeposits = array_reverse(user_deposits($user["email"]));

$page_title = "Orders - 90N.GameShop";
$body_class = "public-page";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/nav.php";
?>
<main class="page">
    <?php show_flash(); ?>
    <section class="panel">
        <div class="section-title"><div><p>TOPUP</p><h2>My Orders</h2></div><a class="btn ghost" href="index.php#free-fire">Buy Topup</a></div>
        <div class="list">
            <?php if (!$userOrders): ?><p>No topup orders yet.</p><?php endif; ?>
            <?php foreach ($userOrders as $order): ?>
                <article class="list-item">
                    <div><b><?= e($order["product"] ?? "-") ?></b><span><?= e($order["package"] ?? "-") ?> | UID: <?= e($order["player_uid"] ?? "-") ?></span></div>
                    <div><strong><?= money($order["price"] ?? 0) ?></strong><em class="<?= status_class($order["status"] ?? "pending") ?>"><?= e($order["status"] ?? "pending") ?></em></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="section-title"><div><p>WALLET</p><h2>Add Money Requests</h2></div></div>
        <div class="list">
            <?php if (!$userDeposits): ?><p>No add-money requests yet.</p><?php endif; ?>
            <?php foreach ($userDeposits as $deposit): ?>
                <article class="list-item">
                    <div><b><?= e($deposit["id"] ?? "-") ?></b><span>TrxID: <?= e($deposit["trxid"] ?? "-") ?></span></div>
                    <div><strong><?= money($deposit["amount"] ?? 0) ?></strong><em class="<?= status_class($deposit["status"] ?? "pending") ?>"><?= e($deposit["status"] ?? "pending") ?></em></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
