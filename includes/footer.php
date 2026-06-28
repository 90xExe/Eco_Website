<?php $footerSettings = site_settings(); ?>
<footer class="site-footer">
    <p>© <?= date("Y") ?> 90N.GameShop</p>
    <p>WhatsApp: <?= e($footerSettings["support_whatsapp"] ?? "") ?> | Email: <?= e($footerSettings["support_email"] ?? "") ?></p>
</footer>
<script src="assets/js/app.js?v=1"></script>
</body>
</html>
