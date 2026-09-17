# Superadmin Feature

This document describes the superadmin functionality for viewing all submissions across all fields.

## Overview

The superadmin feature allows designated admin users to:
- View all submissions from all fields in a single table view
- See submission details including date, type, field, location, and evaluator
- Access reports directly from the admin dashboard

## Setup

### For New & Existing Installations

Run the standard Laravel migrations and database seeder:

```bash
php artisan migrate --seed
```

Or to run just the seeders:

```bash
php artisan db:seed
```

This will create:
- All database tables with proper foreign key constraints
- A demo user (`joel@uconn.edu` / `password`) attached to a demo athletic field
- A superadmin user (`admin@tgmsuite.com` / `admin123`)

## Default Superadmin Credentials

```
Email: admin@tgmsuite.com
Password: admin123
```

**⚠️ IMPORTANT:** Change this password after first login!

## Usage

1. Log in with superadmin credentials
2. Click the "Admin" link in the navigation bar (visible only to admin users)
3. View and filter submissions across all fields by report type and date range
4. Click "View Report" to see individual submission details

## Creating Additional Admin Users

To grant an existing user admin privileges via Laravel Tinker:

```bash
php artisan tinker
```

Then run:

```php
App\Models\User::where('email', 'user@example.com')->update(['is_admin' => true]);
```

## Security Notes

- Superadmins (`is_admin = 1`) can see all submissions and manage all fields.
- Regular users (`is_admin = 0`) can only view and manage fields linked to them in `field_user` via `FieldPolicy`.
- Non-admin access to `/admin/submissions` is rejected with HTTP 403 Forbidden.

## Legacy Archive

The legacy Slim 4 application, old migration scripts, and templates have been archived under `legacy/` (`legacy/public/`, `legacy/migrate-admin.php`, `legacy/seed.php`). All active application code now runs via Laravel 11 at the project root.
