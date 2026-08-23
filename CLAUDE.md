# Shared UI Package

This package contains shared Blade layouts/components, plus cross-app backend
infrastructure (currently: hard-abend error tracking) used across all Trigonon apps.

## What lives here
- `resources/views/layouts/` — app, guest, navigation, public layouts
- `resources/views/components/` — all shared Blade components
- `resources/css/app.css` — base styles, FontAwesome, table utilities
- `tailwind.config.js` — shared color palette and fonts (no imports — apps handle those)
- `src/ErrorTracking/` — captures uncaught exceptions to an `error_logs` table
  and emails an alert. `HardAbendRecorder::register($exceptions)` is called
  from each app's `bootstrap/app.php` inside `withExceptions()`. Config:
  `config/shared-ui.php` (`error_alert_email`, overridable per app via
  `ERROR_ALERT_EMAIL`). Migration auto-loads via `loadMigrationsFrom` — no
  publish step needed, just `php artisan migrate` in the consuming app.

## Rules
- No app-*specific* logic here — logic must be generic enough to apply to every
  consuming app (not "how account.thetrigonon.com handles X")
- No imports in tailwind.config.js (apps handle their own node_modules)
- Changes here affect ALL apps — test in account/ after any change
- After view/CSS changes, apps may need: php artisan view:clear
- After backend changes (migrations, config), apps need: composer update
  trigonon/shared-ui && php artisan migrate