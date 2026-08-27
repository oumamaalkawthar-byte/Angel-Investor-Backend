# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

**Angel Investor Backend** — a Laravel 12 + Filament 3 JSON API + admin panel for the Angel Investor site's three forms (Apply as Startup, Join as Investor, Contact). It replaces the framework-free PHP + SQLite backend that previously lived inside the Astro frontend repo (`public/api/submit-*.php`), because the account owner wanted a real Laravel backend (the same stack as the sibling `faithfuture` project on the same cPanel account) rather than plain PHP — but **deliberately with none of `faithfuture`'s event/ticketing machinery**. This app only ever needs three form-submission tables, three Filament resources, one Google Sheets webhook, and email notifications.

The Astro frontend (`C:\Users\USER\Desktop\Angel-Investor`, deployed separately as a static site) stays exactly as it is — same pages, same design, same client-side form UX. Only the `fetch()` target for the three forms changes, from a same-origin `/api/submit-*.php` to this app's own domain's `/api/submit-*` (a different origin, hence the CORS config below).

## Stack

- **Laravel 12**, **Filament 3.3**, **PHP 8.2+** — matches `faithfuture` exactly, since that's the proven, already-deployed combination on this same hosting account.
- **MySQL** (not SQLite) — a deliberate choice for this project, unlike the old plain-PHP backend's SQLite file.
- No queue worker assumed to be running in production (same constraint as `faithfuture` likely faces on shared cPanel hosting) — `QUEUE_CONNECTION=database` is set, but nothing here currently dispatches a real queued job; mail sending uses `app()->terminating()` (deferred until after the response, not queued) exactly like `faithfuture`'s `FormController::dispatchMails()`.

## Important: this machine has no local PHP or Composer

Neither `php` nor `composer` are installed on the Windows development machine this was built on (confirmed via `php --version` / `composer --version`, both "not recognized"). Every file in this repository was written by hand, matching Laravel/Filament conventions and mirroring `faithfuture`'s own actual working files as a reference — **none of it has been executed locally**. The very first real test of `composer install`, migrations, and the Filament panel booting successfully will happen on whatever server actually has PHP (the eventual hosting target, or any other machine with PHP available). Review PHP syntax carefully before trusting it; this is the same situation `faithfuture` itself was built under for its own forms backend (see that project's own CLAUDE.md, "Local verification limits" note).

## Architecture

### Forms

