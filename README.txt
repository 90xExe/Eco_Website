Eco_Website - 90N.GameShop

Clean PHP + JSON wallet top-up project.

Default admin:
Username: admin
Password: admin123

Default user:
Email: demo@gmail.com
Password: 123456

Local XAMPP:
1. Copy Eco_Website to C:\xampp\htdocs\
2. Start Apache
3. Open http://localhost/Eco_Website/

VPS:
Project path:
/home/monirul4213/web/host.mangroveesp.com/public_html/Eco_Web

Recommended data path:
/home/monirul4213/eco_web_data

Create config.php:
<?php
define("ECO_DATA_DIR", "/home/monirul4213/eco_web_data");

After install:
php tools/reset_demo_login.php
chmod -R 775 /home/monirul4213/eco_web_data

Links:
Website: https://host.mangroveesp.com/Eco_Web/
Admin: https://host.mangroveesp.com/Eco_Web/admin/login.php

Main features:
- User register/login
- Wallet balance
- Add-money request
- Admin approve/reject wallet requests
- Product/package management
- Wallet top-up order
- Order status management
