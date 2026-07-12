# Enaya Backend

Laravel REST API powering **Enaya (عناية)** — a clinic management system handling appointments, patients, prescriptions, and multi-role workflows for Admins, Doctors, Receptionists, and Patients.

This API is shared between the [Enaya React admin panel](https://github.com/yasserotani/enaya-react) and three Flutter mobile apps (Doctor, Receptionist, Patient).

## Tech Stack

- **Framework:** Laravel 13 (PHP 8.3+)
- **Auth:** Laravel Sanctum (token-based, 30-day expiry + refresh endpoint)
- **Authorization:** Spatie Permission (role-based checks; fine-grained permissions in progress)
- **Database:** MySQL
- **Testing:** Pest
- **API Docs:** Scribe
- **Tooling:** Vite + Tailwind (for Scribe doc theme), Laravel Pint

## Features

**Authentication**
- Signup, login, logout, token refresh, current-user lookup
- Automatic linking of app accounts to existing walk-in patient records by phone number

**Patient**
- Profile retrieval, completion, and updates
- Department/doctor lookup for booking
- Appointment booking with available-slot lookup and conflict prevention
- Appointment cancellation
- Prescription and session history

**Receptionist**
- Walk-in patient registration, search, update, soft delete/restore
- Appointment scheduling, confirmation, arrival marking, rescheduling, cancellation, no-show marking

**Doctor**
- Appointment list and details
- Appointment sessions: start, end, update, with diagnosis and notes
- Prescriptions: create and remove (active sessions only)
- Patient list and history for the doctor's own patients

**Admin**
- User management (create/update/delete doctors and receptionists)
- Patient and doctor CRUD with soft delete/restore
- Department management
- Appointment oversight across the clinic, with stats and cancellation/no-show/reschedule actions
- Dashboard endpoint with KPIs (patient/doctor counts, appointment volume, recent activity, 7-day trend)

## API Conventions

- All responses follow a standard envelope:
  ```json
  { "success": true, "data": {}, "message": null, "error": null, "errorCode": null }
  ```
- Protected routes require a Sanctum bearer token: `Authorization: Bearer <token>`
- Actor-based controller structure: `Admin/`, `Doctor/`, `Patient/`, `Reception/`
- Separate FormRequest per flow, route model binding, and database transactions for multi-table writes
- Domain-level validation errors are handled by a global `DomainException` handler and returned as `422`
- Appointment statuses: `scheduled`, `confirmed`, `arrived`, `inProgress`, `completed`, `cancelled`, `noShow`, `rescheduled`

Full endpoint reference (requests, responses, and status codes): [docs/API.md](./docs/API.md)

## Getting Started

```bash
composer install && npm install
cp .env.example .env
php artisan key:generate
```

Create a local MySQL database named `enaya_backend`, update `.env` if needed, then:

```bash
php artisan migrate
composer run dev   # starts server + queue + Vite together
```

### Useful Commands

| Command                      | What it does                        |
| ----------------------------- | ------------------------------------ |
| `composer run dev`            | Server + queue + Vite (recommended)  |
| `composer run setup`          | Full first-time setup                |
| `php artisan test --compact`  | Run Pest tests                       |
| `vendor/bin/pint --dirty`     | Format changed PHP files             |
| `php artisan scribe:generate` | Regenerate API documentation         |


## Related Repositories

- [`enaya-react`](https://github.com/yasserotani/enaya-react) — React admin dashboard
- `enaya-mobile` — Flutter apps for Doctor, Receptionist, and Patient roles

## License

This project is part of an academic graduation project and is not currently licensed for external use.
