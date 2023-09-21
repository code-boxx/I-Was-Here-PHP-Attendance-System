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
<div id="reader"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>