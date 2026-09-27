<?php
// Visit http://localhost/pharmacy/hash.php?p=YourPassword
// Copy the output hash and UPDATE users SET password_hash='...' WHERE email='admin@pharmacy.local';
$p = $_GET['p'] ?? 'admin123';
echo '<pre>' . password_hash($p, PASSWORD_BCRYPT) . '</pre>';