# Security policy

## Reporting a vulnerability

Do not open a public issue for a security vulnerability. Contact the
repository maintainers privately with a description, reproduction steps,
affected versions, and the potential impact. Remove credentials, personal
data, and other secrets from the report.

Reports are acknowledged as soon as practical and investigated on the
supported development branch. Fixes should include a regression test where
possible and must not disclose sensitive details in logs or public reports.

## Deployment requirements

- Use HTTPS for every application request.
- Keep database credentials and `JWT_SECRET` outside version control.
- Restrict database access and writable directories.
- Keep PHP error details hidden from visitors.
- Apply migrations during a maintenance window and back up the database first.
- Review installed PHP packages and update them through their upstream
  security advisories.

The application records authentication, authorization, CSRF, settings,
session-revocation, and report-export events for administrator review.
