# Improvement Roadmap

This document is the ordered plan for improving the Community Edition. Each
step builds on the work before it. Status describes the current development
branch; a roadmap item is not complete just because it is listed here.

## Status at a glance

- **Steps 1-4: Implemented.** Core attendance, reporting, notifications,
  attendance policies, school calendar, class lifecycle workflows, and
  attendance correction history are available.
- **Step 5: In progress.** Roster search/filter/sort, course transfers, CSV
  reconciliation for courses/users/classes/enrollments, row-error downloads,
  room/group conflict checks, course audit history, and reasoned roster
  audit/undo are implemented. Class schedule changes now also support guarded
  undo with conflict rechecking and an undo reason.
- **Step 6: In progress.** Offline student QR check-ins now have local pending,
  syncing, synchronized, failed, and expired states with automatic retry and
  idempotent server submission. Camera permission guidance, live status
  announcements, reduced scanner polling, and API network-cache protection
  are implemented. Dynamic page navigation now moves focus to the loaded
  heading, and logout/session expiry clears offline attendance data and
  service-worker caches. Device-level PWA testing and a formal cache-retention
  policy remain.
- **Step 7: In progress.** Broader security/privacy work has started; idle
  and absolute session expiry are now configurable and enforced.
- **Steps 8-9: Planned.** Integrations and contributor/operator tooling.
- **Step 10: Long term.** Larger institutional capabilities.

**Validation checkpoint (2026-11-15):** JavaScript tests, JavaScript syntax
checks, PHP lint, and `git diff --check` passed for the recent changes. The
database integration suite is available but requires a configured disposable
MySQL test database; the current environment rejected the configured
`root@localhost` credentials.

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

Room and student-group conflicts are checked when those optional values are
provided on a class; recurring schedules and reusable room/group directories
remain future work.

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

## Step 5 — Improve administration and data operations — In progress

1. **Implemented:** Improve roster search, role filtering, sorting, teacher
   assignment, and student movement between courses.
2. **Implemented:** Course, user, class, and course-enrollment CSV imports
   classify rows before saving. Course imports identify new, changed, and
   unchanged records; all import types flag duplicate and conflicting rows.
3. **Implemented:** Downloadable row-level error report and a clear,
   reviewable import result.
4. **Implemented:** Classes can optionally record a room and student group.
   The same 60-minute conflict window now rejects overlapping scheduled
   classes that reuse either resource, including CSV imports.
5. **Partially implemented:** Course roster changes record who changed
   enrollment, when, and why, and support guarded undo with an undo reason.
   Course creation, edits, and deletion now retain actor, reason, timestamp,
   and before/after values in a viewable audit history. Guarded undo/correction
   workflows now cover course edits and class schedule changes; other
   administrative operations remain.

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

## Step 7 — Strengthen production security and privacy — In progress

1. Review authorization on every protected server request, including exports
   and administrative actions.
2. Ensure role changes, account suspension, and course-assignment changes
   promptly invalidate obsolete access.
3. **Implemented:** Authenticated POST APIs now require a session-bound CSRF
   token, and the shared client automatically submits it. Anonymous login and
   recovery flows remain available without an authenticated token.
4. **Implemented:** Authenticated JWT sessions enforce configurable idle and
   absolute expiry limits, with defaults of 30 minutes idle and 12 hours
   absolute lifetime.
5. **Implemented:** Active sessions are registered server-side, checked on
   each authenticated request, and can be listed or individually revoked.
6. **Implemented:** Authentication failures, throttling, CSRF failures,
   authorization denials, and settings changes are recorded in a protected
   security-event log accessible to admins.
7. Add account protections such as login throttling, secure recovery, and
   optional administrator MFA.
8. Add signing-key rotation.
9. **Implemented:** Report exports and session revocation are recorded in the
   security-event log.
10. **Implemented:** Deployment hardening, dependency update guidance, and
    private vulnerability-reporting guidance are documented in `SECURITY.md`.
11. Define retention, export, and deletion controls for personal data.

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




Here are additional improvement ideas beyond the ones already listed in your features.md.

1. High-impact features to add
Attend-class.com - Revolutionary QR Code Attendance System for Modern Education

1. Smart QR attendance security
High priority
Prevent students from sharing screenshots of attendance QR codes.

Generate a new QR code every 15–30 seconds.

Use short-lived, server-validated attendance tokens.

