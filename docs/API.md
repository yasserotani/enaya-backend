# Enaya Frontend API Guide

This guide is for the React admin panel and Flutter mobile app. All endpoints are prefixed with `/api`.

## Conventions

Protected endpoints require a Sanctum bearer token:

```http
Authorization: Bearer <token>
Accept: application/json
```

Successful responses generally use this shape:

```json
{
    "success": true,
    "data": {},
    "message": "Optional message",
    "error": null,
    "errorCode": null
}
```

Validation errors return HTTP `422` with Laravel validation error details.

Phone numbers are normalized by the backend before validation. The frontend may send Syrian phone numbers as `09xxxxxxxx`, `9639xxxxxxxx`, `009639xxxxxxxx`, or `+9639xxxxxxxx`; the backend stores them as `+9639xxxxxxxx`.

## Auth

### Sign Up

`POST /api/auth/signup`

Creates an app user with the `patient` role. The backend then checks whether the submitted phone belongs to an existing walk-in patient.

Request:

```json
{
    "username": "newuser123",
    "email": "newuser@enaya.com",
    "phone": "+963912345678",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Response `201`:

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "email": "newuser@enaya.com",
            "username": "newuser123",
            "roleId": 3
        },
        "token": "plain-text-sanctum-token",
        "expiresAt": "2026-07-07T12:00:00.000000Z"
    },
    "error": null,
    "errorCode": null
}
```

Signup patient-linking behavior:

- If `patients.phone` exists with `user_id = null`, the new account is linked to that existing patient record.
- If no patient exists with that phone, the backend creates an incomplete patient profile with `profile_completed = false`.
- If the phone already belongs to a linked patient, validation rejects the signup.

Frontend next step:

- After signup, call `GET /api/patients/profile`.
- If `profile_completed` is `false`, navigate the patient to the complete-profile screen.
- If `profile_completed` is `true`, let the patient continue into the app.

### Login

`POST /api/auth/login`

Request:

```json
{
    "usernameOrEmail": "newuser@enaya.com",
    "password": "password123"
}
```

Response `200` has the same token shape as signup.

### Current User

`GET /api/auth/me`

Requires auth.

Response `200`:

```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "email": "newuser@enaya.com",
            "username": "newuser123",
            "roleId": 3
        }
    },
    "error": null,
    "errorCode": null
}
```

### Refresh Token

`POST /api/auth/refresh-token`

Requires auth. Deletes the current token and returns a new token.

### Logout

`POST /api/auth/logout`

Requires auth. Deletes the current token.

## Patient App Profile

These endpoints require `auth:sanctum` and the `patient` role.

### Get Profile

`GET /api/patients/profile`

Response `200`:

```json
{
    "success": true,
    "data": {
        "name": "newuser123",
        "email": "newuser@enaya.com",
        "phone": "+963912345678",
        "full_name": "New User",
        "date_of_birth": "1995-05-20",
        "gender": "female",
        "address": "Damascus",
        "job": "Teacher",
        "profile_completed": false
    }
}
```

### Complete Profile

`POST /api/patients/complete-profile`

Use this once after signup when `profile_completed` is `false`. This endpoint requires all required patient profile fields and then sets `profile_completed = true`.

Request:

```json
{
    "full_name": "Jane Doe",
    "phone": "+963912345678",
    "date_of_birth": "1995-05-20",
    "gender": "female",
    "address": "Damascus",
    "job": "Teacher"
}
```

Response `200`:

```json
{
    "success": true,
    "message": "Profile completed successfully",
    "data": {
        "id": 1,
        "user_id": 1,
        "full_name": "Jane Doe",
        "phone": "+963912345678",
        "date_of_birth": "1995-05-20",
        "gender": "female",
        "address": "Damascus",
        "job": "Teacher",
        "profile_completed": true
    }
}
```

If the profile is already complete, the endpoint returns `403`.

Complete-profile flow for the mobile app:

1. User signs up or logs in.
2. Store the returned Sanctum token.
3. Call `GET /api/patients/profile`.
4. If `profile_completed = false`, show the complete-profile form.
5. Submit the form to `POST /api/patients/complete-profile`.
6. After success, navigate to the patient home screen.

### Update Profile

`PUT /api/patients/profile`

Use this after the profile already exists. All fields are optional.

Request:

```json
{
    "name": "Updated Username",
    "email": "updated@example.com",
    "phone": "+963933333333",
    "date_of_birth": "1990-01-01",
    "gender": "male",
    "address": "Homs",
    "job": "Engineer"
}
```

Response `200`:

```json
{
    "success": true,
    "message": "Profile updated successfully",
    "data": {
        "name": "Updated Username",
        "email": "updated@example.com",
        "phone": "+963933333333",
        "date_of_birth": "1990-01-01",
        "gender": "male",
        "address": "Homs",
        "job": "Engineer"
    }
}
```

## Reception Patients

These endpoints require `auth:sanctum` and the `receptionist` role.

### List Patients

`GET /api/patients/reception`

Query parameters:

| Name          | Description                                          |
| ------------- | ---------------------------------------------------- |
| `search`      | Matches patient full name or phone                   |
| `gender`      | `male` or `female`                                   |
| `has_account` | `true` for app-linked patients, `false` for walk-ins |

### Create Walk-In Patient

`POST /api/patients/reception`

Request:

```json
{
    "full_name": "Jane Doe",
    "phone": "+963912345678",
    "date_of_birth": "1995-05-20",
    "gender": "female",
    "address": "Damascus",
    "job": "Teacher"
}
```

Response `201`:

```json
{
    "success": true,
    "message": "Patient created successfully",
    "data": {
        "id": 1,
        "user_id": null,
        "full_name": "Jane Doe",
        "phone": "+963912345678",
        "profile_completed": true
    }
}
```

