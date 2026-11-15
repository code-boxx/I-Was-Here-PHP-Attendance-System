<?php
// (A) PAGE META
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."html5-qrcode.min.js", "defer"],
  ["s", HOST_ASSETS."U-qr.js", "defer"]
]];

// (B) HTML
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (B1) HEADER -->
<h3 class="mb-0">TAKE ATTENDANCE</h3>
<div class="mb-3">
  Scan the QR code that the teacher has provided.
</div>

<!-- (B2) QR READER -->
<div id="reader" aria-describedby="camera-guidance"></div>
<div id="camera-guidance" class="alert alert-info" role="note">
  Allow camera access when prompted. If scanning does not start, check that
  this site uses HTTPS, close other camera apps, and use the browser's site
  permissions to allow the camera.
</div>
<p id="camera-status" class="small text-secondary" role="status" aria-live="polite">
  Camera scanner is starting.
</p>
<p id="offline-checkin-status" class="small text-secondary mt-3" role="status" aria-live="polite">
  No offline check-ins waiting to sync.
</p>
<p class="small text-secondary">
  If the connection drops, a check-in can be queued locally. Device time is unverified;
  queued requests do not count until a teacher reviews them.
</p>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>