Bind tokens to a specific class session and course.

Enforce one attendance submission per student per session.

Set attendance opening and closing times.

Allow teachers to revoke or close a session immediately.

Important: QR rotation alone cannot prevent someone from sharing a live QR code. Combine it with other checks where appropriate.

Parent Teacher edtech website - UI Design by Raghavendra Bhat on Dribbble

2. Live classroom attendance dashboard
Give teachers a real-time view of attendance as students check in.

Show present, late, absent and excused counts.

Update the dashboard without refreshing the page.

Display the last successful check-in.

Highlight students who have not checked in.

Allow manual corrections with a recorded reason.

Generate a session summary when attendance closes.

This would be one of the most immediately useful improvements for everyday classroom use.

CAMU | Reviews, Pricing & Demos - SoftwareAdvice AU

3. Student attendance portal
Give students a dedicated view of their own attendance.

View attendance history by course.

See attendance percentage and missed classes.

Receive warnings when attendance falls below a threshold.

Review class dates and session details.

Submit an absence explanation or correction request.

Download a personal attendance statement.

Students should see only their own records, while teachers should see only the classes they are authorized to manage.

A large-scale implementation of predictive learning analytics in higher education: the teachers’ role and perspective | Educational technology research and development | Springer Nature Link

4. Early-warning and attendance analytics
Turn attendance data into actionable information.

Identify students approaching an attendance limit.

Detect repeated absences or declining attendance.

Compare attendance across courses and class sessions.

Show weekly and monthly attendance trends.

Provide configurable warning thresholds.

Export a list of students needing follow-up.

Start with transparent rules, such as attendance below 75%, rather than an opaque prediction model. Let institutions configure their own policies.

2. Attendance features that solve real-world problems
These ideas can differentiate the project from a basic attendance system.

Feature

How it would work

Priority

Offline attendance queue

Save check-ins locally when connectivity drops, then sync securely when online

High

Multiple attendance methods

Support QR, NFC, teacher entry and approved manual imports

High

Late-arrival tracking

Record check-in time and calculate lateness automatically

High

Attendance correction requests

Students request changes; teachers approve or reject them

High

Substitute teacher access

An authorized substitute manages a class for a limited period

Medium

Class cancellation and rescheduling

Update sessions and notify enrolled students

Medium

Guest or visiting students

Allow temporary attendance with explicit authorization

Medium

Attendance dispute resolution

Track the original record, proposed correction and final decision

Medium

Classroom check-in display

Show aggregate attendance progress on a projector without exposing student information

Medium

Multiple check-ins policy

Configure whether a student may leave and return, if required by the institution

Low

A particularly valuable addition: offline-first attendance
Since this is a progressive web app, you could build an offline attendance workflow for institutions with unreliable internet connections.

A safe design would:

Cache the class roster and authorized session information before class.

Queue attendance events locally with unique event IDs and timestamps.

Sync pending events when connectivity returns.

Make synchronization idempotent so retrying an event does not create duplicates.

Clearly label unverified, pending and synchronized records.

Security consideration: a timestamp stored on a student's device is not inherently trustworthy. Offline check-ins should be distinguishable from server-verified check-ins, and the application should define how conflicts and expired sessions are handled.

3. NFC improvements
You already support NFC login and enrollment. You can extend this without making NFC mandatory for devices that do not support it.

NFC attendance check-in: Let students tap a supported reader or tag as an alternative to scanning a QR code.

Lost-tag management: Allow admins to disable a lost tag and enroll a replacement.

Tag reassignment protection: Prevent the same active tag from being assigned to multiple accounts.

Enrollment confirmation: Require an authenticated user and a confirmation step before binding a tag.

Secure tag verification: Do not trust a static tag identifier as proof of identity; where hardware supports it, use cryptographic challenge-response.

Device compatibility checks: Clearly explain when Web NFC is unsupported and offer QR or manual attendance instead.

Avoid tying attendance to expensive hardware unless your target institutions actually need it. Standard QR attendance should remain a reliable fallback.

4. Improve the administration experience
Admins often spend more time managing data than recording attendance. These features could save significant time.

Class roster management

Search, filter and sort students; move students between courses; manage waitlists; and bulk-assign teachers.

Timetable conflict detection

Warn when a teacher, classroom or student group has overlapping scheduled sessions.

Import reconciliation