### Show Patient

`GET /api/patients/reception/{patient}`

### Update Patient

`PUT /api/patients/reception/{patient}`

Receptionists can update walk-in patients. App-linked patients require the `edit-app-patients` permission.

### Delete Patient

`DELETE /api/patients/reception/{patient}`

Receptionists can delete walk-in patients. App-linked patients require the `delete-app-patients` permission.

## Admin Panel

These endpoints require `auth:sanctum` and the `admin` role.

### List Users

`GET /api/admin/users`

Optional query parameters:

| Name     | Description                                             |
| -------- | ------------------------------------------------------- |
| `search` | Matches user name or email                              |
| `role`   | Filters by role: `doctor`, `receptionist`, or `patient` |
| `page`   | Page number for paginated results                       |

Response `200`:

```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "name": "Dr. House",
                "email": "house@enaya.com",
                "roles": ["doctor"]
            }
        ],
        "last_page": 1
    }
}
```

### Create User

`POST /api/admin/users`

Request body when creating a doctor:

```json
{
    "name": "Dr. Sam",
    "email": "sam@enaya.com",
    "password": "password123",
    "role": "doctor",
    "specialty": "Cardiology",
    "department_id": 2
}
```

Request body when creating a receptionist:

```json
{
    "name": "Nadia",
    "email": "nadia@enaya.com",
    "password": "password123",
    "role": "receptionist"
}
```

Response `201`:

```json
{
    "success": true,
    "message": "User created successfully.",
    "data": {
        "id": 5,
        "name": "Nadia",
        "email": "nadia@enaya.com",
        "roles": ["receptionist"]
    }
}
```

### Show User

`GET /api/admin/users/{user}`

Response `200` returns a user resource. For patients it returns the linked patient profile; for doctors it returns doctor details.

### Update User

`PUT /api/admin/users/{user}`

Available fields:

- `name`
- `email`
- `specialty` (doctors only)
- `department_id` (doctors only)

Response `200`:

```json
{
    "success": true,
    "message": "User updated successfully.",
    "data": {
        "id": 5,
        "name": "Nadia Updated",
        "email": "nadia@enaya.com"
    }
}
```

### Delete User

`DELETE /api/admin/users/{user}`

Admins cannot delete their own account. Returns `403` when attempting to delete the authenticated user.

### List Patients

`GET /api/admin/patients`

Optional query parameters:

| Name          | Description                                          |
| ------------- | ---------------------------------------------------- |
| `search`      | Matches patient full name or phone                   |
| `gender`      | `male` or `female`                                   |
| `has_account` | `true` for app-linked patients, `false` for walk-ins |
| `page`        | Page number for paginated results                    |

Response `200`:

```json
{
    "success": true,
    "data": {
        "current_page": 1,
        "data": [
            {
                "id": 1,
                "full_name": "Jane Doe",
                "phone": "+963912345678",
                "profile_completed": false
            }
        ],
        "last_page": 1
    }
}
```

### Create Patient

`POST /api/admin/patients`

Request:

```json
{
    "full_name": "Jane Doe",
    "phone": "+963912345678",
    "date_of_birth": "1995-05-20",
    "gender": "female",
    "address": "Damascus",
    "job": "Teacher"
}
```

Response `201`:

```json
{
    "success": true,
    "message": "Patient created successfully.",
    "data": {
        "id": 1,
        "user_id": null,
        "full_name": "Jane Doe",
        "phone": "+963912345678",
        "profile_completed": false
    }
}
```

A `409` conflict is returned if a patient with the same phone number already exists.

### Show Patient

`GET /api/admin/patients/{patient}`

Response `200` returns the patient resource including the linked user when present.

### Update Patient

`PUT /api/admin/patients/{patient}`

Admins can update any patient record.

### Delete Patient

`DELETE /api/admin/patients/{patient}`

Responds with a success message when the patient is removed.

## Doctor Patients

### List Patients For Doctor

`GET /api/doctors/{doctor}/patients`

Requires `auth:sanctum` and the `view-patients` permission.

Returns patients who have appointments with the given doctor.

Query parameters:

| Name                | Description                                          |
| ------------------- | ---------------------------------------------------- |
| `search`            | Matches patient full name or phone                   |
| `gender`            | `male` or `female`                                   |
| `has_account`       | `true` for app-linked patients, `false` for walk-ins |
| `profile_completed` | `true`/`false`                                       |
| `created_from`      | `YYYY-MM-DD` (patient `created_at` lower bound)      |
| `created_to`        | `YYYY-MM-DD` (patient `created_at` upper bound)      |
| `dob_from`          | `YYYY-MM-DD` (patient `date_of_birth` lower bound)   |
| `dob_to`            | `YYYY-MM-DD` (patient `date_of_birth` upper bound)   |

Response `200`:

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": null,
            "full_name": "Jane Doe",
            "phone": "+963912345678",
            "date_of_birth": "1995-05-20",
            "gender": "female",
            "address": "Damascus",
            "job": "Teacher",
            "profile_completed": true,
            "created_at": "2026-06-07 12:00:00"
        }
    ]
}
```

## Appointment Sessions

Appointment sessions are linked one-to-one with appointments.

- The linkage key is `appointment_sessions.appointment_id`.
- One appointment can have at most one session.
- Deleting an appointment deletes its linked session (cascade delete).

Model relations available in backend code:

- `Appointment::appointmentSession()`
- `AppointmentSession::appointment()`

## Common Frontend Handling

- Store token securely after signup/login.
- Always send `Accept: application/json`.
- Treat `401` as unauthenticated and send the user to login.
- Treat `403` as authenticated but not allowed.
- Treat `422` as form validation errors.
- Use `profile_completed` to decide whether app patients need the complete-profile screen.
