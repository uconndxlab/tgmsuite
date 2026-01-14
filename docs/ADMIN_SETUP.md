# Superadmin Feature

This document describes the superadmin functionality for viewing all submissions across all fields.

## Overview

The superadmin feature allows designated admin users to:
- View all submissions from all fields in a single table view
- See submission details including date, type, field, location, and evaluator
- Access reports directly from the admin dashboard

## Setup

### For New Installations

If you're setting up a fresh database, run the seed script:

```bash
cd public
php seed.php
```

This will create:
- All necessary database tables including the `is_admin` column
- A test user (joel@uconn.edu / password: password)
- A superadmin user (admin@tgmsuite.com / password: admin123)

### For Existing Installations

If you already have a database, run the migration script:

```bash
cd public
php migrate-admin.php
```

This will:
- Add the `is_admin` column to the users table
- Create a superadmin user if one doesn't exist
- Preserve all existing data

## Default Superadmin Credentials

```
Email: admin@tgmsuite.com
Password: admin123
```

**⚠️ IMPORTANT:** Change this password after first login!

## Usage

1. Log in with superadmin credentials
2. Click the "Admin" link in the navigation bar (visible only to admin users)
3. View all submissions in the table
4. Click "View Report" to see individual submission details

## Creating Additional Admin Users

To make an existing user an admin:

```bash
sqlite3 turfgrass.db
```

Then run:

```sql
UPDATE users SET is_admin = 1 WHERE email = 'user@example.com';
```

Or manually in the database, set the `is_admin` column to `1` for the user.

## Security Notes

- Admin users can see ALL submissions across ALL fields
- Regular users can only see submissions for fields they have access to
- The admin page is protected and will redirect non-admin users to the fields page

## Database Schema Changes

The `users` table now includes:

```sql
is_admin INTEGER DEFAULT 0
```

Where:
- `0` = Regular user (default)
- `1` = Superadmin user

## Files Modified

- `public/seed.php` - Added is_admin column and superadmin creation
- `public/AuthMiddleware.php` - Added isAdmin() method
- `public/index.php` - Added admin status tracking and /admin/submissions route
- `public/templates/admin-submissions.html` - New admin dashboard template
- `public/templates/parts/header.html` - Added admin navigation link
- `public/migrate-admin.php` - Migration script for existing installations