Preview new, updated, unchanged and conflicting records before committing a CSV import. Provide a downloadable error file with row numbers and reasons.

Configurable institutional policies

Configure attendance thresholds, late-arrival grace periods, excused absences, holidays and minimum attendance requirements without changing PHP source code.

Safe undo and change history

Provide a reviewable history of important changes, including the original and corrected attendance status, the actor, timestamp and reason.

One design principle matters here: attendance records should not simply disappear when corrected. Preserve the history of important changes so disputes can be resolved later.

5. Security improvements for a production-ready system
Because the application handles student identities and attendance records, security deserves its own roadmap.

Improvement

What to implement

Fine-grained permissions

Check permissions on every server-side request, not just whether a user is logged in.

JWT revocation

Support server-side revocation, token expiry and signing-key rotation.

CSRF and XSS protection

Protect state-changing requests and safely escape user-generated content.

Database security

Use prepared statements, transactions, constraints and least-privilege database credentials.

Account protection

Add login throttling, secure recovery, suspicious-login notifications and optional MFA for admins.

Session management

Show active sessions, allow remote logout and enforce idle and absolute expiry.

Dependency monitoring

Track vulnerable PHP packages and JavaScript libraries; establish an update process.

Security event logging

Record login failures, permission denials, exports, administrative changes and session revocations.

These align with the OWASP guidance on authentication , session management  and API security .

OWASP Cheat Sheet Series
+2

Two specific issues deserve extra attention:

Role changes must take effect promptly. If an admin suspends a user or removes a teacher's course assignment, previously issued JWTs should not continue granting obsolete permissions.

Offline and cached data must respect access changes. The PWA should avoid exposing sensitive student records through shared devices, stale caches or service-worker responses after logout.

6. PWA and mobile experience
Make the application dependable on the phones students already use.

Add a clear connection indicator and synchronization status.

Provide a low-bandwidth mode with minimal images and network requests.

Improve camera permission guidance and QR scanning in low light.

Support accessible font sizes, keyboard navigation and screen readers.

Add a visible confirmation after successful attendance submission.

Prevent duplicate submissions when students tap repeatedly or retry after a timeout.

Handle expired sessions and failed synchronization without losing pending work.

Test installation, updates and offline behavior on Android and iOS browsers.

An especially useful feature would be a one-tap student check-in screen that opens directly to the current eligible class, rather than making students navigate through multiple pages.

7. Notifications and communication
Introduce a configurable notification system with email first, followed by optional push notifications.

Class reminders before a scheduled session.

Alerts when attendance drops below an institutional threshold.

Notifications when a correction request is approved or rejected.

Alerts to teachers when a class roster changes.

Admin notifications for failed imports or suspicious activity.

Daily or weekly attendance summaries for authorized staff.

Include notification preferences, deduplication and retry handling. Avoid sending a notification for every small change if a daily digest would be more useful.

8. Features that could make the project stand out
These are larger opportunities once the core system is reliable.

edumerge - ERP Suite for Groups of Institutions

Multi-tenant SaaS edition

Let different schools or institutions run on one deployment with isolated data, separate administrators, institutional branding and configurable plans. Enforce tenant isolation at the data-access layer.



Academic timetable management

Manage semesters, academic years, rooms, recurring sessions, holidays, examination schedules and calendar exports.

BUKSU SIAS: Modern Student Information & · Built with Blink

Student information system integrations

Import enrollment data from existing systems and offer documented APIs or webhooks for approved integrations with learning-management systems.

Student Details Report | Bold Reports

Custom report builder

Allow authorized administrators to select courses, date ranges, attendance statuses and report columns, then save reusable report templates.

9. Developer experience and maintainability
Since this is an open-source PHP project, improving the developer workflow will help contributors deliver features safely.

Add a reproducible local environment using Docker Compose as an alternative to XAMPP or WAMP.

Introduce continuous integration to run automated tests on every pull request.

Add static analysis with PHPStan or Psalm and formatting checks.

Document the database schema and relationships.

Provide database migrations rather than relying exclusively on installer-time schema creation.

Add a development seed script with fake users, courses and attendance records.

Create an API reference with request examples, response formats and error codes.

Add a health-check endpoint for database connectivity and application status.

Document supported PHP versions and test against them.

Create a SECURITY.md, contribution guidelines and a process for reporting vulnerabilities.

