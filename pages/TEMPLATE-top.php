<!DOCTYPE html>
<html>
  <head>
    <!-- (A) HEAD -->
    <!-- (A1) TITLE, DESC, CHARSET, VIEWPORT -->
    <title><?=isset($_PMETA["title"])?$_PMETA["title"]:"I Was Here"?></title>
    <meta charset="utf-8">
    <meta name="description" content="<?=isset($_PMETA["desc"])?$_PMETA["desc"]:""?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.5">
    <meta name="robots" content="noindex">

    <!-- (A2) WEB APP & ICONS -->
    <link rel="icon" href="<?=HOST_ASSETS?>favicon.png" type="image/png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="theme-color" content="white">
    <link rel="apple-touch-icon" href="<?=HOST_ASSETS?>icon-512.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="I Was Here">
    <meta name="msapplication-TileImage" content="<?=HOST_ASSETS?>icon-512.png">
    <meta name="msapplication-TileColor" content="#ffffff">

    <?php if (isset($_SESSION["user"])) { ?>
    <!-- (A3) WEB APP MANIFEST -->
    <!-- https://web.dev/add-manifest/ -->
    <link rel="manifest" href="<?=HOST_BASE?>CB-manifest.json">

    <!-- (A4) SERVICE WORKER -->
    <script>if ("serviceWorker" in navigator) {
      navigator.serviceWorker.register("<?=HOST_BASE?>CB-worker.js", {scope: "<?=HOST_BASE_PATH?>"});
    }</script>
    <?php } ?>

    <!-- (A5) LIBRARIES & SCRIPTS -->
    <!-- https://getbootstrap.com/ -->
    <!-- https://fonts.google.com/icons -->
    <link rel="stylesheet" href="<?=HOST_ASSETS?>bootstrap.min.css">
    <script defer src="<?=HOST_ASSETS?>bootstrap.bundle.min.js"></script>
    <style>
    @font-face{font-family:"Material Icons";font-style:normal;font-weight:400;src:url(<?=HOST_ASSETS?>maticon.woff2) format("woff2");}
    .mi{font-family:"Material Icons";font-weight:400;font-style:normal;font-size:24px;letter-spacing:normal;text-transform:none;display:inline-block;white-space:nowrap;word-wrap:normal;direction:ltr;-webkit-font-feature-settings:"liga";-webkit-font-smoothing:antialiased}
    .mi-big{font-size:32px}.mi-smol{font-size:18px}
    #cb-loading{transition:opacity .3s}.cb-hide{opacity:0;visibility:hidden;height:0}.cb-pg-hide{display:none}
    #cb-loading{width:100vw;height:100vh;position:fixed;top:0;left:0;z-index:999;background:rgba(0,0,0,.7)}#cb-loading .spinner-border{width:80px;height:80px}
    .head{background:#ddd}.zebra .d-flex{background:#fff;margin-bottom:10px}.zebra .d-flex:nth-child(odd){background-color:#f1f1f1}.pagination{border:1px solid #d0e8ff;background:#f0f8ff}
    #cb-body,body{min-height:100vh}#cb-toggle{display:none}#cb-side{width:155px;flex-shrink:0}#cb-side a{color:#fff}#cb-side .mi{color:#6a6a6a}@media screen and (max-width:768px){#cb-toggle{display:block}#cb-side{display:none}#cb-side.show{display:block}}#reader{max-width:380px}
    </style>
    <script>var cbhost={base:"<?=HOST_BASE?>",basepath:"<?=HOST_BASE_PATH?>",api:"<?=HOST_API_BASE?>",assets:"<?=HOST_ASSETS?>"};</script>
    <script defer src="<?=HOST_ASSETS?>PAGE-cb.js"></script>

    <!-- (A6) ADDITIONAL SCRIPTS -->
    <?php if (isset($_PMETA["load"])) { foreach ($_PMETA["load"] as $load) {
      if ($load[0]=="s") {
        printf("<script src='%s'%s></script>", $load[1], isset($load[2]) ? " ".$load[2] : "");
      } else {
        printf("<link rel='stylesheet' href='%s'>", $load[1]);
      }
    }}
    if (isset($_PMETA)) { unset($_PMETA); } ?>
  </head>
  <body class="bg-light">
    <!-- (B) COMMON SHARED INTERFACE -->
    <!-- (B1) NOW LOADING -->
    <div id="cb-loading" class="d-flex justify-content-center align-items-center cb-hide">
      <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <!-- (B2) TOAST MESSAGE -->
    <div class="position-fixed top-50 start-50 translate-middle" style="z-index:11">
      <div id="cb-toast" class="toast hide" role="alert">
        <div class="toast-header">
          <span id="cb-toast-icon" class="mi"></span>
          <strong id="cb-toast-head" class="me-auto p-1"></strong>
          <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
        </div>
        <div id="cb-toast-body" class="toast-body bg-light"></div>
      </div>
    </div>

    <!-- (B3) MODAL DIALOG BOX -->
    <div id="cb-modal" class="modal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header">
        <h5 id="cb-modal-head" class="modal-title"></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div id="cb-modal-body" class="modal-body"></div>
      <div id="cb-modal-foot" class="modal-footer">
      </div>
    </div></div></div>

    <!-- (C) MAIN INTERFACE -->
    <div id="cb-body" class="d-flex">
      <?php if (isset($_SESSION["user"])) { ?>
      <!-- (C1) LEFT SIDEBAR -->
      <nav id="cb-side" class="bg-dark text-white p-2"><ul class="navbar-nav"><?php
        require PATH_PAGES . "TEMPLATE-".$_SESSION["user"]["user_level"]."-menu.php";
      ?></ul></nav>
      <?php } ?>

      <!-- (C2) RIGHT CONTENTS -->
      <div class="flex-grow-1">
        <?php if (isset($_SESSION["user"])) { ?>
          <!-- (C2-1) TOP NAV -->
          <nav class="navbar navbar-expand-lg navbar-dark bg-dark"><div class="container-fluid">
          <button id="cb-toggle" class="navbar-toggler btn btn-sm mi text-white" onclick="cb.toggle()">
            menu
          </button>
          <div class="navbar-nav me-auto mb-2 mb-lg-0"></div>

          <div class="d-flex align-items-center">
            <div class="dropdown">
              <button class="btn btn-sm text-white mi" type="button" data-bs-toggle="dropdown">
                person
              </button>
              <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                <li class="dropdown-header">
                  <?=$_SESSION["user"]["user_name"]?><br>
                  <?=$_SESSION["user"]["user_email"]?>
                </li>
                <li><a class="dropdown-item" href="<?=HOST_BASE?>account">
                  <i class="mi mi-smol">person</i> My Account
                </a></li>
                <li class="dropdown-item text-warning" onclick="cb.bye()">
                  <i class="mi mi-smol">logout</i> Logout
                </li>
              </ul>
            </div>
          </div>
        </div></nav>
        <?php } ?>

        <!-- (C2-2) CONTENTS -->
        <div class="p-4">
          <div id="cb-page-1">