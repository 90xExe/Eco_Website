<?php
if (PHP_SAPI !== "cli") {
    http_response_code(403);
    exit("CLI only.");
}

require_once __DIR__ . "/../includes/functions.php";

$admins = admins();
$foundAdmin = false;
foreach ($admins as &$admin) {
    if (($admin["username"] ?? "") === "admin") {
        $admin["name"] = "Main Admin";
        $admin["password"] = make_password_hash("admin123");
        $admin["updated_at"] = date("d M Y, h:i A");
        $foundAdmin = true;
        break;
    }
}
unset($admin);
if (!$foundAdmin) $admins[] = default_admins()[0];
save_admins($admins);

$users = users();
$foundUser = false;
foreach ($users as &$user) {
    if (($user["email"] ?? "") === "demo@gmail.com") {
        $user["name"] = "Demo User";
        $user["password"] = make_password_hash("123456");
        $user["balance"] = 500;
        $user["status"] = "active";
        $user["updated_at"] = date("d M Y, h:i A");
        $foundUser = true;
        break;
    }
}
unset($user);
if (!$foundUser) $users[] = default_users()[0];
save_users($users);

echo "Reset complete." . PHP_EOL;
echo "Admin: admin / admin123" . PHP_EOL;
echo "User: demo@gmail.com / 123456" . PHP_EOL;
echo "Data dir: " . dirname(data_path("users.json")) . PHP_EOL;
