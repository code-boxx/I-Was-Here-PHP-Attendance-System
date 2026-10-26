<?php
$_CORE->ucheck(["A", "T"]);
$_CORE->load("Classes");
$class = $_CORE->Classes->get($_GET["c"] ?? 0);
if (!is_array($class)) { exit("Invalid request"); }
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Attendance QR Session</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
      * { font-family: Arial, sans-serif; box-sizing: border-box; }
      body { margin: 0; padding: 20px; }
      #qrwrap { width: min(100%, 340px); padding: 20px; border: 1px solid #ddd; }
      #qrcode { min-height: 256px; display: grid; place-items: center; }
      #qrcode img { max-width: 100%; }
      #session-status { margin: 12px 0; }
      #session-message { min-height: 1.5em; }
    </style>
    <script>
      var qrClassId = <?=json_encode((int)$class["class_id"])?>;
      var qrApiBase = <?=json_encode(HOST_API_BASE)?>;
    </script>
    <script defer src="<?=HOST_ASSETS?>qrcode.min.js"></script>
    <script defer src="<?=HOST_ASSETS?>TA-classqr.js"></script>
  </head>
  <body>
    <main id="qrwrap">
      <h1>Attendance QR session</h1>
      <div id="qrcourse">[<?=htmlspecialchars($class["course_code"], ENT_QUOTES, "UTF-8")?>] <?=htmlspecialchars($class["course_name"], ENT_QUOTES, "UTF-8")?></div>
      <p><?=$class["cd"]?></p>
      <p id="session-status" role="status">Checking session status…</p>
      <div id="qrcode" aria-label="Rotating attendance QR code"></div>
      <p id="qr-expiry"></p>
      <p id="session-message" role="alert"></p>
      <button id="session-open" type="button" onclick="qrSession.open()">Open attendance</button>
      <button id="session-close" type="button" onclick="qrSession.close()" hidden>Close attendance</button>
    </main>
  </body>
</html>
