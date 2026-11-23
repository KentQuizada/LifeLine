# Debugging defense: method, cheat sheet and practice drills

For the hands-on debugging (about Dec 7-8): 10 minutes, 3-5 problems, on our own laptops.
Nobody memorizes the code. You follow the clues. Laravel tells you more than a single big HTML file does.

## The method (4 steps)

1. **Make it happen.** Click the thing that is broken.
2. **Read the clue.** There are only 3 places to look:
   - **Red error page** in the browser: it names the **file and line**. Open that file at that line.
   - **Page looks wrong / a button does nothing**: press **F12**.
     - **Console** tab: a red error names the JS file and line (`public/js/...`).
     - **Network** tab: a red row (404, 419, 500) shows which file or address failed.
   - **No clue on screen**: open `lifeline/storage/logs/laravel.log` and read the bottom.
3. **Search, don't remember.** VS Code **Ctrl+Shift+F**: search a word you see on the page (a button label, a
   message, a route name). It takes you to the file.
4. **Fix and refresh.** If nothing changes: `php artisan optimize:clear`, then **Ctrl+F5** in the browser.

## Cheat sheet

| You see | Where to look |
|---|---|
| Error page with a file and line | That file and line: typo, wrong variable name. For **syntax error, unexpected ...** look **just above** the line: a missing `;` or `)` is reported on the next line |
| `Undefined variable $something` in a `.blade.php` | The controller that opens that page: is the variable passed in `view('...', [...])`? |
| **404 Not Found** (page) | `routes/web.php`: is the address there, spelled the same? `php artisan route:list` lists all |
| **404** in the Network tab (a CSS/JS/API file) | The address in the page or script is misspelled: compare it with the real file or `routes/api.php` |
| **419 Page Expired** | The form in the `.blade.php` file is missing `@csrf` |
| **Route [xxx] not defined** | The name in `route('...')` does not match a `->name('...')` in `routes/web.php` |
| **403 Forbidden** | `role:` in `routes/web.php`: is this user allowed on this page? |
| A form saves but one field stays empty | That field is missing from `$fillable` in the model (`app/Models/`) |
| A rule gives the wrong answer (matching, statuses) | The rule tables in `app/Models/BloodRequest.php` and `Donor::scopeEligibleFor` |
| Page has no design (plain white) | F12 → Network: the CSS file is red (404). Check the path in `partials/head.blade.php` |
| `SQLSTATE ... Connection refused` | XAMPP MySQL is not started |
| After fixing, `php artisan test` | Proves nothing else broke (458 tests) |

Where things live: pages `resources/views/`, addresses `routes/web.php` and `routes/api.php`, page logic
`app/Http/Controllers/`, rules and database `app/Models/`, design `public/css/lifeline.css`, scripts `public/js/`.

## Practice drills

12 drills in `drills/`. Each one breaks LifeLine in one small way, like the instructor might. **Do not open the
`.patch` file before you try** (it shows the answer).

Before you start: XAMPP MySQL running, then in `lifeline`: `php artisan serve`, open http://127.0.0.1:8000.
Demo logins (password `password`): requester@lifeline.test, donor@lifeline.test, admin@lifeline.test.

**Start a drill** (in PowerShell, in the `LifeLine` folder):

```powershell
git apply docs/defense/drills/drill-01.patch
```

Then use the 4 steps. Each drill's starting point is in the table below; time yourself (aim: under 3 minutes).

**Check your fix:** `git status` shows no changed files under `lifeline/` when your fix matches the original
exactly. Or run `php artisan test` in `lifeline`.

**Give up / reset** (this throws away every unsaved change under `lifeline/`):

```powershell
git restore lifeline
```

| Drill | Where to start | Tests notice? |
|---|---|---|
| 01 | Log in as requester, open **SOS / New request** | yes |
| 02 | Log out, then log in | no |
| 03 | Log in as requester: the Home page | yes |
| 04 | Log out, **Track a blood request**, enter a real reference (from My requests) | no |
| 05 | As requester, open one of **My requests** | yes |
| 06 | As requester, send a new request **with notes**, then look at the request page | yes |
| 07 | Any page | no |
| 08 | Login page: click the **eye** (show password) | no |
| 09 | As requester, send an **O+** request: is the O- demo donor (Demo Donor) suggested? | yes |
| 10 | As requester, open an open request and **Cancel** it | yes |
| 11 | As requester, send an **A+** request: is the demo donor suggested, and are the right donors listed? | yes |
| 12 | As requester, open one of **My requests**: look at the reference number | no |

"Tests notice? no" means only your eyes and F12 find it, just like in the defense.
Answers: `drills/ANSWERS.md` (read only after trying).
