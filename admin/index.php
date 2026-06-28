<?php
require_once __DIR__ . "/../includes/functions.php";
require_admin();

$section = clean($_GET["section"] ?? "dashboard");
function admin_url(string $section): string { return "index.php?section=" . urlencode($section); }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = clean($_POST["action"] ?? "");

    if ($action === "save_product") {
        $name = clean($_POST["name"] ?? "");
        $slug = slugify(clean($_POST["slug"] ?? $name));
        $packages = parse_packages($_POST["packages"] ?? "");
        if ($name === "" || !$packages) {
            flash("error", "Product name and package required.");
            redirect(admin_url("products"));
        }
        $products = products();
        $edit = clean($_POST["edit_slug"] ?? "");
        $item = [
            "name" => $name,
            "slug" => $slug,
            "category" => clean($_POST["category"] ?? "free-fire"),
            "type" => clean($_POST["type"] ?? "Game / Top up"),
            "image" => clean($_POST["image"] ?? "assets/images/product-1.png"),
            "active" => isset($_POST["active"]),
            "rules" => clean($_POST["rules"] ?? "90N.GameShop"),
            "packages" => $packages
        ];
        if ($edit !== "") {
            [$idx] = find_product($edit);
            if ($idx !== null) $products[$idx] = $item;
        } else {
            $products[] = $item;
        }
        save_products($products);
        flash("success", "Product saved.");
        redirect(admin_url("products"));
    }

    if ($action === "delete_product") {
        $slug = clean($_POST["slug"] ?? "");
        save_products(array_values(array_filter(products(), fn($p) => ($p["slug"] ?? "") !== $slug)));
        flash("success", "Product deleted.");
        redirect(admin_url("products"));
    }

    if ($action === "update_order") {
        $id = clean($_POST["id"] ?? "");
        $status = clean($_POST["status"] ?? "pending");
        $orders = orders();
        foreach ($orders as &$order) {
            if (($order["id"] ?? "") === $id) {
                $oldStatus = $order["status"] ?? "pending";
                $order["status"] = $status;
                $order["updated_at"] = date("d M Y, h:i A");
                if ($status === "cancelled" && $oldStatus !== "cancelled" && empty($order["refunded_at"])) {
                    [$uIndex] = find_user($order["user_email"] ?? "");
                    if ($uIndex !== null) {
                        $users = users();
                        $users[$uIndex]["balance"] = (float)($users[$uIndex]["balance"] ?? 0) + (float)($order["price"] ?? 0);
                        save_users($users);
                        $order["refunded_at"] = date("d M Y, h:i A");
                    }
                }
                break;
            }
        }
        unset($order);
        save_orders($orders);
        flash("success", "Order updated.");
        redirect(admin_url("orders"));
    }

    if ($action === "update_deposit") {
        $id = clean($_POST["id"] ?? "");
        $status = clean($_POST["status"] ?? "pending");
        $deposits = deposits();
        foreach ($deposits as &$deposit) {
            if (($deposit["id"] ?? "") === $id) {
                $oldStatus = $deposit["status"] ?? "pending";
                $deposit["status"] = $status;
                $deposit["updated_at"] = date("d M Y, h:i A");
                if ($oldStatus !== $status) {
                    [$uIndex] = find_user($deposit["user_email"] ?? "");
                    if ($uIndex !== null) {
                        $users = users();
                        $amount = (float)($deposit["amount"] ?? 0);
                        if ($oldStatus !== "approved" && $status === "approved") {
                            $users[$uIndex]["balance"] = (float)($users[$uIndex]["balance"] ?? 0) + $amount;
                        }
                        if ($oldStatus === "approved" && $status !== "approved") {
                            $users[$uIndex]["balance"] = max(0, (float)($users[$uIndex]["balance"] ?? 0) - $amount);
                        }
                        save_users($users);
                    }
                }
                break;
            }
        }
        unset($deposit);
        save_deposits($deposits);
        flash("success", "Deposit updated.");
        redirect(admin_url("deposits"));
    }

    if ($action === "update_user") {
        $email = clean_email($_POST["email"] ?? "");
        $users = users();
        foreach ($users as &$user) {
            if (($user["email"] ?? "") === $email) {
                $user["name"] = clean($_POST["name"] ?? ($user["name"] ?? ""));
                $user["balance"] = (float)($_POST["balance"] ?? ($user["balance"] ?? 0));
                $user["status"] = clean($_POST["status"] ?? "active");
                break;
            }
        }
        unset($user);
        save_users($users);
        flash("success", "User updated.");
        redirect(admin_url("users"));
    }

    if ($action === "save_settings") {
        $newPassword = clean($_POST["new_admin_password"] ?? "");
        if ($newPassword !== "") {
            $admins = admins();
            foreach ($admins as &$admin) {
                if (($admin["username"] ?? "") === ($_SESSION["admin_username"] ?? "")) {
                    $admin["password"] = make_password_hash($newPassword);
                    break;
                }
            }
            unset($admin);
            save_admins($admins);
        }
        save_settings([
            "bkash_number" => clean_phone($_POST["bkash_number"] ?? ""),
            "support_whatsapp" => clean_phone($_POST["support_whatsapp"] ?? ""),
            "support_email" => clean_email($_POST["support_email"] ?? ""),
            "site_notice" => clean($_POST["site_notice"] ?? "")
        ]);
        flash("success", "Settings saved.");
        redirect(admin_url("settings"));
    }
}

