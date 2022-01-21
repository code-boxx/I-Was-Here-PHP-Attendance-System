// (A) FILES TO CACHE
const cName = "iwashere",
cFiles = [
  // (A1) BOOTSTRAP
  "assets/bootstrap.bundle.min.js",
  "assets/bootstrap.bundle.min.js.map",
  "assets/bootstrap.min.css",
  "assets/bootstrap.min.css.map",
  // (A2) ICONS + IMAGES
  "assets/favicon.png",
  "assets/ico-512.png",
  "assets/desk.jpg",
  "assets/question.jpg",
  // (A3) COMMON INTERFACE
  "assets/CB-selector.css",
  "assets/CB-selector.js",
  "assets/PAGE-cb.js",
  "assets/maticon.woff2",
  // (A4) PAGES
  "assets/PAGE-login.js",
  "assets/PAGE-forgot.js",
  "assets/PAGE-my-account.js",
  // (A5) ADMIN
  "assets/ADMIN-course-attend.js",
  "assets/ADMIN-course-class.js",
  "assets/ADMIN-course-user-import.js",
  "assets/ADMIN-course-user.js",
  "assets/ADMIN-course.js",
  "assets/ADMIN-settings.js",
  "assets/ADMIN-users-import.js",
  "assets/ADMIN-users.js",
  // (A6) TEACHER
  "assets/TEACHER-attend.js",
  "assets/TEACHER-classes.js",
  // (A7) STUDENT
  "assets/STUDENT-class.js"
];

// (B) CREATE/INSTALL CACHE
self.addEventListener("install", (evt) => {
  evt.waitUntil(
    caches.open(cName)
    .then((cache) => { return cache.addAll(cFiles); })
    .catch((err) => { console.error(err) })
  );
});

// (C) LOAD FROM CACHE FIRST, FALLBACK TO NETWORK IF NOT FOUND
self.addEventListener("fetch", (evt) => {
  evt.respondWith(
    caches.match(evt.request)
    .then((res) => { return res || fetch(evt.request); })
  );
});
