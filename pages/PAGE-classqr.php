<?php
// (A) ADMIN + TEACHER ONLY
if (!isset($_SESS["user"]) || ($_SESS["user"]["user_role"]!="A" && $_SESS["user"]["user_role"]!="T")) {
  $_CORE->redirect();
}

// (B) GET CLASS
$_CORE->load("Classes");
$class = $_CORE->Classes->get($_GET["c"]);
if (!is_array($class)) { exit("Invalid request"); }

// (C) HTML PAGE ?>
<!DOCTYPE html>
<html>
  <head>
    <title>QR Code Generator</title>
    <style>
    * {
      font-family: Arial, sans-serif;
      box-sizing: border-box;
    }
    #qrwrap {
      width: 300px;
      padding: 20px;
      border: 1px solid #e1e1e1;
    }
    #qrcourse {
      margin-top: 20px;
      color: #f00;
      font-size: 0.9em;
    }
    #qrtime {
      margin-top: 5px;
      text-transform: uppercase;
      font-size: 1em;
    }
    </style>
    <script src="<?=HOST_ASSETS?>qrcode.min.js"></script>
    <script>
    window.onload = () => {
      new QRCode("qrcode", {
        text: '<?=json_encode([
          "i" => $class["class_id"],
          "h" => $class["class_hash"]
        ])?>',
        width: 256, height: 256,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
      });
    };
    </script>
  </head>
  <body>
    <div id="qrwrap">
      <div id="qrcode"></div>
      <div id="qrcourse">[<?=$class["course_code"]?>] <?=$class["course_name"]?></div>
      <div id="qrtime"><?=$class["cd"]?></div>
    </div>
  </body>
</html>