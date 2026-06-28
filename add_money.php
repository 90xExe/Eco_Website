<?php
require_once __DIR__ . "/includes/functions.php";
require_login();

$user = current_user();
$settings = site_settings();
$amount = (float)($_GET["amount"] ?? $_POST["amount"] ?? 0);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $trxid = clean($_POST["trxid"] ?? "");
    $amount = (float)($_POST["amount"] ?? 0);

    if ($amount <= 0 || $trxid === "") {
        flash("error", "Amount and transaction ID are required.");
        redirect("profile.php");
    }

    $deposits = deposits();
    $deposits[] = [
        "id" => make_id("DEP"),
        "user_email" => $user["email"],
        "user_name" => $user["name"],
        "amount" => $amount,
        "method" => "bKash Send Money",
        "merchant_number" => $settings["bkash_number"] ?? "",
        "trxid" => $trxid,
        "status" => "pending",
        "created_at" => date("d M Y, h:i A")
    ];
    save_deposits($deposits);

    flash("success", "Add money request submitted. Admin approval required.");
    redirect("orders.php");
}

if ($amount <= 0) {
    flash("error", "Please enter amount first.");
    redirect("profile.php");
}

$page_title = "Add Money - 90N.GameShop";
$body_class = "public-page";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/nav.php";
?>
<main class="page auth-page">
    <section class="auth-card wide">
        <h1>Add Money</h1>
        <div class="pay-box">
            <p>Send Money Number</p>
            <h2><?= e($settings["bkash_number"] ?? "") ?></h2>
            <p>Amount: <b><?= money($amount) ?></b></p>
        </div>
        <form method="POST">
            <input type="hidden" name="amount" value="<?= e($amount) ?>">
            <label>bKash Transaction ID</label>
            <input type="text" name="trxid" required>
            <button class="btn" type="submit">Submit Request</button>
        </form>
    </section>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
