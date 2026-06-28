<?php
require_once __DIR__ . "/includes/functions.php";
if (current_user()) redirect("profile.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = clean_email($_POST["email"] ?? "");
    $password = clean($_POST["password"] ?? "");
    [$index, $user] = find_user($email);

    if (!$user || !password_matches($password, $user["password"] ?? "")) {
        flash("error", "Invalid email or password.");
        redirect("login.php");
    }

    if ($index !== null && password_needs_upgrade($user["password"] ?? "")) {
        $users = users();
        $users[$index]["password"] = make_password_hash($password);
        save_users($users);
    }

    session_regenerate_id(true);
    $_SESSION["user_email"] = $email;
    flash("success", "Login successful.");
    redirect("profile.php");
}

$page_title = "Login - 90N.GameShop";
$body_class = "public-page";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/nav.php";
?>
<main class="page auth-page">
    <section class="auth-card">
        <h1>Login</h1>
        <?php show_flash(); ?>
        <form method="POST">
            <label>Email</label>
            <input type="email" name="email" placeholder="demo@gmail.com" required>
            <label>Password</label>
            <div class="password-field">
                <input type="password" name="password" placeholder="123456" required>
                <button type="button" class="eye-btn">Show</button>
            </div>
            <button class="btn" type="submit">Login</button>
        </form>
        <p>Demo: <b>demo@gmail.com</b> / <b>123456</b></p>
        <p>Don't have account? <a href="register.php">Register</a></p>
    </section>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
