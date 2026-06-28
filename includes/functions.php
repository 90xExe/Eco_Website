<?php
if (file_exists(__DIR__ . "/../config.php")) {
    require_once __DIR__ . "/../config.php";
}

if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off")
        || (($_SERVER["HTTP_X_FORWARDED_PROTO"] ?? "") === "https");

    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "secure" => $isSecure,
        "httponly" => true,
        "samesite" => "Lax"
    ]);

    session_start();
}

date_default_timezone_set("Asia/Dhaka");

const DEFAULT_ADMIN_PASSWORD_HASH = '$2y$10$pWpSy3sCCZjDEqK17I2PR.iS3BcOvIyquIz3KiASmkgsqqlYrU2NG';
const DEFAULT_USER_PASSWORD_HASH = '$2y$10$Sdi.dBxQvvgeijQS.q4hfuznpUtvaTu2Qh/fhQAoW4t0HCjQZRWpS';

function data_path(string $file): string {
    $baseDir = defined("ECO_DATA_DIR")
        ? rtrim((string)ECO_DATA_DIR, "/\\")
        : __DIR__ . "/../data";

    return $baseDir . "/" . basename($file);
}

function write_json(string $file, array $data): void {
    $path = data_path($file);
    $dir = dirname($path);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $json = json_encode(array_values($data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    if (in_array($file, ["settings.json"], true)) {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    if ($json === false || file_put_contents($path, $json, LOCK_EX) === false) {
        throw new RuntimeException("Could not write data file: " . $path);
    }
}

function read_json(string $file, array $default = []): array {
    $path = data_path($file);

    if (!file_exists($path) || trim((string)@file_get_contents($path)) === "") {
        write_json($file, $default);
        return $default;
    }

    $data = json_decode((string)file_get_contents($path), true);
    if (!is_array($data)) {
        write_json($file, $default);
        return $default;
    }

    return $data;
}

function e($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function clean($value): string {
    return trim((string)$value);
}

function clean_email($email): string {
    return strtolower(trim((string)$email));
}

function clean_phone($phone): string {
    return preg_replace('/[^0-9+]/', '', trim((string)$phone));
}

function make_password_hash(string $password): string {
    return password_hash($password, PASSWORD_DEFAULT);
}

function password_is_hash(string $stored): bool {
    return preg_match('/^\$(2y|2a|2x|argon2i|argon2id)\$/', (string)$stored) === 1;
}

function password_matches(string $password, string $stored): bool {
    $stored = (string)$stored;
    if ($stored === "") return false;
    if (password_is_hash($stored)) return password_verify($password, $stored);
    return hash_equals($stored, $password);
}

function password_needs_upgrade(string $stored): bool {
    $stored = (string)$stored;
    return !password_is_hash($stored) || password_needs_rehash($stored, PASSWORD_DEFAULT);
}

function redirect(string $url): never {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    header("Location: " . $url);
    exit;
}

function flash(string $type, string $message): void {
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}

function get_flash(): ?array {
    if (empty($_SESSION["flash"])) return null;
    $flash = $_SESSION["flash"];
    unset($_SESSION["flash"]);
    return $flash;
}

function show_flash(): void {
    $flash = get_flash();
    if (!$flash) return;
    echo '<div class="alert ' . e($flash["type"] ?? "info") . '">' . e($flash["message"] ?? "") . '</div>';
}

function make_id(string $prefix): string {
    return $prefix . date("YmdHis") . random_int(100, 999);
}

function money($amount): string {
    $amount = (float)$amount;
    return floor($amount) == $amount ? number_format($amount, 0) . " tk" : number_format($amount, 2) . " tk";
}

function status_class(string $status): string {
    $status = strtolower(trim($status));
    if (in_array($status, ["completed", "approved", "active"], true)) return "success";
    if (in_array($status, ["cancelled", "rejected", "banned", "inactive"], true)) return "danger";
    if ($status === "processing") return "info";
    return "warning";
}

function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return $text !== "" ? $text : "item-" . time();
}

function default_admins(): array {
    return [[
        "id" => "ADM001",
        "name" => "Main Admin",
        "username" => "admin",
        "password" => DEFAULT_ADMIN_PASSWORD_HASH,
        "created_at" => date("d M Y")
    ]];
}

function default_users(): array {
    return [[
        "id" => "USRDEMO001",
        "name" => "Demo User",
        "email" => "demo@gmail.com",
        "password" => DEFAULT_USER_PASSWORD_HASH,
        "phone" => "",
        "balance" => 500,
        "status" => "active",
        "created_at" => date("d M Y")
    ]];
}

function default_categories(): array {
    return [[
        "id" => "CAT001",
        "name" => "Free Fire",
        "slug" => "free-fire",
        "active" => true,
        "created_at" => date("d M Y")
    ]];
}

function default_products(): array {
    return [
        [
            "name" => "TOP UP BD (UID) AI",
            "slug" => "top-up-bd-uid-ai",
            "category" => "free-fire",
            "type" => "Game / Top up",
            "image" => "assets/images/product-1.png",
            "active" => true,
            "rules" => "90N.GameShop",
            "packages" => [
                ["name" => "25 Diamonds", "price" => 20],
                ["name" => "50 Diamonds", "price" => 35],
                ["name" => "100 Diamonds", "price" => 70]
            ]
        ],
        [
            "name" => "Weekly & Monthly (UID)",
            "slug" => "weekly-monthly-uid",
            "category" => "free-fire",
            "type" => "Game / Top up",
            "image" => "assets/images/product-2.png",
            "active" => true,
            "rules" => "90N.GameShop",
            "packages" => [
                ["name" => "Weekly", "price" => 158],
                ["name" => "Monthly", "price" => 790]
            ]
        ],
        [
            "name" => "WEEKLY LITE",
            "slug" => "weekly-lite",
            "category" => "free-fire",
            "type" => "Game / Top up",
            "image" => "assets/images/product-3.png",
            "active" => true,
            "rules" => "90N.GameShop",
            "packages" => [
                ["name" => "1x Weekly Lite", "price" => 40],
                ["name" => "2x Weekly Lite", "price" => 80]
            ]
        ]
    ];
}

function default_settings(): array {
    return [
        "bkash_number" => "01349723513",
        "support_whatsapp" => "01349723513",
        "support_email" => "support@90ngameshop.com",
        "site_notice" => "",
        "notice_active" => false,
        "active_message" => "Your account is active.",
        "inactive_message" => "Your account is inactive. Please contact support.",
        "banned_message" => "Your account is banned. Please contact support."
    ];
}

function admins(): array { return read_json("admins.json", default_admins()); }
function save_admins(array $items): void { write_json("admins.json", $items); }
function users(): array { return read_json("users.json", default_users()); }
function save_users(array $items): void { write_json("users.json", $items); }
function categories(): array { return read_json("categories.json", default_categories()); }
function save_categories(array $items): void { write_json("categories.json", $items); }
function products(): array { return read_json("products.json", default_products()); }
function save_products(array $items): void { write_json("products.json", $items); }
function orders(): array { return read_json("orders.json", []); }
function save_orders(array $items): void { write_json("orders.json", $items); }
function deposits(): array { return read_json("deposits.json", []); }
function save_deposits(array $items): void { write_json("deposits.json", $items); }
function coupons(): array { return read_json("coupons.json", []); }
function save_coupons(array $items): void { write_json("coupons.json", $items); }
function site_settings(): array { return array_merge(default_settings(), read_json("settings.json", default_settings())); }
function save_settings(array $settings): void { write_json("settings.json", array_merge(site_settings(), $settings)); }

function find_admin(string $username): array {
    foreach (admins() as $index => $admin) {
        if (($admin["username"] ?? "") === $username) return [$index, $admin];
    }
    return [null, null];
}

function find_user(string $email): array {
    $email = clean_email($email);
    foreach (users() as $index => $user) {
        if (($user["email"] ?? "") === $email) return [$index, $user];
    }
    return [null, null];
}

function find_category(string $slug): array {
    foreach (categories() as $index => $cat) {
        if (($cat["slug"] ?? "") === $slug) return [$index, $cat];
    }
    return [null, null];
}

function find_product(string $slug): array {
    foreach (products() as $index => $product) {
        if (($product["slug"] ?? "") === $slug) return [$index, $product];
    }
    return [null, null];
}

function current_user(): ?array {
    if (empty($_SESSION["user_email"])) return null;
    [, $user] = find_user($_SESSION["user_email"]);
    return $user ?: null;
}

function current_admin(): ?array {
    if (empty($_SESSION["admin_username"])) return null;
    [, $admin] = find_admin($_SESSION["admin_username"]);
    return $admin ?: null;
}

function require_login(): void {
    if (!current_user()) {
        flash("error", "Please login first.");
        redirect("login.php");
    }
}

function require_admin(): void {
    if (!current_admin()) {
        flash("error", "Admin login required.");
        redirect("login.php");
    }
}

function user_orders(string $email): array {
    return array_values(array_filter(orders(), fn($order) => ($order["user_email"] ?? "") === $email));
}

function user_deposits(string $email): array {
    return array_values(array_filter(deposits(), fn($deposit) => ($deposit["user_email"] ?? "") === $email));
}

function parse_packages(string $text): array {
    $items = [];
    foreach (preg_split("/\r\n|\n|\r/", $text) as $line) {
        $line = trim($line);
        if ($line === "") continue;
        $parts = array_map("trim", explode("|", $line));
        $name = $parts[0] ?? "";
        $price = (float)($parts[1] ?? 0);
        if ($name !== "" && $price >= 0) $items[] = ["name" => $name, "price" => $price];
    }
    return $items;
}

function packages_to_text(array $packages): string {
    return implode("\n", array_map(fn($pkg) => ($pkg["name"] ?? "") . " | " . ($pkg["price"] ?? 0), $packages));
}

function find_coupon(string $code): ?array {
    $code = strtoupper(trim($code));
    foreach (coupons() as $coupon) {
        if (strtoupper($coupon["code"] ?? "") === $code && !empty($coupon["active"])) return $coupon;
    }
    return null;
}

function coupon_discount(float $price, string $code): array {
    $coupon = find_coupon($code);
    if (!$coupon) return [0, null];
    $expires = trim((string)($coupon["expires_at"] ?? ""));
    if ($expires !== "" && $expires < date("Y-m-d")) return [0, null];
    $percent = max(0, min(100, (float)($coupon["percent"] ?? 0)));
    return [round(($price * $percent) / 100, 2), $coupon];
}
