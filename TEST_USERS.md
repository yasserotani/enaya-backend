# Test Users

Use these seeded accounts after running:

```powershell
php artisan db:seed --no-interaction
```

| Type | Email | Password | Assigned Role |
| --- | --- | --- | --- |
| Admin | `admin@enaya.com` | `password` | `admin` |
| User | `user@enaya.com` | `password` | `patient` |
| Receptionist | `receptionist@enaya.com` | `password` | `receptionist` |
| Doctor | `doctor@enaya.com` | `password` | `doctor` |

> Note: The "User" test account currently uses the `patient` role in this project.

