// (A) CREATE/INSTALL CACHE
self.addEventListener("install", evt => {
  self.skipWaiting();
  evt.waitUntil(
    caches.open("IWasHere")
    .then(cache => cache.addAll([
      // (A1) BOOTSTRAP
      "assets/bootstrap.bundle.min.js",
      "assets/bootstrap.bundle.min.js.map",
      "assets/bootstrap.min.css",
      "assets/bootstrap.min.css.map",
      // (A2) ICONS + IMAGES
      "assets/ico-512.png",
      "assets/favicon.png",
      "assets/login.jpg",
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