Add upgrade checks and rollback guidance for database changes.

For an application that uses a custom PHP framework, I would prioritize regression tests, migrations and a clean development setup before attempting a major architectural rewrite.

10. My recommended development roadmap
Here is how I would sequence the work to keep each release useful.

Phase 1

Reliability and security
Foundation release

Automated tests for authentication, enrollment and attendance.

Server-side authorization and session revocation.

Better API errors and structured logs.

CSV validation and database integrity checks.

Phase 2

Smarter attendance
Core product upgrade

Expiring QR tokens and duplicate-check-in prevention.

Late-arrival rules and session controls.

Student attendance portal.

Correction requests and audit history.

Phase 3

Analytics and automation
Efficiency release

Attendance dashboards and configurable alerts.

Advanced reports and printable exports.

Bulk operations and timetable conflict checks.

Notification preferences.

Phase 4

Scale and integrations
Long-term expansion

Multi-campus support.

Offline-first attendance.

External API integrations.

Advanced reporting and configurable institutional policies.

11. Additional ideas for your features.md
You could add this section directly to your existing improvement document.

Writing


Additional Improvement Ideas
Attendance integrity and classroom workflows
Expiring QR tokens: Rotate attendance tokens and validate them against the active class session on the server.
Live attendance dashboard: Show real-time check-ins, attendance totals and students who have not checked in.
Late-arrival tracking: Calculate lateness using configurable grace periods and class start times.
Attendance correction requests: Allow students to request corrections and teachers to review them with an audit trail.
Offline-first attendance: Queue eligible attendance events locally and synchronize them safely when connectivity returns.
NFC attendance: Support NFC check-ins where compatible hardware and browsers are available, with QR fallback.
Student and teacher experience
Student attendance portal: Display course attendance percentages, missed sessions, history and correction requests.
Attendance alerts: Notify students when they approach configurable attendance limits.
Teacher timetable: Display upcoming classes, assigned courses and class rosters.
Substitute teacher access: Grant temporary, scoped permissions for authorized replacement teachers.
Low-bandwidth mode: Reduce network usage and improve usability on unreliable connections.
Security and data integrity
Fine-grained authorization: Enforce role, course and session permissions on every protected server request.
Session administration: Support active-session visibility, remote revocation and configurable expiration.
Security event monitoring: Log important authentication, permission, export and configuration events.
Attendance integrity: Prevent duplicate submissions and preserve the history of attendance corrections.
Dependency security: Monitor package vulnerabilities and establish a documented patching process.
Administration and analytics
Timetable conflict detection: Identify overlapping teacher, room and class schedules.
Advanced reporting: Provide reusable report templates, date filters, attendance trends and printable exports.
Import reconciliation: Preview changes, identify duplicates and download row-level validation errors before committing imports.
Configurable policies: Support institutional rules for late arrivals, excused absences, attendance thresholds and holidays.
Bulk operations: Simplify enrollment, account management and attendance corrections.
Developer experience and operations
Continuous integration: Run automated tests and code-quality checks on every change.
Database migrations: Track schema changes and provide documented upgrade procedures.
Reproducible development environment: Offer Docker-based setup and development seed data.
Operational health checks: Monitor application dependencies and provide safe diagnostics.
Security documentation: Publish vulnerability-reporting instructions, supported versions and deployment hardening guidance.
Long-term opportunities
Multi-campus support: Isolate institutional data and delegate administration.
External integrations: Provide Here are additional improvement ideas beyond the ones already listed in your features.md.

1. High-impact features to add
Attend-class.com - Revolutionary QR Code Attendance System for Modern Education

1. Smart QR attendance security
High priority
Prevent students from sharing screenshots of attendance QR codes.

Generate a new QR code every 15–30 seconds.

Use short-lived, server-validated attendance tokens.

Bind tokens to a specific class session and course.

Enforce one attendance submission per student per session.

Set attendance opening and closing times.

Allow teachers to revoke or close a session immediately.

Important: QR rotation alone cannot prevent someone from sharing a live QR code. Combine it with other checks where appropriate.

Parent Teacher edtech website - UI Design by Raghavendra Bhat on Dribbble

2. Live classroom attendance dashboard
Give teachers a real-time view of attendance as students check in.

Show present, late, absent and excused counts.

Update the dashboard without refreshing the page.

Display the last successful check-in.

Highlight students who have not checked in.

