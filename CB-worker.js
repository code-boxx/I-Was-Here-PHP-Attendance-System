// (A) FILES TO CACHE
const cName = "iwashere",
cFiles = [
  // (A1) BOOTSTRAP
  "assets/bootstrap.bundle.min.js",
  "assets/bootstrap.bundle.min.js.map",
  "assets/bootstrap.min.css",
  "assets/bootstrap.min.css.map",
  // (A2) ICONS + IMAGES
  "assets/ico-512.png",
  "assets/favicon.png",
  "assets/book.jpg",
  // (A3) COMMON INTERFACE
  "assets/PAGE-cb.js",
  "assets/CB-selector.css",
  "assets/CB-selector.js",
  "assets/maticon.woff2",
  "CB-manifest.json",
  // (A4) HTML QR CODE + SCANNER
  "assets/html5-qrcode.min.js",
  "assets/qrcode.min.js",
  // (A5) PAGES
  "assets/PAGE-account.js",
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
self.addEventListener("install", evt => evt.waitUntil(
  caches.open(cName)
  .then(cache => cache.addAll(cFiles))
  .catch(err => console.error(err))
));

// (C) LOAD FROM CACHE FIRST, FALLBACK TO NETWORK IF NOT FOUND
self.addEventListener("fetch", evt => evt.respondWith(
  caches.match(evt.request).then(res => res || fetch(evt.request))
));