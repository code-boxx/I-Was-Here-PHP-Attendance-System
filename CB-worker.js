// (A) CREATE/INSTALL CACHE
self.addEventListener("install", evt => {
  self.skipWaiting();
  evt.waitUntil(
    caches.open("IWASHERE")
    .then(cache => cache.addAll([
      // (A1) ADMIN
      "assets/A-classes.js",
      "assets/A-course.js",
      "assets/A-course-user.js",
      "assets/A-import.js",
      "assets/A-reports.js",
      "assets/A-settings.js",
      "assets/A-users.js",
      "assets/A-users-nfc.js",

      // (A2) BOOTSTRAP
      "assets/bootstrap.bundle.min.js",
      "assets/bootstrap.bundle.min.js.map",
      "assets/bootstrap.min.css",
      "assets/bootstrap.min.css.map",

      // (A3) COMMON INTERFACE
      "assets/CB-autocomplete.js",
      "assets/csv.min.js",
      "assets/html5-qrcode.min.js",
      "assets/icomoon.woff",
      "assets/PAGE-cb.js",
      "assets/PAGE-cb.css",
      "assets/qrcode.min.js",
      "CB-manifest.json",

      // (A4) ICONS + IMAGES
      "assets/favicon.png",
      "assets/ico-512.png",
      "assets/users.webp",

      // (A5) PAGES
      "assets/PAGE-forgot.js",
      "assets/PAGE-login.js",
      "assets/PAGE-login-nfc.js",
      "assets/PAGE-login-wa.js",
      "assets/PAGE-myaccount.js",
      "assets/PAGE-nfc.js",
      "assets/PAGE-wa.js",
      "assets/PAGE-wa-helper.js",
      
      // (A6) TEACHER & STUDENT
      "assets/TA-attend.js",
      "assets/T-classes.js",
      "assets/U-classes.js",
      "assets/U-qr.js",
    ]))
    .catch(err => console.error(err))
  );
});

// (B) CLAIM CONTROL INSTANTLY
self.addEventListener("activate", evt => self.clients.claim());

// (C) LOAD FROM CACHE FIRST, FALLBACK TO NETWORK IF NOT FOUND
self.addEventListener("fetch", evt => evt.respondWith(
  caches.match(evt.request).then(res => res || fetch(evt.request))
));

// (D) LISTEN TO PUSH NOTIFICATIONS
self.addEventListener("push", evt => {
  const data = evt.data.json();
  self.registration.showNotification(data.title, {
    body: data.body,
    icon: data.icon,
    image: data.image
  });
});