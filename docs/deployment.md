# Putting LifeLine online (system link due Nov 20, 11:59 PM)

LifeLine runs online as one **Docker** container (`lifeline/Dockerfile`: Apache + PHP 8.2) plus a **MySQL**
database from the hosting site. On every start the container caches settings, creates or updates the tables
(`php artisan migrate`) and adds the demo data only if the database is empty (`php artisan lifeline:seed-if-empty`).

The steps below use **Railway** (railway.com). Prices change, so check its pricing page first. In late 2026 it
offers a short trial credit, then about US$5 a month. Any host that can run a Dockerfile and give you a MySQL
database works the same way.

## What you need

- A **GitHub** account (the code goes there; Railway reads it from there).
- A **Railway** account (sign up with GitHub).
- Optional, for sign-up emails: a **Gmail** account with 2-Step Verification and an **App Password**.

## 1. Put the code on GitHub

1. On github.com, create a new **private** repository, for example `lifeline`, without a README.
2. In a terminal in `D:\ITE-18_LifeLine`:

   ```bash
   git remote add origin https://github.com/YOUR-USERNAME/lifeline.git
   git push -u origin main
   ```

## 2. Create the app on Railway

1. railway.com → **New Project** → **Deploy from GitHub repo** → choose `lifeline`.
2. Open the new service → **Settings** → **Source** → **Root Directory**: `lifeline`
   (the Laravel app is in that folder). Railway then finds the `Dockerfile` by itself.
3. In the same project: **New** → **Database** → **MySQL**.

## 3. Settings (Variables)

Open the LifeLine service → **Variables** → **Raw Editor** and paste this, then fill the empty values.
The `${{MySQL.…}}` parts are filled in by Railway from the MySQL service.

```
APP_NAME=LifeLine
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_URL=
LOG_CHANNEL=stderr
LOG_LEVEL=warning
DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}
PORT=8000
SESSION_DRIVER=file
SESSION_SECURE_COOKIE=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
SEED_PASSWORD=
MAIL_MAILER=log
MAIL_FROM_ADDRESS=no-reply@lifeline.test
MAIL_FROM_NAME=LifeLine
AUTO_VERIFY_EMAIL=true
```

- **DB_URL**: add it with **New Variable → Add Reference → MySQL → MYSQL_URL** so Railway links the two
  services. (Separate `DB_HOST`/`DB_PORT`/... references came out empty in our project.)
- **PORT**: must equal the port shown under the domain in **Settings → Networking** (ours is 8000).
- **APP_KEY**: run `php artisan key:generate --show` in `D:\ITE-18_LifeLine\lifeline` and paste the whole
  `base64:...` value.
- **SEED_PASSWORD**: choose a password for the demo accounts online (requester@, donor@, pending@ and
  admin@lifeline.test). Do not use "password": the link is public. Give it only to your instructor.
- **APP_URL**: fill in after step 4.

The full list with comments is in `lifeline/.env.production.example`.

## 4. Get the link

1. LifeLine service → **Settings** → **Networking** → **Generate Domain**. Railway shows a link such as
   `https://lifeline-production.up.railway.app`.
2. Put that link in the **APP_URL** variable. Railway redeploys by itself.
3. Wait for the deploy to finish (**Deployments** tab, green). The first start also adds the demo data.

## 5. Check it

- `https://YOUR-LINK/up` shows a green "Application up" page.
- Log in as `admin@lifeline.test` with your SEED_PASSWORD: the dashboard shows the charts.
- Run the demo script (`docs/testing/demo-script.md`) once on the link: requester, donor, admin.
- Open the link on a phone. The browser menu offers **Install app / Add to Home screen**.

## Emails online

Railway blocks outgoing mail (SMTP) on its Trial and Hobby plans, so with `MAIL_MAILER=log` no email leaves
the server. `AUTO_VERIFY_EMAIL=true` (DEC-024) makes a brand-new sign-up count as verified at once, so anyone can
register and use the system. Password-reset emails still do not arrive online; the admin can help instead.

