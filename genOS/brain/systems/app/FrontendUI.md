# FrontendUI

## Purpose
Mobile-first, installable user interface implementing the team's LifeLine design.

## Status
`active` — M1.3 done 2026-11-14: app shell (guest and signed-in layouts, bottom nav), A1–A4 onboarding, B1–B3 registration, B4 login, verify-email, forgot/reset password and the admin verification queue. M2.1 done 2026-11-14: C1 home dashboard and E1 my profile restyled to the team design (red header, green availability switch, Account menu), edit profile, donation history; Profile tab live. Checked in the browser at 360 px and laptop width.

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
- Screens: `lifeline/resources/views/` (`onboarding`, `auth/`, `home`, `profile/`, `donations/`, `admin/`)
- Brand CSS: `lifeline/public/css/lifeline.css`; vendored Bootstrap 5.3.3 and Bootstrap Icons 1.11.3: `lifeline/public/vendor/`

## Invariants
- Every button and link works; no placeholder screens
- Layout works from phone to laptop widths

## Notes
- Reusable pieces from M2.1 (`lifeline/public/css/lifeline.css`): `.ll-hero` red header with `.ll-hero-stat` tiles, `.ll-availability` card with `.ll-switch` (a real submit button, no JavaScript), `.ll-section-label` and `.ll-menu-icon` for menu lists.
- Brand: red primary, white cards, green for available/success.
- The design image should be saved in the repo for reference, in `docs/design/` (still missing as of 2026-11-14).
- Front-end assets come from the app itself, not a CDN (see [[30_DECISIONS]] DEC-013).
- Bottom-nav contract: each tab appears only once its route exists (`Route::has`), so there are no placeholder tabs. Route names M2 must use: `home`, `map` (M2.4), `requests.create` (SOS, M2.2), `alerts.index` (M2.5), `profile.show` (M2.1). Admins get no bottom nav.

- Layout sections: `title`, `content`, and in the signed-in layout `container_class` (default `ll-narrow`, empty for full width). Flash messages: `session('status')` (success) and `session('error')` (danger); field errors inline with `@error`.

## Future Changes
- PWA manifest is not done yet.
- The design image is still not in `docs/design/` (team to save it as `docs/design/lifeline-design.png`).
- M3.2: hero small-text contrast (~3.4–4:1 on red) and the availability switch track contrast are below WCAG AA; fix in the accessibility pass.
- Design parts still to come: SOS centre tab and Emergency Request button (M2.2), Donors near you and map (M2.4), Notifications switch (M2.5).
