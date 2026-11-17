# FrontendUI

## Purpose
Mobile-first, installable user interface implementing the team's LifeLine design.

## Status
`active` — M1.3 done 2026-11-14: app shell (guest and signed-in layouts, bottom nav), A1–A4 onboarding, B1–B3 registration, B4 login, verify-email, forgot/reset password and the admin verification queue. M2.1 done 2026-11-14: C1 home dashboard and E1 my profile restyled to the team design (red header, green availability switch, Account menu), edit profile, donation history; Profile tab live. Checked in the browser at 360 px and laptop width. M2.2 done 2026-11-14: D1 SOS form, D2 request sent, my requests, request page and the track page; raised red SOS centre tab and Emergency request / My requests buttons on home. Checked at 375 px and 1280 px. M2.4–M2.6 done 2026-11-17: C2 donor map, C3 Alerts tab with unread badge (all five bottom-nav tabs now live), location picker and email switch in Edit profile, desktop admin area with its own menu, PWA manifest and icons.

## Responsibilities
- Screens from the design: A1 splash; A2-A4 onboarding; B1-B3 registration; B4 login; C1 home dashboard; C2 live donor map; C3 notifications; D1 SOS request form; D2 request sent; D3 donor profile; E1 my profile
- Bottom navigation: Home, Map, SOS, Alerts, Profile
- Screens to add (not in design): track request, my requests, admin dashboard, error pages
- PWA manifest for installability; frontend validation

## Dependencies
- [[RestApi]]
- [[Authentication]]

## Runtime References
- Layouts and shared parts: `lifeline/resources/views/layouts/`, `lifeline/resources/views/partials/` (bottom nav: `partials/bottom-nav.blade.php`)
- Screens: `lifeline/resources/views/` (`onboarding`, `auth/`, `home`, `profile/`, `donations/`, `requests/`, `admin/`)
- Page scripts: `lifeline/public/js/` (`track.js`, `matches.js`, `map.js`, `location-picker.js`, `alerts.js`, `admin-dashboard.js`; plain JS, `textContent` only). Pages add assets with `@push('head')` / `@push('scripts')`.
- PWA: `lifeline/public/manifest.webmanifest`, icons in `lifeline/public/icons/`
- Brand CSS: `lifeline/public/css/lifeline.css`; vendored Bootstrap 5.3.3 and Bootstrap Icons 1.11.3: `lifeline/public/vendor/`

## Invariants
- Every button and link works; no placeholder screens
- Layout works from phone to laptop widths

## Notes
- Reusable pieces from M2.1 (`lifeline/public/css/lifeline.css`): `.ll-hero` red header with `.ll-hero-stat` tiles, `.ll-availability` card with `.ll-switch` (a real submit button, no JavaScript), `.ll-section-label` and `.ll-menu-icon` for menu lists. From M2.2: `.ll-sos` raised centre tab, `.ll-reference` monospace reference number, status badge partial `partials/request-status.blade.php`.
- Brand: red primary, white cards, green for available/success.
- The design image should be saved in the repo for reference, in `docs/design/` (still missing as of 2026-11-14).
- Front-end assets come from the app itself, not a CDN (see [[30_DECISIONS]] DEC-013).
- Bottom-nav contract: each tab appears only once its route exists (`Route::has`), so there are no placeholder tabs. Route names M2 must use: `home`, `map` (M2.4), `requests.create` (SOS, M2.2), `alerts.index` (M2.5), `profile.show` (M2.1). Admins get no bottom nav.

- Layout sections: `title`, `content`, and in the signed-in layout `container_class` (default `ll-narrow`, empty for full width). Flash messages: `session('status')` (success) and `session('error')` (danger); field errors inline with `@error`.

## Future Changes
- No service worker (offline pages); the manifest makes LifeLine installable from the browser menu.
- The design image is still not in `docs/design/` (team to save it as `docs/design/lifeline-design.png`).
- Done in M3.2: hero small text 4.6:1+, switch 4.7:1 (off) / 5.1:1 (on), section labels 5.6:1; `public/js/lifeline.js` (single-submit forms) loads on every page.
- Keyboard-only walkthrough by a team member still to do (MAN-07).
- Design parts still to come: Donors near you and map (M2.4), Notifications switch (M2.5).
