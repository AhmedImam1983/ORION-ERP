# Orion ERP

**Orion ERP** is a modular business management system built with Laravel and Filament. It brings day-to-day operations—including sales, invoicing, point of sale, inventory, customer and supplier records, and financial reporting—into one application.

> This README describes the project structure and setup based on the repository. Configure and test each integration in your own environment before using it in production.

## Core areas

- **Sales and invoicing** — manage invoices and related records.
- **Point of sale (POS)** — support cashier workflows and receipts.
- **Inventory** — manage products and inventory-related operations.
- **Customers and suppliers** — maintain business partner records.
- **Accounting and reports** — provide financial data and reporting features.
- **Installments** — generate and record installment payments.
- **Exports and documents** — generate PDF documents, barcode labels, and Excel exports.
- **Attachments** — upload and manage files associated with application records.
- **Integrations** — configuration points for services such as WhatsApp and e-invoicing.

Available features and permissions depend on the current implementation and environment configuration.

## Technology stack

- PHP `^8.3`
- Laravel `^13.8`
- Filament `^5.6`
- MySQL or another database supported by the configured Laravel connection
- Node.js and npm for frontend assets
- Composer for PHP dependencies

See `composer.json` and `package.json` for the authoritative dependency lists.

## Requirements

Install versions compatible with the constraints in `composer.json` and `package.json`:

- PHP and Composer
- A supported database server
- Node.js and npm
- PHP extensions required by Laravel and the installed packages

## Local installation

1. Clone the repository and enter the project directory:

   ```bash
   git clone https://github.com/AhmedImam1983/ORION-ERP.git
   cd ORION-ERP
   ```

2. Install PHP dependencies:

   ```bash
   composer install
   ```

3. Create your local environment file and generate an application key:

   **Windows PowerShell**
   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

   **Linux / macOS**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. Configure `.env` for your local database and any services you intend to use. Do not commit `.env` or real credentials.

5. Create the database, then run migrations:

   ```bash
   php artisan migrate
   ```

6. Install frontend dependencies and build assets:

   ```bash
   npm install
   npm run build
   ```

7. Start the local development server:

   ```bash
   php artisan serve
   ```

Open the local URL printed by Artisan. Follow the application's existing authentication and user-provisioning process to access the admin panel.

## Scheduled tasks and background work

The repository defines scheduled commands for database backups and daily alerts in `routes/console.php`. Configure Laravel's scheduler in the deployment environment and verify that the referenced commands are installed and working before relying on them.

If queue workers are used by your configured features, run and supervise them according to your deployment setup.

## Configuration and integrations

Review the configuration files and `.env.example` for the variables used by your deployment, including database access and any enabled third-party integrations.

- Keep production credentials in environment variables or a secrets manager.
- Never commit `.env`, API tokens, private keys, database dumps, or real customer data.
- Set `APP_ENV=production` and `APP_DEBUG=false` in production.
- Use HTTPS and secure session/cookie settings in production.
- Configure backup storage with restricted access, retention, and restore testing.
- Test external integrations in a non-production environment before enabling them for real transactions.

## Testing and code quality

Run the test suite with:

```bash
php artisan test
```

You can also run Laravel Pint if it is installed:

```bash
vendor/bin/pint --test
```

The current test suite may not cover every business workflow. Add regression tests for permissions, branch isolation, invoice numbering, inventory movements, installments, accounting entries, and report exports as those areas are validated.

## Deployment checklist

Before deploying to production:

- [ ] Install dependencies using the versions supported by the lock files.
- [ ] Configure production environment variables and generate a unique `APP_KEY`.
- [ ] Set `APP_DEBUG=false`.
- [ ] Apply migrations after backing up the database.
- [ ] Build frontend assets.
- [ ] Configure HTTPS, file permissions, scheduler, and queue workers as required.
- [ ] Verify authorization and branch-level data isolation.
- [ ] Test backup creation and restoration.
- [ ] Run automated tests and critical business-flow checks.
- [ ] Confirm that secrets, local files, and real data are not included in Git.

## License

No project-specific license information was verified as part of this README update. Confirm the intended license before distributing Orion ERP.
