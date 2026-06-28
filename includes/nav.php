<?php $navUser = current_user(); ?>
<header class="site-header">
    <a class="brand" href="index.php"><span>90N</span><strong>90N.GameShop</strong></a>
    <nav>
        <a href="index.php">Home</a>
        <a href="index.php#free-fire">Topup</a>
        <?php if ($navUser): ?>
            <a href="profile.php">Profile</a>
            <a href="orders.php">Orders</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="register.php">Register</a>
            <a class="pill" href="login.php">Login</a>
        <?php endif; ?>
    </nav>
</header>
