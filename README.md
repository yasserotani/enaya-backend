# Enaya Backend

Laravel 13 · Sanctum Auth · Pest Tests · Vite + Tailwind · Laravel Boost

## Overview

REST API backend shared between the React admin panel and Flutter mobile app (Doctor, Patient, Receptionist roles). Auth is handled via Laravel Sanctum. Real-time is polling-first, WebSockets later.

**Stack:** PHP 8.3+, Laravel 13, MySQL, Sanctum 4, Pest 4, Vite 8, Tailwind 4

## Setup

```powershell
composer install && npm install
Copy-Item .env.example .env
php artisan key:generate
```

Create a local MySQL database named `enaya_backend`, update `.env` if needed, then:

```powershell
php artisan migrate
composer run dev   # starts server + queue + Vite together
```

## Useful Commands

| Command | What it does |
|---|---|
| `composer run dev` | Server + queue + Vite (recommended) |
| `composer run setup` | Full first-time setup |
| `php artisan test --compact` | Run Pest tests |
| `vendor\bin\pint --dirty` | Format changed PHP files |

## Auth

All protected routes require a Sanctum Bearer token:
`Authorization: Bearer <token>`

Login via `POST /api/login` — returns a token for React/Flutter clients.

