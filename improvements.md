# Improvement Roadmap

This document is the ordered plan for improving the Community Edition. Each
step builds on the work before it. Status describes the current development
branch; a roadmap item is not complete just because it is listed here.

## Status at a glance

- **Steps 1-4: Implemented.** Core attendance, analytics, notifications,
  attendance policies, school calendar, and class lifecycle workflows are
  available.
- **Step 5: Next.** Improve administration and data operations.
- **Steps 5-10: Planned.** Administration, PWA, security, integrations,
  developer tooling, and larger institutional capabilities.

## Step 1 — Secure attendance foundations — Implemented

- Use short-lived, server-validated QR tokens tied to an active class session.
- Let teachers open and close attendance sessions; reject expired sessions and
  duplicate check-ins.
- Enforce class-level authorization on protected server operations.
- Preserve attendance corrections and their reasons in an audit history.
- Provide students with their course attendance summary, class history, and
  correction-request history.
- Record useful server-side errors and track database schema migrations.

Rotating QR codes reduce the time available to reuse a screenshot, but cannot
prevent sharing a currently valid live code. Keep this limitation clear to
users; stronger identity or proximity checks require separate design work.

## Step 2 — Analytics and communication — Implemented

- Show teachers live session totals and check-in activity, including present,
  absent, unrecorded, and last-check-in information.
- Let admins configure the school-wide attendance warning threshold; show
  students a warning when their attendance falls below it.
- Provide date-filtered CSV and print-friendly reports, weekly trends, and
  below-threshold follow-up exports.
- Support bulk course enrollment actions and detect scheduling conflicts for
  the same teacher within the documented session window.
- Let users opt in to attendance warnings, correction decisions, and
  upcoming-class reminders. Queue, deduplicate, retry, and deliver email
  notifications using the host's configured mail transport.

Known limitation: room scheduling conflicts cannot be checked until rooms are
represented in the data model.

## Step 3 — Attendance workflow extensions — Implemented

- Configure a late-arrival grace period and show teachers present, late,
  absent, and unrecorded session totals.
- Grant substitute teachers temporary access to an assigned class. Access is
  scoped to that class, expires automatically, and cannot be used to assign
  further substitutes.
- Let students save check-in requests locally and upload them when online.
  Device timestamps are unverified; queued requests do not count as attendance
  until a teacher reviews them. Approval records the review time and does not
  overwrite an existing attendance record.
- Provide teachers with an aggregate-only projector view; it does not return
  student names or per-student attendance records.
- Show whether the browser reports a network connection. This is only a
  browser connectivity hint, not confirmation that the application server is
  reachable.

This is not yet a full offline-first PWA. Offline requests are not equivalent
to server-verified QR attendance.

## Step 4 — Attendance policies and session lifecycle — Implemented

1. Record an explicit **excused** attendance status. Excused records are
   reported separately and excluded from both the attended count and the
   percentage denominator. The same calculation is used by student summaries,
   threshold warnings, trends, follow-up exports, and attendance reports.
2. Let admins configure school-wide holidays and non-instruction days. Existing
   classes on those dates are preserved for history but excluded from
   attendance calculations; new sessions, QR check-ins, corrections, and
   offline approvals are blocked for those dates.
3. Let class owners and admins cancel or reschedule a class with a required
   reason. Cancellation preserves attendance records, closes an active QR
   session, excludes the class from totals, and queues notices for users who
   opted in to class reminders. Rescheduling retains the class and attendance
   history, checks the teacher's 60-minute conflict window, closes an active
   QR session, and sends an opted-in update.
4. Keep one attendance record per enrolled student per class; the database key
   and duplicate check-in handling prevent multiple records. The existing
   school-wide minimum attendance warning is configurable from 1% to 100%.
   Late status is based on server-recorded attendance time and the configured
   grace period. Guest attendance is not supported; it requires an explicitly
   authorized enrollment workflow before implementation.
5. Preserve status corrections and class schedule changes in audit history,
   including old/new values, actor, time, and required reason. Student
   correction requests retain their proposed status and reason, with the
   teacher/admin decision and decision reason.

The late rule is "late after the configured grace period" (strictly greater
than the grace value). Offline device timestamps remain unverified; approval
uses the teacher review time.

## Step 5 — Improve administration and data operations — Next

1. Improve roster search, filtering, sorting, teacher assignment, and student
   movement between courses.
