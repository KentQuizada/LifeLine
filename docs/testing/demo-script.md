# LifeLine demo script

For the hands-on evaluation. About 10 minutes. The same steps run automatically in
`lifeline/tests/Feature/DemoScriptTest.php`, so a passing test suite means this script has no server errors.

## Before the demo

1. Start **MySQL** in the XAMPP Control Panel.
2. Reset the database to the demo data (this deletes everything in the `lifeline` database):
   ```
   cd lifeline
   php artisan migrate:fresh --seed
   ```
3. Start the app: `php artisan serve` and open http://127.0.0.1:8000.
4. The map tiles need internet. Without internet the map area is blank, but the donor and hospital lists still work.
5. Emails are written to `lifeline/storage/logs/laravel.log` (`MAIL_MAILER=log`). To show real emails, set the
   team's SMTP or Mailtrap details in `lifeline/.env`.

Every demo account uses the password `password`.

| Account | Email | Role |
|---|---|---|
| Demo Requester | requester@lifeline.test | requester |
| Demo Donor | donor@lifeline.test | verified O- donor near Sample General Hospital |
| Pending Donor | pending@lifeline.test | donor waiting for verification |
| LifeLine Admin | admin@lifeline.test | administrator |

Open two browser windows (one normal, one private) so the requester and the donor can stay logged in at the same time.
Use the browser's phone view (F12 → device toolbar, 375 px) to show the mobile design.

## 1. Requester asks for blood (window A)

1. Log in as **requester@lifeline.test**. Show the Home screen and the bottom navigation.
2. **Map** tab: available donors appear as approximate red circles (about 1 km) and in a list with rounded distances.
   Filter by **O-**. Press **Near me** and deny the permission: the map falls back to Butuan City. Tap the map to search
   from another point. Point out that no names or exact homes are shown.
3. **SOS** tab: blood type **AB+**, 2 units, **Critical**, hospital **Sample General Hospital**, patient name. Submit.
4. The Request sent screen shows the **reference number** (format `LL-YYMMDD-XXXXXX`). Copy it.
   The page lists the potential donors (waiting for reply). Read the medical disclaimer.

## 2. Track without logging in

1. In any window, open **/track** and paste the reference number: status and history appear, with no patient details.
2. Try a wrong reference (`LL-000000-AAAAAA`) and a malformed one (`hello`) to show the error messages.

## 3. Donor answers (window B)

1. Log in as **donor@lifeline.test**. The **Alerts** tab has a red badge: "Critical request: AB+ blood needed".
2. Open the alert → **Requests for you** → **Accept**. The requester's name and mobile number are now visible to the donor.
3. Back in Alerts, press **Mark all as read**: the badge disappears.
4. **Profile → Edit profile**: show the location picker (Use my current location / tap the map), the location sharing switch,
   and the email notifications switch.

## 4. Requester sees the match (window A)

1. Refresh the request page: status **Donor found**, and the donor's name, mobile number, Call and SMS buttons appear.
2. The Alerts tab shows "Donor match found!".
3. Press **Mark fulfilled**.

## 5. Administrator (window B, log out first)

1. Log in as **admin@lifeline.test**. The **Dashboard** shows number cards and three charts.
2. **Verify donors**: verify **Pending Donor**.
3. **Requests**: search by the reference number from step 1, open a different request that is still "Looking for donors",
   and change its status to **Expired** with a reason. The status history shows the admin's name and reason, and the
   requester gets an alert.
4. **Hospitals**: add a hospital, edit it, and show that a hospital with requests cannot be deleted.
5. **Reports**: choose a date range, download the requests CSV and open it in Excel.

## If something goes wrong

- "SQLSTATE[HY000] [2002]": MySQL is not running. Start it in XAMPP.
- Blank map area: no internet for the map tiles. The lists below the map still work.
- Login fails for a demo account: run `php artisan migrate:fresh --seed` again.
