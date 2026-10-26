# Features and Improvement Ideas

This document summarizes functionality present in the current project and lists
possible improvements for future work. Ideas below are suggestions, not existing
features.

## Current Features

### Accounts and access

- Separate admin, teacher, student, and suspended user levels.
- Server-side checks restrict teachers to their own classes and enrolled students.
- Admin-managed user accounts and profile updates.
- Email-and-password sign-in, password reset, and account activation flows.
- Sign-in throttling after five failed attempts for an email/address pair within a 15-minute window.
- Passwordless sign-in using WebAuthn.
- NFC-based login and NFC tag enrollment where the browser and device support Web NFC.
- JWT-backed sign-in sessions with per-account revocation after account changes, password resets, or logout.

### Courses and classes

- Admin management of courses, teachers, and students.
- Course start and end dates.
- Create and manage class sessions for a course.
- Assign teachers and enroll users in courses.
- Admin and teacher views for course and class information.

### Attendance

- Teachers and admins can record or update attendance manually.
- Students can mark attendance by scanning a class QR code; check-ins require course enrollment and reject duplicate present records.
- Attendance records include status, recorder, timestamp, and optional notes.
- View student attendance for a class and course attendance reports.
- Export every enrolled student, including students with no check-ins, plus attendance percentages as CSV.

### Data import

- Preview and import CSV files for users, courses, course enrollments, and classes, with required-field and email checks.
- Render imported CSV values as text in previews and neutralize spreadsheet formulas in report exports.
- Sample CSV files are provided in `assets/`.

### Application and configuration

- Browser-installable progressive web app with a service worker and cached assets.
- Installation wizard for database, site URL, system defaults, JWT, and initial admin setup.
- Configurable timezone, email sender, API HTTPS enforcement, and CORS policy.
- Log server-side errors while returning generic error messages to visitors.
- Apply database schema updates when the application starts.

## Improvement Ideas

### High priority

- **Add configurable session expiry:** session revocation after account changes and password resets is implemented; configurable expiry and administrative session management remain.
- **Expand automated coverage:** add NFC/WebAuthn and broader migration-upgrade tests.
- **Add short-lived QR tokens:** QR check-ins currently use a class code and enrollment checks; rotating server-validated tokens and session open/close controls would better resist sharing.
- **Improve bulk-import feedback:** summarize successful rows and errors, allow correcting rejected rows, and support larger imports safely.

### Useful next steps

- **Extend attendance reporting:** add date-range filters and attendance trends over time.
- **Export more formats:** offer XLSX or printable PDF reports in addition to CSV.
- **Add bulk actions:** support bulk enrollment, account status changes, and attendance corrections.
- **Provide an audit trail:** record who changed attendance, users, and course settings, and when.
- **Continue accessibility and mobile improvements:** review keyboard navigation, screen-reader announcements, contrast, and layouts across all screens.
- **Add notifications:** notify students about upcoming classes or attendance changes, with user-configurable preferences.

### Longer-term ideas

- **Support multiple organizations or campuses** with isolated data and role administration.
- **Add recurring class schedules** and calendar export or integration.
- **Add configurable attendance rules**, such as late arrival windows and excused absences.
- **Offer a documented API** for approved integrations with learning-management or student-information systems.
- **Add privacy and retention controls**, including configurable data retention and account export/deletion workflows.

## Suggested Starting Point

Continue by adding expiring QR tokens and session controls, then build on the
improved import, report, and authorization workflows before larger integrations.