`app/Http/Controllers/FormController.php` has three methods — `contact()`, `investor()`, `startup()` — registered in `routes/api.php` under `throttle:5,10` (5 submissions per 10 minutes per IP, same limit the old SQLite-based rate limiter enforced). Each follows the same pattern as `faithfuture`'s `FormController`:
1. Honeypot check (`hp_website` field — pretend success if filled, don't reveal the bot was caught).
2. Validate with Laravel's own validator (field names/limits match the Astro frontend's `name=` attributes and the old PHP backend's limits exactly — see `public/api/submit-*.php` in the Astro repo for the source of truth these were ported from).
3. Persist via Eloquent (`ContactSubmission`, `InvestorApplication`, `StartupApplication`).
4. For investor/startup: build a reference number (`AI-INV-2026-0001` / `AI-2026-0001`) from the real auto-increment `id` **after** the insert, not a pre-computed guess — avoids a race condition two concurrent submissions could hit.
5. Push a row to Google Sheets via `GoogleSheetsService::push($tab, $row)` — fire-and-forget, mirrors `faithfuture`'s service almost exactly (same `{tab, data}` → `{success}` contract), except there's only ever one webhook URL here (one shared spreadsheet, three tabs: `Contact Forms` / `Join As Investor` / `Apply As Startup` — same tab names the old PHP backend already settled on after a real tab-name mismatch bug on that project).
6. Send admin + submitter emails via `dispatchMails()` → `App\Mail\SiteMail`, deferred with `app()->terminating()` — identical mechanism to `faithfuture`.
7. Return `{success, message?, reference?}` JSON — **this exact shape is a contract with the Astro frontend's `form-pages.ts`**, which expects it verbatim. Don't change the response shape without updating that file too.

The startup form's pitch deck: validated as a real PDF (`mimes:pdf|max:15360`), stored on the `public` disk under `pitch-decks/`, filename never trusted from the client. Served (and downloadable from the Filament admin) via the custom `/storage/{path}` route in `routes/web.php`, not a symlink — see "No symlink" below.

### No symlink (same hosting constraint as `faithfuture`)

`routes/web.php` defines `/storage/{path}` resolving against `storage/app/public`, with a `realpath` containment check against path traversal — this exists because the target hosting (same cPanel account/host family as `faithfuture` and the Astro Angel Investor site) doesn't reliably support `public/storage` symlinks. **Don't run `artisan storage:link`** and don't add it back to `composer.json`'s `setup` script — `config/filesystems.php`'s `public` disk URL still resolves to `/storage/...`, which this custom route serves directly instead.

### CORS

The Astro frontend is a **different origin** than this API (its own domain/subdomain), unlike `faithfuture` which is same-origin with its own frontend and has no `config/cors.php` at all. `config/cors.php` here allows `paths: ['api/*']` from `CORS_ALLOWED_ORIGINS` (comma-separated env var, defaults to `https://angelinvestor.pk`) — update that env var (and the Astro frontend's own fetch-target env/constant) together if the frontend's domain ever changes.

### Admin panel

`app/Providers/Filament/AdminPanelProvider.php` — single navigation group ("Submissions"), three resources (`ContactSubmissionResource`, `InvestorApplicationResource`, `StartupApplicationResource`), each with a disabled read-only detail form, a `status` + `admin_notes` follow-up section, table filters by status, and a CSV export action — same pattern as `faithfuture`'s `ContactSubmissionResource`, just without that project's two-role (`Super Admin` / `Social Media`) RBAC, since this is a single-admin backend. `User` has no `role` column and `canAccessPanel()` always returns `true` for any authenticated user — anyone who can log in has full access.

**No Filament `UserResource`** — there's no in-panel way to create additional admin accounts. The only account is the one `database/seeders/DatabaseSeeder.php` creates (`admin@angelinvestor.pk` / `change-me-immediately`) via `php artisan db:seed`. Change that password immediately after the first login (Filament's `->passwordReset()` is enabled on the panel, so a real SMTP mailer needs to be configured for that to actually deliver a reset email — until then, change it via `php artisan tinker` directly: `User::first()->update(['password' => bcrypt('...')])`).

## Not done yet — this is code that has never run

- **Never deployed anywhere.** No server has run `composer install`, no migration has ever executed against a real MySQL database, the Filament panel has never actually booted. All of the above is inference from Laravel/Filament conventions and faithfuture's working equivalents, not verified execution.
- **No hosting/subdomain decided or set up.** Needs its own subdomain (e.g. `api.angelinvestor.pk`) on the `akuedu` cPanel account, its own MySQL database created in cPanel, its own `.env` with real `DB_*`/`MAIL_*`/`GOOGLE_SHEETS_WEBHOOK_URL`/`CORS_ALLOWED_ORIGINS` values, and its own deploy mechanism (likely the same cPanel Git Version Control pattern the other two projects on this account use, pointing `composer install --no-dev && php artisan migrate --force` at deploy time rather than shipping a prebuilt `dist/` the way the static Astro site does).
- **The Astro frontend hasn't been updated to call this API yet.** `src/scripts/form-pages.ts` in the Angel-Investor repo still posts to `/api/submit-contact.php` etc. (the old same-origin PHP endpoints). Once this backend has a real URL, that file's three `submitForm(..., '/api/submit-*.php', ...)` calls need to point at `https://<this-backend-domain>/api/submit-contact` etc. instead — and the old `public/api/submit-*.php` + `_forms_lib.php` files (and the SQLite data directory, and the Decap/forms secrets file pattern they use) can be retired once this is confirmed working, though there's no urgency to delete them immediately.
- **Google Apps Script webhook not deployed for this app.** The Astro repo's existing `scripts/google-apps-script-forms-sync.gs` (checked into that repo) is the right contract to reuce — either point `GOOGLE_SHEETS_WEBHOOK_URL` here at the *same* deployed Apps Script URL the old PHP backend already uses (simplest — same spreadsheet, same tabs, no new Apps Script needed), or deploy a fresh one if the account owner wants a separate spreadsheet for some reason. Reusing the existing one is the more likely intent, since the tab names (`Contact Forms` / `Join As Investor` / `Apply As Startup`) were deliberately kept identical to what's already live.
- **No automated tests.** `tests/Feature` and `tests/Unit` exist as empty directories only.

## Resuming this project

```bash
composer install
cp .env.example .env
php artisan key:generate
# create the MySQL database first, then:
php artisan migrate
php artisan db:seed
php artisan serve
```

Git identity for this account (`oumamaalkawthar-byte`) is the same across this user's other repos — see `faithfuture`'s or `Angel-Investor`'s own CLAUDE.md for the `gh` auth setup notes if pushing fails.