Allow manual corrections with a recorded reason.

Generate a session summary when attendance closes.

This would be one of the most immediately useful improvements for everyday classroom use.

CAMU | Reviews, Pricing & Demos - SoftwareAdvice AU

3. Student attendance portal
Give students a dedicated view of their own attendance.

View attendance history by course.

See attendance percentage and missed classes.

Receive warnings when attendance falls below a threshold.

Review class dates and session details.

Submit an absence explanation or correction request.

Download a personal attendance statement.

Students should see only their own records, while teachers should see only the classes they are authorized to manage.

A large-scale implementation of predictive learning analytics in higher education: the teachers’ role and perspective | Educational technology research and development | Springer Nature Link

4. Early-warning and attendance analytics
Turn attendance data into actionable information.

Identify students approaching an attendance limit.

Detect repeated absences or declining attendance.

Compare attendance across courses and class sessions.

Show weekly and monthly attendance trends.

Provide configurable warning thresholds.

Export a list of students needing follow-up.

Start with transparent rules, such as attendance below 75%, rather than an opaque prediction model. Let institutions configure their own policies.

2. Attendance features that solve real-world problems
These ideas can differentiate the project from a basic attendance system.

Feature

How it would work

Priority

Offline attendance queue

Save check-ins locally when connectivity drops, then sync securely when online

High

Multiple attendance methods

Support QR, NFC, teacher entry and approved manual imports

High

Late-arrival tracking

Record check-in time and calculate lateness automatically

High

Attendance correction requests

Students request changes; teachers approve or reject them

High

Substitute teacher access

An authorized substitute manages a class for a limited period

Medium

Class cancellation and rescheduling

Update sessions and notify enrolled students

Medium

Guest or visiting students

Allow temporary attendance with explicit authorization

Medium

Attendance dispute resolution

Track the original record, proposed correction and final decision

Medium

Classroom check-in display

Show aggregate attendance progress on a projector without exposing student information

Medium

Multiple check-ins policy

Configure whether a student may leave and return, if required by the institution

Low

A particularly valuable addition: offline-first attendance
Since this is a progressive web app, you could build an offline attendance workflow for institutions with unreliable internet connections.

A safe design would:

Cache the class roster and authorized session information before class.

Queue attendance events locally with unique event IDs and timestamps.

Sync pending events when connectivity returns.

Make synchronization idempotent so retrying an event does not create duplicates.

Clearly label unverified, pending and synchronized records.

Security consideration: a timestamp stored on a student's device is not inherently trustworthy. Offline check-ins should be distinguishable from server-verified check-ins, and the application should define how conflicts and expired sessions are handled.

3. NFC improvements
You already support NFC login and enrollment. You can extend this without making NFC mandatory for devices that do not support it.

NFC attendance check-in: Let students tap a supported reader or tag as an alternative to scanning a QR code.

Lost-tag management: Allow admins to disable a lost tag and enroll a replacement.

Tag reassignment protection: Prevent the same active tag from being assigned to multiple accounts.

Enrollment confirmation: Require an authenticated user and a confirmation step before binding a tag.

Secure tag verification: Do not trust a static tag identifier as proof of identity; where hardware supports it, use cryptographic challenge-response.

Device compatibility checks: Clearly explain when Web NFC is unsupported and offer QR or manual attendance instead.

Avoid tying attendance to expensive hardware unless your target institutions actually need it. Standard QR attendance should remain a reliable fallback.

4. Improve the administration experience
Admins often spend more time managing data than recording attendance. These features could save significant time.

Class roster management

Search, filter and sort students; move students between courses; manage waitlists; and bulk-assign teachers.

Timetable conflict detection

Warn when a teacher, classroom or student group has overlapping scheduled sessions.

Import reconciliation

Preview new, updated, unchanged and conflicting records before committing a CSV import. Provide a downloadable error file with row numbers and reasons.

Configurable institutional policies

Configure attendance thresholds, late-arrival grace periods, excused absences, holidays and minimum attendance requirements without changing PHP source code.

Safe undo and change history

Provide a reviewable history of important changes, including the original and corrected attendance status, the actor, timestamp and reason.

One design principle matters here: attendance records should not simply disappear when corrected. Preserve the history of important changes so disputes can be resolved later.

5. Security improvements for a production-ready system
Because the application handles student identities and attendance records, security deserves its own roadmap.

