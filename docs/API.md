# API overview

The browser client calls authenticated endpoints through the application API.
POST requests made by signed-in users require the session-bound `csrf_token`.
The shared client submits this token automatically.

## Main endpoint groups

| Group | Examples | Access |
| --- | --- | --- |
| Session | login, logout, active-session listing, revocation, MFA | Signed-in or anonymous login flow |
| Users | account updates and administrator user management | User or administrator |
| Attendance | QR sessions, check-ins, corrections, summaries | Role and class authorization |
| Courses/classes | course, enrollment, class, audit, and schedule operations | Role and ownership authorization |
| Security | security-event retrieval and filtering | Administrator only |
| Settings | system settings and notification preferences | Administrator or account owner |

API responses use the standard JSON shape:

```json
{
  "status": 1,
  "message": "OK",
  "data": {},
  "more": null,
  "page": null
}
```

Errors use `status: 0` and an explanatory message. Clients must not treat a
successful HTTP response alone as proof that an operation succeeded.

The public health endpoint is separate from the application API:
`GET /health.php` returns only `{"status":"ok"}` or an unavailable response.
