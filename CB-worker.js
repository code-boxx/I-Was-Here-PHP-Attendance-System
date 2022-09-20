// (A) FILES TO CACHE
const cName = "cb-pwa",
cFiles = [
  "CB-manifest.json",
  "assets/ico-512.png",
  "assets/favicon.png",
  "assets/book.jpg",
  "assets/maticon.woff2",
  "assets/bootstrap.bundle.min.js",
  "assets/bootstrap.bundle.min.js.map",
  "assets/bootstrap.min.css",
  "assets/bootstrap.min.css.map",
  "assets/CB-selector.css",
  "assets/CB-selector.js",
  "assets/html5-qrcode.min.js",
  "assets/qrcode.min.js",
  "assets/PAGE-account.js",
  "assets/PAGE-cb.js",
  "assets/PAGE-forgot.js",
  "assets/PAGE-login.js",
  "assets/A-class.js",
  "assets/A-class-attend.js",
  "assets/A-course.js",
  "assets/A-course-import.js",
  "assets/A-course-user.js",
  "assets/A-course-user-import.js",
  "assets/A-reports.js",
  "assets/A-settings.js",
  "assets/A-users.js",
  "assets/A-users-import.js",
  "assets/T-attend.js",
  "assets/T-classes.js",
  "assets/S-classes.js",
  "assets/S-qr.js"
  // @TODO - ADD MORE OF YOUR OWN TO CACHE
];

// (B) CREATE/INSTALL CACHE
self.addEventListener("install", evt => {
  evt.waitUntil(
    caches.open(cName)
    .then(cache => cache.addAll(cFiles))
    .catch(err => console.error(err))
  );
});

// (C) CACHE STRATEGY
self.addEventListener("fetch", evt => {
  // (C1) LOAD FROM CACHE FIRST, FALLBACK TO NETWORK IF NOT FOUND
  evt.respondWith(
    caches.match(evt.request).then(res => res || fetch(evt.request))
  );
});