### Real emails on the free plan: Brevo (DEC-025)

Brevo sends email over HTTPS, which Railway allows on every plan (free: 300 emails a day).

1. Sign up at brevo.com (free plan).
2. **Senders, Domains & Dedicated IPs → Senders → Add a sender**: your Gmail address. Brevo emails you a
   link to confirm it.
3. **SMTP & API → API Keys → Generate a new API key** (name it LifeLine). Copy it once; Brevo shows it only once.
4. In Railway, LifeLine → Variables, set:

```
MAIL_MAILER=brevo
BREVO_API_KEY=the key from step 3
MAIL_FROM_ADDRESS=the Gmail you confirmed in step 2
AUTO_VERIFY_EMAIL=false
```

Deploy. New sign-ups now get the verification email, and **Resend verification email** and password reset
work. Tell testers to check their spam folder the first time.

### SMTP (Railway Pro or another host)

On a plan that allows SMTP, set `AUTO_VERIFY_EMAIL=false` and replace the MAIL lines with (Gmail App Password):

```
MAIL_MAILER=smtp
MAIL_SCHEME=smtps
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=your.gmail@gmail.com
MAIL_PASSWORD=the 16-letter app password
MAIL_FROM_ADDRESS=your.gmail@gmail.com
MAIL_FROM_NAME=LifeLine
```

## Vercel (a vercel.app link)

Vercel runs the same Docker image (`lifeline/Dockerfile`) through its Container Runtime (beta, free Hobby
plan). `lifeline/vercel.json` tells Vercel to build that Dockerfile instead of treating the folder as a Vite
site. Vercel has no MySQL, so the app uses the Railway MySQL database over its public address.

1. vercel.com → **Sign up with GitHub** (Hobby plan).
2. **Add New → Project → Import** the `LifeLine` repository.
3. **Root Directory**: `lifeline`. Framework preset: leave what Vercel picks (vercel.json decides).
4. **Environment Variables**: the same list as step 3 above, with these differences:

```
DB_URL=        (Railway → MySQL service → Variables → MYSQL_PUBLIC_URL, the whole value)
SESSION_DRIVER=cookie
```

   Leave out `PORT` (Vercel and the container both default to 80). Use the same `APP_KEY` and
   `SEED_PASSWORD` as Railway so the demo accounts keep their password. Copy the MAIL_ and BREVO_ lines too.
5. **Deploy**. When it finishes, put the vercel.app link in **APP_URL** (Settings → Environment Variables)
   and **Redeploy**.

Why `SESSION_DRIVER=cookie`: Vercel may run several copies of the container and throws away their files, so
a login saved as a file on one copy is unknown to the next. A cookie session travels with the browser.

The container stops after 5 minutes without visitors; the first visit after that takes a few seconds longer
while it starts (and runs `migrate`, which does nothing when the tables are current).

## Updating the online system

Commit and `git push`; Railway builds and restarts by itself. Data stays, because the database is a separate
service. To start over with fresh demo data, delete and re-create the MySQL service (this erases everything).

## If something goes wrong

- **Deploy fails while building**: open the build log in Railway → Deployments. Most often the Root Directory
  is not set to `lifeline`.
- **500 error / "No application encryption key"**: APP_KEY is empty or was pasted without `base64:`.
- **Database connection refused**: the DB_ variables do not point at the MySQL service; check the names in the
  `${{MySQL.…}}` references match your MySQL service's name.
- **Forms say "Page expired"**: APP_URL must be the exact https link, and SESSION_SECURE_COOKIE=true.
- **Logs**: Railway → the service → **Logs** (LOG_CHANNEL=stderr sends Laravel's errors there).

Note: the Docker image was written for this guide but not built on the team laptop (Docker is not installed
there). The production settings themselves were checked locally on 2026-11-17 (`config:cache`, `route:cache`,
`view:cache`, pages and https links).