$editProduct = null;
if ($section === "products" && isset($_GET["edit"])) [, $editProduct] = find_product(clean($_GET["edit"]));
$settings = site_settings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Control - 90N.GameShop</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body class="admin-body">
    <aside class="admin-sidebar">
        <a class="admin-brand" href="index.php"><span>90N</span><strong>90N.GameShop</strong><small>ADMIN</small></a>
        <nav>
            <?php foreach (["dashboard"=>"Dashboard","products"=>"Products","orders"=>"Orders","deposits"=>"Add Money","users"=>"Users","settings"=>"Settings"] as $key => $label): ?>
                <a class="<?= $section === $key ? 'active' : '' ?>" href="<?= admin_url($key) ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-bottom"><a href="../index.php">View Website</a><a href="logout.php">Logout</a></div>
    </aside>

    <main class="admin-main">
        <section class="admin-hero"><div><p>CONTROL PANEL</p><h1><?= e(ucwords($section)) ?></h1></div><span><?= e(current_admin()["name"] ?? "Admin") ?></span></section>
        <?php show_flash(); ?>

        <?php if ($section === "dashboard"): ?>
            <section class="stats-grid">
                <div><small>Users</small><strong><?= count(users()) ?></strong></div>
                <div><small>Products</small><strong><?= count(products()) ?></strong></div>
                <div><small>Orders</small><strong><?= count(orders()) ?></strong></div>
                <div><small>Add Money</small><strong><?= count(deposits()) ?></strong></div>
            </section>
        <?php endif; ?>

        <?php if ($section === "products"): ?>
            <section class="admin-grid two">
                <div class="admin-card">
                    <h2><?= $editProduct ? "Edit Product" : "Add Product" ?></h2>
                    <form method="POST" class="admin-form">
                        <input type="hidden" name="action" value="save_product">
                        <input type="hidden" name="edit_slug" value="<?= e($editProduct["slug"] ?? "") ?>">
                        <label>Name</label><input name="name" value="<?= e($editProduct["name"] ?? "") ?>" required>
                        <label>Slug</label><input name="slug" value="<?= e($editProduct["slug"] ?? "") ?>">
                        <label>Category</label><input name="category" value="<?= e($editProduct["category"] ?? "free-fire") ?>">
                        <label>Type</label><input name="type" value="<?= e($editProduct["type"] ?? "Game / Top up") ?>">
                        <label>Image</label><input name="image" value="<?= e($editProduct["image"] ?? "assets/images/product-1.png") ?>">
                        <label>Packages <small>Name | Price</small></label><textarea name="packages" rows="7" required><?= e(packages_to_text($editProduct["packages"] ?? [])) ?></textarea>
                        <label>Rules</label><textarea name="rules" rows="3"><?= e($editProduct["rules"] ?? "90N.GameShop") ?></textarea>
                        <label class="check-row"><input type="checkbox" name="active" <?= !isset($editProduct["active"]) || !empty($editProduct["active"]) ? "checked" : "" ?>> Active</label>
                        <button><?= $editProduct ? "Update" : "Add" ?> Product</button>
                    </form>
                </div>
                <div class="admin-card">
                    <h2>All Products</h2>
                    <?php foreach (products() as $product): ?>
                        <div class="list-row">
                            <div><strong><?= e($product["name"]) ?></strong><small><?= e($product["slug"]) ?></small></div>
                            <div class="row-actions">
                                <a href="<?= admin_url("products") ?>&edit=<?= e($product["slug"]) ?>">Edit</a>
                                <form method="POST"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="slug" value="<?= e($product["slug"]) ?>"><button>Delete</button></form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($section === "orders"): ?>
            <section class="admin-card"><h2>Topup Orders</h2><div class="table-wrap"><table><tr><th>ID</th><th>User</th><th>Product</th><th>UID</th><th>Price</th><th>Status</th><th>Action</th></tr>
                <?php foreach (array_reverse(orders()) as $order): ?><tr>
                    <td><?= e($order["id"] ?? "") ?></td><td><?= e($order["user_email"] ?? "") ?></td><td><?= e($order["product"] ?? "") ?></td><td><?= e($order["player_uid"] ?? "") ?></td><td><?= money($order["price"] ?? 0) ?></td><td><span class="badge <?= status_class($order["status"] ?? "pending") ?>"><?= e($order["status"] ?? "pending") ?></span></td>
                    <td><form method="POST" class="inline-form"><input type="hidden" name="action" value="update_order"><input type="hidden" name="id" value="<?= e($order["id"] ?? "") ?>"><select name="status"><?php foreach (["pending","processing","completed","cancelled"] as $s): ?><option value="<?= $s ?>" <?= (($order["status"] ?? "") === $s) ? "selected" : "" ?>><?= $s ?></option><?php endforeach; ?></select><button>Save</button></form></td>
                </tr><?php endforeach; ?>
            </table></div></section>
        <?php endif; ?>

        <?php if ($section === "deposits"): ?>
            <section class="admin-card"><h2>Add Money Requests</h2><div class="table-wrap"><table><tr><th>ID</th><th>User</th><th>Amount</th><th>TrxID</th><th>Status</th><th>Action</th></tr>
                <?php foreach (array_reverse(deposits()) as $deposit): ?><tr>
                    <td><?= e($deposit["id"] ?? "") ?></td><td><?= e($deposit["user_email"] ?? "") ?></td><td><?= money($deposit["amount"] ?? 0) ?></td><td><?= e($deposit["trxid"] ?? "") ?></td><td><span class="badge <?= status_class($deposit["status"] ?? "pending") ?>"><?= e($deposit["status"] ?? "pending") ?></span></td>
                    <td><form method="POST" class="inline-form"><input type="hidden" name="action" value="update_deposit"><input type="hidden" name="id" value="<?= e($deposit["id"] ?? "") ?>"><select name="status"><?php foreach (["pending","approved","rejected"] as $s): ?><option value="<?= $s ?>" <?= (($deposit["status"] ?? "") === $s) ? "selected" : "" ?>><?= $s ?></option><?php endforeach; ?></select><button>Save</button></form></td>
                </tr><?php endforeach; ?>
            </table></div></section>
        <?php endif; ?>

        <?php if ($section === "users"): ?>
            <section class="admin-card"><h2>Users</h2><div class="table-wrap"><table><tr><th>Name</th><th>Email</th><th>Balance</th><th>Status</th><th>Save</th></tr>
                <?php foreach (users() as $user): ?><tr><form method="POST">
                    <input type="hidden" name="action" value="update_user"><input type="hidden" name="email" value="<?= e($user["email"] ?? "") ?>">
                    <td><input name="name" value="<?= e($user["name"] ?? "") ?>"></td><td><?= e($user["email"] ?? "") ?></td><td><input type="number" name="balance" value="<?= e($user["balance"] ?? 0) ?>"></td>
                    <td><select name="status"><?php foreach (["active","inactive","banned"] as $s): ?><option value="<?= $s ?>" <?= (($user["status"] ?? "active") === $s) ? "selected" : "" ?>><?= $s ?></option><?php endforeach; ?></select></td><td><button>Save</button></td>
                </form></tr><?php endforeach; ?>
            </table></div></section>
        <?php endif; ?>

        <?php if ($section === "settings"): ?>
            <section class="admin-card"><h2>Settings</h2><form method="POST" class="admin-form wide">
                <input type="hidden" name="action" value="save_settings">
                <label>bKash Number</label><input name="bkash_number" value="<?= e($settings["bkash_number"] ?? "") ?>">
                <label>Support WhatsApp</label><input name="support_whatsapp" value="<?= e($settings["support_whatsapp"] ?? "") ?>">
                <label>Support Email</label><input type="email" name="support_email" value="<?= e($settings["support_email"] ?? "") ?>">
                <label>Notice</label><textarea name="site_notice" rows="4"><?= e($settings["site_notice"] ?? "") ?></textarea>
                <label>New Admin Password</label><input type="password" name="new_admin_password" placeholder="Leave blank to keep current">
                <button>Save Settings</button>
            </form></section>
        <?php endif; ?>
    </main>
</body>
</html>
