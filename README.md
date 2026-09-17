# Athletic Field Assessment Tool (TGM Suite)

The **Athletic Field Assessment Tool** is a web platform built for municipal groundskeepers, athletic directors, and turf managers to assess, track, and report on the quality of natural and synthetic turf fields.

Converted from legacy Slim 4 to **Laravel 11**.

---

## Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone git@github.com:uconndxlab/tgmsuite.git
   cd tgmsuite
   ```

2. **Install PHP Dependencies:**
   ```bash
   composer install
   ```

3. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```
   This creates:
   - All relational tables for athletic fields and 10 assessment report types
   - Superadmin user: `admin@tgmsuite.com` / `admin123`
   - Field manager demo account: `joel@uconn.edu` / `password`
   - Demo athletic field: `Memorial Stadium Turf`

---

## Default Credentials

| Role | Email | Password |
|---|---|---|
| Superadmin | `admin@tgmsuite.com` | `admin123` |
| Field Manager | `joel@uconn.edu` | `password` |