Improvement

What to implement

Fine-grained permissions

Check permissions on every server-side request, not just whether a user is logged in.

JWT revocation

Support server-side revocation, token expiry and signing-key rotation.

CSRF and XSS protection

Protect state-changing requests and safely escape user-generated content.

Database security

Use prepared statements, transactions, constraints and least-privilege database credentials.

Account protection

Add login throttling, secure recovery, suspicious-login notifications and optional MFA for admins.

Session management

Show active sessions, allow remote logout and enforce idle and absolute expiry.

Dependency monitoring

Track vulnerable PHP packages and JavaScript libraries; establish an update process.

Security event logging

Record login failures, permission denials, exports, administrative changes and session revocations.

These align with the OWASP guidance on authentication , session management  and API security .

OWASP Cheat Sheet Series
+2

Two specific issues deserve extra attention:

Role changes must take effect promptly. If an admin suspends a user or removes a teacher's course assignment, previously issued JWTs should not continue granting obsolete permissions.

Offline and cached data must respect access changes. The PWA should avoid exposing sensitive student records through shared devices, stale caches or service-worker responses after logout.

6. PWA and mobile experience
Make the application dependable on the phones students already use.

Add a clear connection indicator and synchronization status.

Provide a low-bandwidth mode with minimal images and network requests.

Improve camera permission guidance and QR scanning in low light.

Support accessible font sizes, keyboard navigation and screen readers.

Add a visible confirmation after successful attendance submission.

Prevent duplicate submissions when students tap repeatedly or retry after a timeout.

Handle expired sessions and failed synchronization without losing pending work.

Test installation, updates and offline behavior on Android and iOS browsers.

An especially useful feature would be a one-tap student check-in screen that opens directly to the current eligible class, rather than making students navigate through multiple pages.

7. Notifications and communication
Introduce a configurable notification system with email first, followed by optional push notifications.

Class reminders before a scheduled session.

Alerts when attendance drops below an institutional threshold.

Notifications when a correction request is approved or rejected.

Alerts to teachers when a class roster changes.

Admin notifications for failed imports or suspicious activity.

Daily or weekly attendance summaries for authorized staff.

Include notification preferences, deduplication and retry handling. Avoid sending a notification for every small change if a daily digest would be more useful.

8. Features that could make the project stand out
These are larger opportunities once the core system is reliable.

edumerge - ERP Suite for Groups of Institutions

Multi-tenant SaaS edition

Let different schools or institutions run on one deployment with isolated data, separate administrators, institutional branding and configurable plans. Enforce tenant isolation at the data-access layer.



Academic timetable management

Manage semesters, academic years, rooms, recurring sessions, holidays, examination schedules and calendar exports.

BUKSU SIAS: Modern Student Information & · Built with Blink

Student information system integrations

Import enrollment data from existing systems and offer documented APIs or webhooks for approved integrations with learning-management systems.

Student Details Report | Bold Reports

Custom report builder

Allow authorized administrators to select courses, date ranges, attendance statuses and report columns, then save reusable report templates.

9. Developer experience and maintainability
Since this is an open-source PHP project, improving the developer workflow will help contributors deliver features safely.

Add a reproducible local environment using Docker Compose as an alternative to XAMPP or WAMP.

Introduce continuous integration to run automated tests on every pull request.

Add static analysis with PHPStan or Psalm and formatting checks.

Document the database schema and relationships.

Provide database migrations rather than relying exclusively on installer-time schema creation.

Add a development seed script with fake users, courses and attendance records.

Create an API reference with request examples, response formats and error codes.

Add a health-check endpoint for database connectivity and application status.

Document supported PHP versions and test against them.

Create a SECURITY.md, contribution guidelines and a process for reporting vulnerabilities.

Add upgrade checks and rollback guidance for database changes.

For an application that uses a custom PHP framework, I would prioritize regression tests, migrations and a clean development setup before attempting a major architectural rewrite.

10. My recommended development roadmap
Here is how I would sequence the work to keep each release useful.

Phase 1

Reliability and security
Foundation release

Automated tests for authentication, enrollment and attendance.

Server-side authorization and session revocation.

Better API errors and structured logs.

CSV validation and database integrity checks.

Phase 2

Smarter attendance
Core product upgrade

Expiring QR tokens and duplicate-check-in prevention.

Late-arrival rules and session controls.

Student attendance portal.

