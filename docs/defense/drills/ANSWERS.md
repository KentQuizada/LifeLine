# Drill answers (read only after trying)

| Drill | What you see | The clue | The bug and the fix |
|---|---|---|---|
| 01 | Error page: **syntax error, unexpected token "try"**, `BloodRequestController.php:40` | File and line on the error page, **then look just above** | Line 40 is where PHP noticed; the mistake is on line 36: the `;` after `submit($request->user(), $details)` is missing. Add it. |
| 02 | **Page expired, Error 419** after "Log in" (the message blames time, but the form was just opened) | 419 = CSRF token missing | `resources/views/auth/login.blade.php`: `@csrf` was removed from the form. Add `@csrf` on the line after `<form ...>`. |
| 03 | Home shows an error: **Route [requests.create] not defined** | Search `requests.create` (Ctrl+Shift+F) | `routes/web.php` line 73: the route is named `request.create`. Rename it back to `requests.create`. |
| 04 | Tracking says **"No request found"** for a real reference | F12 → Network: `/api/v1/request/track/...` is **404** | `resources/views/requests/track.blade.php` line 9: `data-api` points to `/api/v1/request/track`. It must be `/api/v1/requests/track` (see `routes/api.php`). |
| 05 | Error: **Undefined variable $bloodRequest** in `requests/show.blade.php` | The view needs `$bloodRequest`; who sends it? | `BloodRequestController::show()` line 56 passes `'request' => $record`. Change the key back to `'bloodRequest'`. |
| 06 | No error, but the **notes** you typed are not on the request page | The form sends notes; the database row has none | `app/Models/BloodRequest.php`: `'notes'` was removed from `$fillable`, so Laravel ignores it. Add `'notes',` back. |
| 07 | Every page loses its design (plain white, no colors) | F12 → Network: `lifline.css` is **404** | `resources/views/partials/head.blade.php` line 14: `css/lifline.css` must be `css/lifeline.css`. |
| 08 | Clicking the eye does nothing | F12 → Console: **ReferenceError: feild is not defined, lifeline.js:51** | `public/js/lifeline.js` line 51: `feild` must be `field`. |
| 09 | An O+ request suggests only O+ donors: the **O- demo donor is missing** although O- can give to O+ | O- can give to everyone: check the compatibility table | `app/Models/BloodRequest.php` `COMPATIBLE_DONORS`: `'O+' => ['O+']` must be `['O+', 'O-']`. |
| 10 | Cancel says **"This request can no longer be changed."** | Search that message → `updateStatus()` → `changeStatus()` → `TRANSITIONS` | `app/Models/BloodRequest.php` `TRANSITIONS['open']` lost `'cancelled'`. Add it back. |
| 11 | New requests suggest the wrong donors: the available demo donor is missing, donors who switched **Available off** are suggested | Matching starts from `Donor::scopeEligibleFor()` → `scopeSearchable()` | `app/Models/Donor.php` line 70: `where('is_available', false)` must be `true`. |
| 12 | The **reference number is blank** on the request page, no error | Search `Reference number` in the views | `resources/views/requests/show.blade.php` line 25: `reference_numbr` must be `reference_number` (a misspelled column gives empty text, not an error). |

What the instructor sees when you explain: say the symptom, where you looked (error page / Console / Network /
log / search), the file and line, and why the fix works.
