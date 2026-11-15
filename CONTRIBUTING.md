# Contributing

## Local setup

Use the Docker Compose setup for a disposable development environment:

```bash
cp .env.example .env
docker compose up --build
```

Open `http://localhost:8080/` and complete the installer. Seed demo data only
when needed with the explicitly guarded command documented in `README.md`.

## Validation

Run the focused checks before opening a pull request:

```bash
find lib pages bin tests -name '*.php' -print0 | xargs -0 -n1 php -l
php -l health.php
php -l calendar.php
node --test tests/*.test.js
git diff --check
```

The integration suite requires a disposable MySQL or MariaDB database and
valid credentials. Never run it against production data.

## Database changes

Add a new sequential `lib/SQL-I-WAS-HERE-N.sql` migration and register it at
the end of `I_SQL` in `lib/LIB-Install.php`. Update integration assertions
that verify the schema version. Migrations must be safe to apply once and
must preserve existing data unless the change explicitly requires removal.

## Security and privacy

Do not commit credentials, JWT secrets, personal data, or production
configuration. Review authorization, CSRF behavior, audit logging, and
privacy impact for every new endpoint. Report vulnerabilities privately as
described in `SECURITY.md`.