2. Add CSV import reconciliation: preview new, changed, unchanged, duplicate,
   and conflicting rows before saving.
3. Provide a downloadable row-level error report and a clear, reviewable
   import result.
4. Extend timetable conflict checks to rooms and student groups once those
   entities and schedules exist.
5. Add safe undo or correction workflows for important administrative
   changes, retaining who changed what and why.

## Step 6 — Make the PWA dependable on mobile and unreliable networks — Planned

1. Build on the connection hint with explicit pending, syncing, synchronized,
   failed, and expired states for queued work.
2. Add safe retry and duplicate prevention for submissions that time out.
3. Improve camera permission guidance, low-light QR scanning, and successful
   check-in confirmation.
4. Provide accessible font sizing, keyboard navigation, and screen-reader
   feedback throughout attendance workflows.
5. Reduce unnecessary network requests and provide a low-bandwidth experience.
6. Test installation, updates, and offline behavior on supported Android and
   iOS browsers.
7. Decide whether and how to cache class information. Any cached student data
   must account for shared devices, logout, stale permissions, and retention.

Do not treat a device timestamp as trusted evidence. Offline records must
remain distinguishable from server-verified records, and conflicts or expired
sessions must have an explicit review policy.

## Step 7 — Strengthen production security and privacy — Planned

1. Review authorization on every protected server request, including exports
   and administrative actions.
2. Ensure role changes, account suspension, and course-assignment changes
   promptly invalidate obsolete access.
3. Review CSRF protections, output escaping, input validation, prepared
   statements, transaction boundaries, and database least privilege.
4. Add account protections such as login throttling, secure recovery, and
   optional administrator MFA.
5. Add active-session visibility, remote logout, idle expiry, absolute expiry,
   and signing-key rotation.
6. Record security-relevant events such as authentication failures,
   permission denials, exports, configuration changes, and session revocation.
7. Establish dependency monitoring, patching guidance, and a vulnerability
   reporting process.
8. Define retention, export, and deletion controls for personal data.

Offline caches and service-worker behavior must not expose sensitive records
after logout or after permissions change.

## Step 8 — Extend communication and approved integrations — Planned

1. Expand notification options only with user preferences, deduplication,
   retries, and clear delivery status.
2. Consider optional push notifications and authorized staff digests after
   the email workflow is stable.
3. Document supported APIs and error formats; add webhooks only with
   authentication, authorization, and delivery retry controls.
4. Design controlled enrollment imports or integrations with learning
   management and student information systems.
5. Consider calendar exports for class schedules.

## Step 9 — Improve contributor and operator workflows — Planned

1. Add continuous integration for automated tests and supported PHP versions.
2. Add PHP static analysis and consistent formatting checks.
3. Document the database schema, API behavior, installation, upgrades, and
   migration recovery.
4. Provide a reproducible local environment, such as Docker Compose, and
   development seed data.
5. Add a health check that reports application and database readiness without
   exposing secrets or sensitive diagnostics.
6. Document supported versions, deployment hardening, backups, restore
   checks, contribution guidelines, and vulnerability reporting.

Prioritize regression tests, safe migrations, and a reproducible development
setup before considering a framework rewrite.

## Step 10 — Consider larger institutional capabilities — Long term

- Academic timetable management for terms, recurring sessions, rooms,
  holidays, examinations, and calendar exports.
- Reusable custom report definitions for authorized staff.
- Multi-campus or multi-tenant operation with isolation enforced at the
  data-access layer, delegated administrators, and institutional settings.
- Institutional branding and terminology.
- Additional student information system and learning-management integrations.

These are larger changes with data-isolation and operational consequences.
They should follow the security, policy, and developer-foundation work above.

## NFC attendance: security gate

NFC attendance is not part of the next implementation step. A static NFC tag
identifier or reusable token is not proof that the enrolled student is
present, and a lost tag may be copied or misused. Before adding NFC as an
attendance method:

1. Confirm that target institutions need it and identify supported hardware.
2. Design a secure verification method, preferably cryptographic
   challenge-response where supported.
3. Add lost-tag revocation, replacement enrollment, reassignment protection,
   and authenticated confirmation.
4. Keep rotating QR and authorized manual attendance as accessible fallbacks.

Do not weaken the existing login or attendance flow by treating a static tag
value as trusted identity or presence evidence.
