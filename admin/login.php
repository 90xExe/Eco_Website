<?php
require_once __DIR__ . "/../includes/functions.php";
if (current_admin()) redirect("index.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = clean($_POST["username"] ?? "");
    $password = clean($_POST["password"] ?? "");
    [$index, $admin] = find_admin($username);

    if (!$admin || !password_matches($password, $admin["password"] ?? "")) {
        flash("error", "Invalid admin username or password.");
        redirect("login.php");
    }

    if ($index !== null && password_needs_upgrade($admin["password"] ?? "")) {
        $admins = admins();
        $admins[$index]["password"] = make_password_hash($password);
        save_admins($admins);
    }

    session_regenerate_id(true);
    $_SESSION["admin_username"] = $username;
    flash("success", "Admin login successful.");
    redirect("index.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - 90N.GameShop</title>
    <link rel="stylesheet" href="../assets/css/admin.css?v=1">
</head>
<body class="admin-login-body">
    <main class="admin-login-card">
        <div class="admin-logo">90N</div>
        <p>ADMIN CONTROL</p>
        <h1>90N.GameShop</h1>
        <?php show_flash(); ?>
        <form method="POST">
            <label>Username</label>
            <input type="text" name="username" value="admin" required>
            <label>Password</label>
            <input type="password" name="password" placeholder="admin123" required>
            <button type="submit">Login Admin</button>
        </form>
        <small>Default: admin / admin123</small>
        <a href="../index.php">Back to Website</a>
    </main>
</body>
</html>
