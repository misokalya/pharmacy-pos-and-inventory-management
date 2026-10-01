# PharmaCare
by Miso Kimiti - https://instagram.com/miso_geek - misokalya@yahoo.com

PharmaCare is a browser-based pharmacy sales and inventory management application built with PHP and MySQL. It supports staff workflows for managing medicines, receiving stock by batch, point-of-sale transactions, and monitoring expiry and low-stock levels.

## Features

- Role-based access for administrators, pharmacists, and cashiers
- Product, category, supplier, and user management
- Batch-based inventory receiving and stock adjustments
- Point of sale, sales history, and printable receipts
- Expiry, low-stock, sales, and inventory valuation reports
- Application settings and a scheduled expiry digest script

## Technology

- PHP 8.0 or later
- MySQL 8 or MariaDB 10.4 or later
- Apache with `mod_rewrite` enabled
- No Composer installation is required

The application uses a small in-project router, controllers, models, and PHP views. Tailwind CSS and Font Awesome are loaded from CDNs.

## Local Setup

The project is configured for XAMPP on Windows by default.

1. Place the project at `C:\xampp\htdocs\pharmacy`.
2. Start Apache and MySQL in the XAMPP Control Panel.
3. Create a MySQL database named `pharmacy_db` using `utf8mb4` character encoding.
4. Set the database connection in `config/database.php`.
5. Set the application URL in `config/app.php`. The default is `http://localhost/pharmacy/public`.
6. Enable Apache's `mod_rewrite` module and allow `.htaccess` overrides for the project directory.
7. Open `http://localhost/pharmacy/public/` in a browser.

See [setup.md](setup.md) for the longer XAMPP setup guide and optional expiry-digest scheduling instructions.

### Database Setup Status

The checked-in `database/schema.sql` does not currently define all tables required by the application, including `batches` and `stock_movements`. The setup guide also refers to additional `phase*.sql` files that are not present in this repository. Therefore, importing `schema.sql` alone is not sufficient to initialize a working database for inventory and sales. The missing schema/migration files must be supplied before completing database setup.

## Project Layout

- `app/controllers/` - Request handlers and page workflows
- `app/models/` - Database access and domain operations
- `app/core/` - Router, bootstrap, and database connection
- `app/middleware/` - Authentication and guest access checks
- `config/` - Application and database configuration
- `database/` - SQL schema files
- `public/` - Web entry point, assets, uploads, and URL rewriting rules
- `scripts/` - CLI scripts, including the expiry digest
- `storage/` - Export and log output
- `views/` - PHP templates for application pages

## Default Account

The seed data in `database/schema.sql` creates an administrator account:

- Email: `admin@pharmacy.com`
- Password: `admin123`

Change the password immediately after the first successful sign-in. Do not use these credentials outside a local development environment.

## Deployment Notes

Before deploying, disable debug output, use a least-privilege database account, serve the app over HTTPS, configure secure session cookies, keep non-public application files outside the web root where possible, and remove development-only utilities such as `public/hash.php`.
