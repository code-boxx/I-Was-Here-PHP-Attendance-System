<?php
$_PMETA = ["load" => [
  ["s", HOST_ASSETS."S-qr.js", "defer"],
  ["s", HOST_ASSETS."html5-qrcode.min.js", "defer"]
]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<!-- (A) HEADER -->
<h3 class="mb-0">TAKE ATTENDANCE</h3>
<div class="mb-3">
  Scan the QR code that the teacher has provided.
</div>

<!-- (B) QR READER -->
<div id="reader"></div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>