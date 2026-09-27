Pharmacy Sales & Inventory Management System

Prerequisites
Item	Version	Notes
XAMPP	8.1+ (with PHP 8.0+, MySQL 8 / MariaDB 10.4+)	apachefriends.org
Web browser	Chrome / Firefox / Edge (latest)	Chrome recommended for DevTools
Text editor	VS Code / Notepad++ / Sublime	Any editor works
Composer (optional)	Latest	Not required — project is dependency-free
Step 1 — Install XAMPP
Download the XAMPP installer for Windows from the official site.

Run the installer. Accept defaults:

Install path: C:\xampp\

Components: Apache, MySQL, PHP, phpMyAdmin (required); Mercury and FileZilla can be unchecked.

Finish the installer. Do not start the Control Panel yet.

Step 2 — Copy the Project
Copy the entire project folder into XAMPP's web root:

text
C:\xampp\htdocs\pharmacy\
Confirm the final structure:

text
C:\xampp\htdocs\pharmacy\
├── app\
├── config\
├── database\
├── public\
├── scripts\
├── storage\
└── views\
Important: The web root is /public, not the project root. You will access the app at http://localhost/pharmacy/public/….

Step 3 — Start Services
Open XAMPP Control Panel.

Click Start next to Apache.

Click Start next to MySQL.

Both should turn green. If Apache fails to start, another service (IIS, Skype) is using port 80 — change Apache's port in httpd.conf to 8080 and use http://localhost:8080/… from then on.

Step 4 — Create the Database
In a browser, open: http://localhost/phpmyadmin

Click New in the left sidebar.

Database name: pharmacy_db

Collation: utf8mb4_unicode_ci

Click Create.

With pharmacy_db selected, click the Import tab.

Click Choose File → navigate to:

text
C:\xampp\htdocs\pharmacy\database\schema.sql
Scroll down → click Go (Import).

Then repeat the Import step for each phase file in order:

text
phase4.sql
phase4_images.sql
phase5.sql
phase7.sql
phase11.sql
Each one adds tables, columns, or seed data. Import them in this order.

At the end, your database should contain these tables:
users, settings, login_attempts, categories, suppliers, products, batches, stock_movements, sales, sale_items, invoice_counters.

Step 5 — Configure the App
Open C:\xampp\htdocs\pharmacy\config\database.php:

php
<?php
return [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'database' => 'pharmacy_db',
    'username' => 'root',
    'password' => '',           // XAMPP default is empty
    'charset'  => 'utf8mb4',
];
If you set a MySQL root password during installation, put it in password. Otherwise leave blank.

Open C:\xampp\htdocs\pharmacy\config\app.php:

php
<?php
return [
    'name'      => 'PharmaCare',
    'env'       => 'development',
    'debug'     => true,        // set false in production
    'url'       => 'http://localhost/pharmacy/public',
    'timezone'  => 'Africa/Dar_es_Salaam',
    'session_name' => 'pharmacy_session',
];
Confirm the url matches the exact address you'll use in the browser. If XAMPP runs on port 8080, use http://localhost:8080/pharmacy/public.

Step 6 — Create the Uploads Folder
Navigate to C:\xampp\htdocs\pharmacy\public\

Create folder: uploads

Inside uploads, create folder: products

Final path: C:\xampp\htdocs\pharmacy\public\uploads\products\

This is where product images are stored. Windows gives Apache write permission automatically for folders inside htdocs when running as the default account — no chmod needed.

Step 7 — Set the Admin Password
Open a browser and visit:

text
http://localhost/pharmacy/hash.php?p=admin123
(This helper script is in the project root.)

Copy the bcrypt hash it produces (starts with $2y$10$…).

Go to phpMyAdmin → pharmacy_db → SQL tab → run:

sql
UPDATE users
SET password_hash = 'PASTE_HASH_HERE'
WHERE email = 'admin@pharmacy.com';
Delete hash.php from the project root when done — it's a security risk to leave in place.

Step 8 — Enable URL Rewriting
Open C:\xampp\apache\conf\httpd.conf in a text editor.

Find the line:

text
#LoadModule rewrite_module modules/mod_rewrite.so
Remove the leading # so it reads:

text
LoadModule rewrite_module modules/mod_rewrite.so
Find the <Directory "C:/xampp/htdocs"> block. Change:

text
AllowOverride None
to:

text
AllowOverride All
Save the file.

In XAMPP Control Panel, click Stop next to Apache, then Start again.

Without this step, the .htaccess file in /public won't be honored and every URL will return Apache's 404.

Step 9 — First Login
Visit:

text
http://localhost/pharmacy/public/
You'll be redirected to the login page.

Sign in:

Email: admin@pharmacy.com

Password: admin123

Immediately change the password:

Click your name (top right) → My Profile → Change Password.

Step 10 — Initial Configuration
Go to Settings:

App name (default: PharmaCare)

Currency: TZS / Symbol: Tsh

VAT rate: 18 (Tanzania VAT) or 0 if VAT-exempt

Expiry alert window: 90 days

Critical window: 30 days

Alert email: your email (for the daily digest)

Save.

Go to Users → create users for staff:

Pharmacist → gets Products, Inventory, Reports access

Cashier → gets POS only

Go to Suppliers → create at least one supplier.

Go to Categories → create drug categories (Analgesics, Antibiotics, Vitamins, etc.).

Go to Products → add your first products (with images).

Go to Inventory → Receive Stock → create batches for each product with expiry dates.

Optional — Schedule the Daily Expiry Digest
Windows Task Scheduler:

Open Task Scheduler → Create Basic Task.

Name: Pharmacy Expiry Digest

Trigger: Daily, time: 07:00

Action: Start a program

Program: C:\xampp\php\php.exe

Arguments: C:\xampp\htdocs\pharmacy\scripts\send_expiry_digest.php

Start in: C:\xampp\htdocs\pharmacy\

Finish.

To send email on Windows, PHP's built-in mail() needs an SMTP server. Configure C:\xampp\php\php.ini:

ini
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = your-email@gmail.com
sendmail_path = "\"C:\xampp\sendmail\sendmail.exe\" -t"
auth_username = your-email@gmail.com
auth_password = your-app-password
Or use a local SMTP tool like MailHog / Papercut for testing. If email isn't configured, the digest still logs its output to storage/logs/digest.log.

Troubleshooting
Symptom	Cause	Fix
Apache won't start	Port 80 in use	Change to 8080 in httpd.conf
database connection failed	MySQL not running, or wrong creds	Start MySQL; verify config/database.php
All URLs 404	mod_rewrite disabled or AllowOverride wrong	Redo Step 8
Login says "CSRF mismatch"	Cookies blocked	Enable cookies; clear site data
Product images don't save	Missing uploads folder	Redo Step 6
Class "App..." not found	File missing or namespace mismatch	Verify filename = class name, folder = app/models/, etc.
HAVING SQL error	MariaDB version quirk	Use the subquery version in Report::lowStock()
Dark mode doesn't work	Tailwind darkMode: 'class' missing	Add to layout <head> script block
Tsh not showing	Settings not saved	Settings → save currency
Production Notes (Do Not Skip for Deployment)
Before deploying to a real environment:

In config/app.php: set 'env' => 'production' and 'debug' => false.

Create a MySQL user with least privileges for pharmacy_db — don't use root.

Serve over HTTPS. Update session_set_cookie_params(['secure' => true]) in bootstrap.php.

Move storage/, app/, config/, database/ above the web root so they're not directly accessible. Only public/ should be web-visible.

Set up automated MySQL backups (daily mysqldump).

Delete hash.php if you haven't already.

