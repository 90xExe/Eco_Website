<?php
require_once __DIR__ . "/includes/functions.php";
if (current_user()) redirect("profile.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = clean($_POST["name"] ?? "");
    $email = clean_email($_POST["email"] ?? "");
    $password = clean($_POST["password"] ?? "");
    $confirm = clean($_POST["confirm"] ?? "");

    if ($name === "" || $email === "" || $password === "") {
        flash("error", "All fields are required.");
        redirect("register.php");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash("error", "Invalid email address.");
        redirect("register.php");
    }
    if ($password !== $confirm) {
        flash("error", "Passwords do not match.");
        redirect("register.php");
    }
    [, $exists] = find_user($email);
    if ($exists) {
        flash("error", "Email already registered.");
        redirect("register.php");
    }

    $users = users();
    $users[] = [
        "id" => make_id("USR"),
        "name" => $name,
        "email" => $email,
        "password" => make_password_hash($password),
        "phone" => "",
        "balance" => 0,
        "status" => "active",
        "created_at" => date("d M Y")
    ];
    save_users($users);

    [, $created] = find_user($email);
    if (!$created) {
        flash("error", "Account could not be saved. Please check data folder permission.");
        redirect("register.php");
    }

    session_regenerate_id(true);
    $_SESSION["user_email"] = $email;
    flash("success", "Account created successfully.");
    redirect("profile.php");
}

$page_title = "Register - 90N.GameShop";
$body_class = "public-page";
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/nav.php";
?>
<main class="page auth-page">
    <section class="auth-card">
        <h1>Register</h1>
        <?php show_flash(); ?>
        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" required>
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Password</label>
            <div class="password-field">
                <input type="password" name="password" required>
                <button type="button" class="eye-btn">Show</button>
            </div>
            <label>Confirm Password</label>
            <div class="password-field">
                <input type="password" name="confirm" required>
                <button type="button" class="eye-btn">Show</button>
            </div>
            <button class="btn" type="submit">Register</button>
        </form>
        <p>Already have account? <a href="login.php">Login</a></p>
    </section>
</main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>
