# LifeLine — Blood Donor Finder and Emergency Coordination

LifeLine connects patients, families, hospitals and blood banks with **verified volunteer blood donors** in
Butuan City. A requester sends an emergency blood request, LifeLine suggests nearby donors with a compatible
blood type, notifies them, and lets everyone follow the request by its unique reference number.

ITE-18 Application Development · Final Project · Caraga State University

> LifeLine only suggests **potential** donors. Final compatibility and eligibility are confirmed by healthcare
> professionals.

## Contents

1. [Features](#1-features)
2. [Technology stack](#2-technology-stack)
3. [Install and run on Windows](#3-install-and-run-on-windows)
4. [Demo accounts](#4-demo-accounts)
5. [Application pages](#5-application-pages)
6. [REST API](#6-rest-api)
7. [Emerging technologies](#7-emerging-technologies)
8. [Database](#8-database)
9. [Testing](#9-testing)
10. [Putting it online](#10-putting-it-online)
11. [Troubleshooting](#11-troubleshooting)
12. [Project folders](#12-project-folders)
13. [Group members and responsibilities](#13-group-members-and-responsibilities)
14. [Demonstration checklist](#14-demonstration-checklist)
15. [Known limitations](#15-known-limitations)

## 1. Features

| Area | What it does |
|---|---|
| Accounts | Register as donor or requester in 3 steps, log in with email or mobile number, email verification, password reset |
| Donors | Blood type, availability switch, approximate location (about 1 km), donation history; an admin verifies every new donor |
| Emergency requests | Blood type, units, urgency, hospital; each request gets a unique reference number (`LL-YYMMDD-XXXXXX`) and a status history |
| Donor matching | Rule-based: compatible blood type, available, verified, no donation in the last 3 months, nearest first (donors who share no location are still matched, after the nearby ones) |
| Donor answers | Donors accept or decline; contact details are shared only after a donor accepts; a donor can withdraw before donating |
| Donor profile | The requester sees an accepted donor's profile and records the donation |
| Donor location | Optional: donors share an approximate location (about 1 km) from their device or the nearest hospital; it only puts them ahead of donors farther away or without a location. The map screens were removed on the instructor's request (DEC-023) |
| Notifications | In-app alerts with an unread badge that updates by itself, plus email (can be turned off) |
| Request tracking | Anyone can track a request by reference number without logging in (no patient details shown) |
| Admin | Dashboard with charts, donor verification and suspension, users, requests (status override with reason), hospitals, reports with CSV export |
| Mobile app feel | Mobile-first design, installable on a phone (PWA), offline page, LifeLine error pages |

## 2. Technology stack

| Layer | Technology |
|---|---|
| Pages | **HTML** (Laravel Blade templates) |
| Design | **CSS** — Bootstrap 5.3 and `lifeline/public/css/lifeline.css` |
| Browser scripts | Plain **JavaScript** (no framework, no build step) — `lifeline/public/js/` |
| Backend | **PHP 8.2**, **Laravel 12** |
| Database | **MySQL / MariaDB** (XAMPP) |
| API | REST JSON API under `/api/v1` |
| Location | Browser Geolocation API (no map) |
| Charts | Chart.js 4.4.1 |
| Email | SMTP (Laravel Mail) |
| Tests | PHPUnit 11 |

Bootstrap and Chart.js are saved inside the project (`lifeline/public/vendor/`), so they work without a
CDN. Everything used is free; no paid API keys.

## 3. Install and run on Windows

### What to install

- **XAMPP** with PHP 8.2 (includes Apache, MariaDB/MySQL and phpMyAdmin)
- **Composer** (PHP package manager)
- **Git**

### Steps

1. Get the code:

   ```bash
   git clone https://github.com/KentQuizada/LifeLine.git
   cd LifeLine/lifeline
   ```

2. Install the PHP packages:

   ```bash
   composer install
   ```

3. Create the settings file and the app key:

   ```bash
   copy .env.example .env
   php artisan key:generate
   ```

4. Start **MySQL** in the XAMPP Control Panel. In phpMyAdmin (http://localhost/phpmyadmin) create two empty
   databases: `lifeline` (the app) and `lifeline_test` (the automated tests).

5. In `.env`, set the database login. With XAMPP's default account:

   ```
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. Create the tables and the sample data:

   ```bash
   php artisan migrate:fresh --seed
   ```

7. Run LifeLine:

   ```bash
   php artisan serve
   ```

   Open http://127.0.0.1:8000. For the phone design, press F12 → device toolbar (about 375 px wide).

Emails are written to `lifeline/storage/logs/laravel.log` (`MAIL_MAILER=log`). To send real emails, put SMTP
details (for example a Gmail App Password) in `.env`.

## 4. Demo accounts

Created by `php artisan migrate:fresh --seed`. Every account uses the password `password` on a laptop
(online the password is set with `SEED_PASSWORD`).

| Account | Email | Role |
|---|---|---|
| Demo Requester | requester@lifeline.test | requester |
| Demo Donor | donor@lifeline.test | verified O- donor near Sample General Hospital |
| Pending Donor | pending@lifeline.test | donor waiting for verification |
| LifeLine Admin | admin@lifeline.test | administrator |

The seeder also adds 30 sample donors, 5 sample requests and 3 sample hospitals (clearly marked "Sample").

## 5. Application pages

| Page | Address | Who |
|---|---|---|
| Welcome / onboarding | `/` | everyone |
| Register (3 steps), Log in, Forgot password | `/register`, `/login`, `/forgot-password` | guests |
| Home dashboard | `/home` | donors, requesters |
| Emergency request (SOS) | `/requests/new` | donors, requesters |
| My requests, request page, donor profile | `/requests`, `/requests/{id}`, `/requests/{id}/donors/{match}` | the requester |
| Track a request | `/track` | everyone, no login |
| Alerts | `/alerts` | donors, requesters |
| Requests for you | `/matches` | donors |
| Donation history | `/donations` | donors |
| Profile, edit profile | `/profile`, `/profile/edit` | donors, requesters |
| Admin dashboard, donors, users, requests, hospitals, reports | `/admin/...` | admin |

## 6. REST API

Base address: `/api/v1`. Every answer is JSON. Signed-in endpoints use the normal login session; writes send
the CSRF token in the `X-CSRF-TOKEN` header.

| Method | Endpoint | Purpose | Login |
|---|---|---|---|
| GET | `/api/v1/requests/track/{reference}` | Track a request by reference number | no |
| GET | `/api/v1/requests/{id}/matches` | Potential donors for my request (contact only after acceptance) | yes |
| GET | `/api/v1/notifications` | My alerts (Today / Earlier) | yes |
| GET | `/api/v1/notifications/unread-count` | Number for the Alerts badge | yes |
| POST | `/api/v1/notifications/read` | Mark all my alerts as read | yes |
| GET | `/api/v1/admin/stats` | Numbers for the admin charts | admin |

Code: `lifeline/routes/api.php` and `lifeline/app/Http/Controllers/Api/`. Ready-made requests to try them:
`docs/testing/api-tests.http`.

## 7. Emerging technologies

| Technology | Purpose in LifeLine |
|---|---|
| Geolocation (GPS) | The donor's approximate location for distance-based matching |
| Rule-based donor matching | Blood type compatibility, distance (Haversine), availability and the 3-month donation interval |
| Real-time style notifications | In-app alerts, email, self-updating unread badge |
| Data visualization | Admin charts (Chart.js) and CSV reports |
| Progressive Web App | Installable on a phone, offline page (service worker) |
| REST API | The pages load their data from `/api/v1` |

## 8. Database

MySQL/MariaDB with primary and foreign keys, UNIQUE constraints and normalized tables: `users`, `administrators`,
`donors`, `hospitals`, `blood_requests`, `request_history`, `donor_matches`, `donations`, `notifications`.

- ERD and table details: `docs/database/erd.md`
- SQL script of the schema: `docs/database/lifeline_schema.sql`
- Example CRUD queries: `docs/database/crud_queries.sql`

**Duplicate reference numbers:** before saving a request, LifeLine checks with SQL whether the reference number
is already recorded, and the `reference_number` column has a UNIQUE constraint, so the database itself refuses a
duplicate. If two requests collide at the same moment, a new number is generated.

## 9. Testing

```bash
cd lifeline
php vendor/bin/phpunit --testdox
```

466 automated tests (feature and unit) cover registration and login, validation, requests, duplicate reference
numbers, matching, notifications, the map, the API, the admin area and the full demo script.

| File | What it holds |
|---|---|
| `docs/testing/test-cases.md` | Test case table (ID, steps, expected, actual, pass/fail) for all nine required categories |
| `docs/testing/automated-test-results.txt` | Latest full test run |
| `docs/testing/api-tests.http` | API test collection |
| `docs/testing/sus-questionnaire.md` | Usability questionnaire (SUS) |
| `docs/testing/bug-log.md` | Bugs found and how they were fixed |
| `docs/testing/demo-script.md` | Step-by-step demo |

## 10. Putting it online

See `docs/deployment.md` (Docker + Railway + MySQL). In short: push this repository to GitHub, create a Railway
project from it with Root Directory `lifeline`, add a MySQL database, paste the settings from
`lifeline/.env.production.example`, and generate a domain.

## 11. Troubleshooting

| Problem | Fix |
|---|---|
| `SQLSTATE[HY000] [2002]` / connection refused | Start MySQL in the XAMPP Control Panel |
| `Unknown database 'lifeline'` | Create the `lifeline` (and `lifeline_test`) databases in phpMyAdmin |
| `Access denied for user` | Check `DB_USERNAME` / `DB_PASSWORD` in `.env` (XAMPP: `root`, empty password) |
| "No application encryption key" | Run `php artisan key:generate` |
| Page shows "Page expired" | The form was open too long; reload the page and try again |
| Map area is blank | The map pictures need internet; the donor and hospital lists still work |
| Cannot log in as a demo account | Run `php artisan migrate:fresh --seed` again (this resets all data) |
| Tests hang or fail to connect | Start MySQL and create the `lifeline_test` database |

## 12. Project folders

```
README.md                  this file
lifeline/                  the Laravel application
  app/                     controllers, models, rules (PHP)
  resources/views/         pages (HTML / Blade)
  public/css, public/js    CSS and JavaScript
  routes/web.php, api.php  page addresses and the REST API
  database/                migrations (tables) and seeders (sample data)
  tests/                   automated tests
docs/
  database/                ERD, schema SQL, CRUD queries
  testing/                 test cases, results, bug log, demo script
  design/                  the team's UI design (screens A1-E1)
  deployment.md            how to put LifeLine online
  architecture.md          how the parts fit together
```

## 13. Group members and responsibilities

| Member | Responsibility |
|---|---|
| Kent Quizada | _(fill in)_ |
| _(name)_ | _(fill in)_ |
| _(name)_ | _(fill in)_ |

## 14. Demonstration checklist

- [ ] MySQL started, `php artisan migrate:fresh --seed` run, `php artisan serve` running
- [ ] Requester sends an emergency request and gets a reference number
- [ ] The request is tracked by reference number without logging in
- [ ] Donor gets the alert, accepts, and the requester sees the donor's contact
- [ ] Requester opens the donor profile and records the donation, then marks the request fulfilled
- [ ] Admin dashboard: charts, verify a pending donor, reports and CSV export
- [ ] Duplicate reference number check explained (UNIQUE constraint + SQL check)
- [ ] `php vendor/bin/phpunit` passes

The full script with what to say is in `docs/testing/demo-script.md`.

## 15. Known limitations

- Sample hospitals are fictional until real Butuan hospitals and blood banks are added (Admin → Hospitals).
- Map pictures need internet (OpenStreetMap).
- No SMS or phone push notifications (paid services); alerts are in-app and email.
- No government ID upload; an administrator verifies each donor instead.
- Emails are only sent when SMTP details are set.
