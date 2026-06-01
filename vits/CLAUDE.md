# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Full dev environment (Laravel server + Vite + queue + log tail, all in one)
composer run dev

# First-time setup
composer run setup

# Run tests
composer run test
# or single test file:
./vendor/bin/pest tests/Feature/ExampleTest.php

# Code formatting (Laravel Pint)
./vendor/bin/pint

# Frontend assets
npm run dev     # watch mode
npm run build   # production build

# Database
php artisan migrate
php artisan db:seed --class=VitsSeeder

# Kizeo import
php artisan kizeo:import                          # unread records only
php artisan kizeo:import-periode 2026-01-01 2026-03-31  # date range re-import
```

## Architecture

This is a Laravel 13 / PHP 8.3 application for managing IT service clients, contracts, and field interventions — branded **VITSMiki**.

**Stack:** Laravel Breeze + Livewire/Volt (auth), Spatie Permission (RBAC), DomPDF (PDF reports), Tailwind CSS + Vite. Database is SQLite locally; production runs on Gandi hosting with the web root mapped via symlink (`htdocs → public`).

### Domain model

```
Client (1) ──── (N) Contrat (1) ──── (N) Intervention
                     │
                     └── (N) PeriodeAjustement
```

- **Client** — a company with a Kizeo client number and a VIT-S contract number.
- **Contrat** — a maintenance contract with start/end dates, period length (`duree_periode_mois`), and hours per period (`heures_par_periode`). The `getPeriodes()` method computes the list of billing periods dynamically from `date_debut` and `duree_periode_mois`.
- **Intervention** — a single service event. Types: `site` (on-site), `distance` (remote), `flash` (short remote). Flash interventions auto-renumber in groups of 3 via `Intervention::recalculerFlash()` — the 3rd flash in a group costs 20 minutes and sets `flash_consomme = true`.
- **PeriodeAjustement** — records a billing-period settlement (credit or overage) once a period ends. Dashboard alerts on unsettled past periods.

The `deductible` flag controls whether an intervention is counted against the contracted hours.

### Kizeo Forms integration

`KizeoService` (REST API v3, `https://www.kizeoforms.com/rest/v3`) pulls from two form IDs:
- `45252` — on-site interventions
- `108738` — remote/flash interventions

API key is stored in `KIZEO_API_KEY` env var, consumed via `config('vits.kizeo_api_key')`. Client name matching tolerates apostrophe variants and maintains a hardcoded aliases map for company renames (e.g., `ACTA PREVENTION` → `VANBERG Prévention`).

### Custom config

`config/vits.php` holds app-specific settings: alert thresholds (`seuil_echeance_mois`, `seuil_heures_pct`), intervention motif list, Kizeo poll frequency, and session duration. These are editable via the `/parametres` admin page at runtime (changes go to the database, not the file).

### Views

Blade views under `resources/views/` follow the standard resource layout: `clients/`, `contrats/`, `interventions/`, `pdf/`, `parametres/`. Auth views are split between `resources/views/auth/` (custom login page) and `resources/views/livewire/pages/auth/` (Breeze/Volt defaults). The PDF report template is `pdf/rapport.blade.php` and uses French locale (`isoFormat`).

### Seeders

`VitsSeeder` loads real client/contract data. `ImportWordpressSeeder` imports historical data from a legacy WordPress database.

## Règles métier
- contrat=Oui ET forfait!=Oui → déductible
- flash=Oui → déductible  
- forfait=Oui → NON déductible
- Flash : compteur 1/3→2/3→3/3, 20min débitées au 3/3 seulement, repart à 1/3 après chaque 3/3
- Tranches : site=1h, distance=20min, administrateur=durée libre (peut être négatif = crédit)

## Déploiement Gandi
- Branche production → Gandi, main → GitHub
- git push gandi production:master puis ssh git.sd3.gpaas.net 'deploy vitsmanage.com.git'
- ⚠️ Après chaque déploiement : remettre htdocs/index.php via SFTP (écrasé par Git)
- htdocs/index.php utilise des chemins absolus vers /srv/data/web/vhosts/vitsmanage.com/vits/

## TODO prioritaires
- Éplucher interventions client='-' 2023-2026 (erreurs saisie Kizeo)
- Script deploy.sh automatique
- Fix index.php écrasé au déploiement

