# Shared UI Package

This package contains shared Blade layouts and components used across all Trigonon apps.

## What lives here
- `resources/views/layouts/` — app, guest, navigation, public layouts
- `resources/views/components/` — all shared Blade components
- `resources/css/app.css` — base styles, FontAwesome, table utilities
- `tailwind.config.js` — shared color palette and fonts (no imports — apps handle those)

## Rules
- No app-specific logic here
- No imports in tailwind.config.js (apps handle their own node_modules)
- Changes here affect ALL apps — test in account/ after any change
- After changes, apps may need: php artisan view:clear