Correction requests and audit history.

Phase 3

Analytics and automation
Efficiency release

Attendance dashboards and configurable alerts.

Advanced reports and printable exports.

Bulk operations and timetable conflict checks.

Notification preferences.

Phase 4

Scale and integrations
Long-term expansion

Multi-campus support.

Offline-first attendance.

External API integrations.

Advanced reporting and configurable institutional policies.

11. Additional ideas for your features.md
You could add this section directly to your existing improvement document.

Writing


Additional Improvement Ideas
Attendance integrity and classroom workflows
Expiring QR tokens: Rotate attendance tokens and validate them against the active class session on the server.
Live attendance dashboard: Show real-time check-ins, attendance totals and students who have not checked in.
Late-arrival tracking: Calculate lateness using configurable grace periods and class start times.
Attendance correction requests: Allow students to request corrections and teachers to review them with an audit trail.
Offline-first attendance: Queue eligible attendance events locally and synchronize them safely when connectivity returns.
NFC attendance: Support NFC check-ins where compatible hardware and browsers are available, with QR fallback.
Student and teacher experience
Student attendance portal: Display course attendance percentages, missed sessions, history and correction requests.
Attendance alerts: Notify students when they approach configurable attendance limits.
Teacher timetable: Display upcoming classes, assigned courses and class rosters.
Substitute teacher access: Grant temporary, scoped permissions for authorized replacement teachers.
Low-bandwidth mode: Reduce network usage and improve usability on unreliable connections.
Security and data integrity
Fine-grained authorization: Enforce role, course and session permissions on every protected server request.
Session administration: Support active-session visibility, remote revocation and configurable expiration.
Security event monitoring: Log important authentication, permission, export and configuration events.
Attendance integrity: Prevent duplicate submissions and preserve the history of attendance corrections.
Dependency security: Monitor package vulnerabilities and establish a documented patching process.
Administration and analytics
Timetable conflict detection: Identify overlapping teacher, room and class schedules.
Advanced reporting: Provide reusable report templates, date filters, attendance trends and printable exports.
Import reconciliation: Preview changes, identify duplicates and download row-level validation errors before committing imports.
Configurable policies: Support institutional rules for late arrivals, excused absences, attendance thresholds and holidays.
Bulk operations: Simplify enrollment, account management and attendance corrections.
Developer experience and operations
Continuous integration: Run automated tests and code-quality checks on every change.
Database migrations: Track schema changes and provide documented upgrade procedures.
Reproducible development environment: Offer Docker-based setup and development seed data.
Operational health checks: Monitor application dependencies and provide safe diagnostics.
Security documentation: Publish vulnerability-reporting instructions, supported versions and deployment hardening guidance.
Long-term opportunities
Multi-campus support: Isolate institutional data and delegate administration.
External integrations: Provide documented APIs and webhooks for approved education systems.
Calendar integration: Export recurring class schedules to supported calendar applications.
Privacy controls: Introduce configurable retention, data export and deletion workflows.
Institutional customization: Support configurable branding, terminology and attendance policies.
Final recommendation
If I were maintaining this project, I would build three features first:

Secure, expiring QR attendance — directly improves the integrity of the core feature.

A student attendance portal with correction requests — benefits students and teachers immediately.

An audit trail with automated tests — protects the reliability of attendance data as the system evolves.

These deliver meaningful improvements without requiring you to turn the project into a full school-management platform immediately.

One important distinction: these are proposed enhancements based on the feature summary you shared. The actual implementation effort and compatibility still need to be checked against the repository's PHP code, database schema and existing API architecture.
documented APIs and webhooks for approved education systems.
Calendar integration: Export recurring class schedules to supported calendar applications.
Privacy controls: Introduce configurable retention, data export and deletion workflows.
Institutional customization: Support configurable branding, terminology and attendance policies.
Final recommendation
If I were maintaining this project, I would build three features first:

Secure, expiring QR attendance — directly improves the integrity of the core feature.

A student attendance portal with correction requests — benefits students and teachers immediately.

An audit trail with automated tests — protects the reliability of attendance data as the system evolves.

These deliver meaningful improvements without requiring you to turn the project into a full school-management platform immediately.

One important distinction: these are proposed enhancements based on the feature summary you shared. The actual implementation effort and compatibility still need to be checked against the repository's PHP code, database schema and existing API architecture.
