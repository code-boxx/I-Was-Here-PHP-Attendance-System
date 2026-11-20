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
- Admins can manage school-wide holidays and non-instruction days. Existing
  classes are retained, labeled, and excluded from attendance calculations on
  those dates.
- Class owners and admins can cancel or reschedule a class with a reason.
  Cancellation preserves attendance history and closes an open QR session;
  rescheduling preserves the class record and checks schedule conflicts.

### Attendance

- Teachers and admins can record or update attendance manually.
- Teachers can open and close QR attendance sessions. The displayed QR token is signed, bound to its class session, expires after 30 seconds, and refreshes every 20 seconds.
- QR check-ins require course enrollment, reject duplicate present records, and are rejected after a session closes.
- Attendance records include present, absent, or excused status, recorder,
  timestamp, and optional notes. Excused absences are shown separately and
  excluded from attendance percentages and their thresholds.
- Attendance status changes are retained in a class-level audit history with the actor, time, old/new status, source, and reason. Manual status corrections and correction decisions require a reason.
- Students can view completed-class attendance totals and percentages for their enrolled courses.
- Students see a warning when their attendance percentage falls below the admin-configured school-wide threshold (default 75%).
- Students can submit one pending correction request per class, and teachers assigned to the class or admins can approve or reject it with a recorded reason.
- Teachers and admins can view a live class dashboard with present, absent, and unrecorded counts; students not marked present; the latest QR check-in; and session status. It refreshes every 10 seconds while the tab is visible.
- Teachers can view present, absent, late, and unrecorded session summaries. Admins configure the late-arrival grace period from 0 to 1440 minutes (default 10).
- Teachers can mark an enrolled student's attendance as excused. Late status
  uses server-recorded attendance time and is applied only when the grace
  period is exceeded.
- Teachers can open a projector-friendly attendance view that refreshes aggregate counts without sending individual student records.
- A class owner or admin can assign a teacher substitute for one class. Substitute access expires automatically and cannot extend beyond 12 hours after class start.
- Students can save offline check-in requests locally and upload them when online. They remain unverified until an authorized teacher reviews them; approval records attendance at review time and does not overwrite existing attendance.
- Class cancellation, rescheduling, and school calendar changes retain a
  reviewable actor/time/reason history. Opted-in users receive queued email
  updates for affected class schedule changes.
- Attendance allows one record per student and class. Guest or visiting
  students are not currently supported.
- View student attendance for a class and course attendance reports.
- Export every enrolled student, including students with no check-ins, plus attendance percentages as CSV.
- Admins can filter course attendance reports by optional start/end dates, download the filtered CSV, or open a print-friendly report.
- Admins and assigned teachers can export weekly attendance trends and below-threshold student follow-up CSVs for authorized courses.
- Admins can bulk add up to 200 course users by email or remove selected course users while preserving past attendance.
- Class scheduling blocks same-teacher sessions that overlap a 60-minute session window; room conflicts are not modeled.

### Data import

- Preview and import CSV files for users, courses, course enrollments, and classes, with required-field and email checks.
- Render imported CSV values as text in previews and neutralize spreadsheet formulas in report exports.
- Sample CSV files are provided in `assets/`.

### Application and configuration

- Browser-installable progressive web app with a service worker and cached assets.
- Installation wizard for database, site URL, system defaults, JWT, and initial admin setup.
- Configurable timezone, email sender, API HTTPS enforcement, and CORS policy.
- Admin-configurable attendance warning threshold from 1% to 100%.
- Users can opt in to email attendance warnings, correction decisions, and class reminders; a scheduled worker queues, deduplicates, retries, and delivers notifications using PHP's configured mail transport.
- Log server-side errors while returning generic error messages to visitors.
- Apply database schema updates when the application starts.
- Keep PWA scripts in a versioned cache so clients activate updated feature assets.

## Improvement Ideas

### High priority

- **Add configurable session expiry:** session revocation after account changes and password resets is implemented; configurable expiry and administrative session management remain.
- **Expand automated coverage:** add NFC/WebAuthn and broader migration-upgrade tests.
- **Improve bulk-import feedback:** summarize successful rows and errors, allow correcting rejected rows, and support larger imports safely.

### Useful next steps

- **Extend attendance reporting:** live class counts, date-range filters,
  attendance trends, downloadable summaries, excused statuses, and
  below-threshold exports are implemented; richer report templates remain.
- **Export more formats:** offer XLSX or printable PDF reports in addition to CSV.
- **Add bulk actions:** support bulk enrollment, account status changes, and attendance corrections.
- **Extend the audit trail:** attendance changes now have an audit history; add audit coverage for user accounts, course settings, exports, and other administrative actions.
- **Continue accessibility and mobile improvements:** review keyboard navigation, screen-reader announcements, contrast, and layouts across all screens.
- **Expand notifications:** opt-in email reminders and cancellation/reschedule
  updates are implemented; push delivery and staff digests remain.

### Longer-term ideas

- **Support multiple organizations or campuses** with isolated data and role administration.
- **Add recurring class schedules** and calendar export or integration.
- **Expand configurable attendance rules** beyond the current excused,
  holiday, threshold, and late-arrival policies; institution-specific
  attendance formulas remain.
- **Offer a documented API** for approved integrations with learning-management or student-information systems.
- **Add privacy and retention controls**, including configurable data retention and account export/deletion workflows.

## Suggested Starting Point

Continue with import reconciliation, roster administration, and broader
administrative audit coverage before larger integrations. QR rotation reduces
the usefulness of screenshots but cannot prevent sharing a live QR code;
institutions may need additional in-person verification.
