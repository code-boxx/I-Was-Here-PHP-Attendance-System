<?php
$_PMETA = ["title" => "IcoMoon List"];
require PATH_PAGES . "TEMPLATE-top.php";

/* (X) EXTRACT LIST FROM CSS *
if (file_exists("test.html")) { unlink("test.html"); }
$all = file(PATH_BASE . "style.css");
foreach ($all as $i=>$line) {
  $before = strpos($line, ":before");
  if ($before !== false) {
    $css = substr($line, 1, $before-1);
    $code = str_replace(["\";", "\r", "\n"], "", substr($all[$i+1], 13));
    file_put_contents("test.html",
      sprintf('<div class="d-flex p-2 bg-white border"><i class="imoon %s"></i><div class="flex-grow-1"><h5 class="mb-0">%s</h4><div class="text-secondary">%s</div></div></div>',
      $css, $css, $code
    ), FILE_APPEND);
    file_put_contents("test.html", "\r\n", FILE_APPEND);
  }
}
exit(); */ ?>
<!-- (A) HEADER -->
<h1 class="mb-0">ICOMOON ICONS LIST</h1>
<div class="text-secondary mb-3">for your convenience</div>
<style>
#moonlist {
  display : grid;
  grid-template-columns : repeat(2, minmax(0, 1fr));
  grid-gap: 10px;
}
#moonlist .imoon {
  display: flex; align-items:center;
  font-size: 40px; width: 60px;
  color: #555;
}
</style>

<div id="moonlist" class="mb-3">
<div class="d-flex p-2 bg-white border"><i class="imoon icon-home3"></i><div class="flex-grow-1"><h5 class="mb-0">icon-home3</h4><div class="text-secondary">e902</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-pencil"></i><div class="flex-grow-1"><h5 class="mb-0">icon-pencil</h4><div class="text-secondary">e905</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-image"></i><div class="flex-grow-1"><h5 class="mb-0">icon-image</h4><div class="text-secondary">e90d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-images"></i><div class="flex-grow-1"><h5 class="mb-0">icon-images</h4><div class="text-secondary">e90e</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-camera"></i><div class="flex-grow-1"><h5 class="mb-0">icon-camera</h4><div class="text-secondary">e90f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-headphones"></i><div class="flex-grow-1"><h5 class="mb-0">icon-headphones</h4><div class="text-secondary">e910</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-music"></i><div class="flex-grow-1"><h5 class="mb-0">icon-music</h4><div class="text-secondary">e911</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-play"></i><div class="flex-grow-1"><h5 class="mb-0">icon-play</h4><div class="text-secondary">e912</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-dice"></i><div class="flex-grow-1"><h5 class="mb-0">icon-dice</h4><div class="text-secondary">e915</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-bullhorn"></i><div class="flex-grow-1"><h5 class="mb-0">icon-bullhorn</h4><div class="text-secondary">e91a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-connection"></i><div class="flex-grow-1"><h5 class="mb-0">icon-connection</h4><div class="text-secondary">e91b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-feed"></i><div class="flex-grow-1"><h5 class="mb-0">icon-feed</h4><div class="text-secondary">e91d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-mic"></i><div class="flex-grow-1"><h5 class="mb-0">icon-mic</h4><div class="text-secondary">e91e</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-file-empty"></i><div class="flex-grow-1"><h5 class="mb-0">icon-file-empty</h4><div class="text-secondary">e924</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-files-empty"></i><div class="flex-grow-1"><h5 class="mb-0">icon-files-empty</h4><div class="text-secondary">e925</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-file-text2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-file-text2</h4><div class="text-secondary">e926</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-file-zip"></i><div class="flex-grow-1"><h5 class="mb-0">icon-file-zip</h4><div class="text-secondary">e92b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-copy"></i><div class="flex-grow-1"><h5 class="mb-0">icon-copy</h4><div class="text-secondary">e92c</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-paste"></i><div class="flex-grow-1"><h5 class="mb-0">icon-paste</h4><div class="text-secondary">e92d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-stack"></i><div class="flex-grow-1"><h5 class="mb-0">icon-stack</h4><div class="text-secondary">e92e</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-folder"></i><div class="flex-grow-1"><h5 class="mb-0">icon-folder</h4><div class="text-secondary">e92f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-folder-open"></i><div class="flex-grow-1"><h5 class="mb-0">icon-folder-open</h4><div class="text-secondary">e930</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-folder-plus"></i><div class="flex-grow-1"><h5 class="mb-0">icon-folder-plus</h4><div class="text-secondary">e931</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-folder-minus"></i><div class="flex-grow-1"><h5 class="mb-0">icon-folder-minus</h4><div class="text-secondary">e932</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-folder-download"></i><div class="flex-grow-1"><h5 class="mb-0">icon-folder-download</h4><div class="text-secondary">e933</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-folder-upload"></i><div class="flex-grow-1"><h5 class="mb-0">icon-folder-upload</h4><div class="text-secondary">e934</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-price-tag"></i><div class="flex-grow-1"><h5 class="mb-0">icon-price-tag</h4><div class="text-secondary">e935</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-barcode"></i><div class="flex-grow-1"><h5 class="mb-0">icon-barcode</h4><div class="text-secondary">e937</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-qrcode"></i><div class="flex-grow-1"><h5 class="mb-0">icon-qrcode</h4><div class="text-secondary">e938</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-cart"></i><div class="flex-grow-1"><h5 class="mb-0">icon-cart</h4><div class="text-secondary">e93a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-coin-dollar"></i><div class="flex-grow-1"><h5 class="mb-0">icon-coin-dollar</h4><div class="text-secondary">e93b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-credit-card"></i><div class="flex-grow-1"><h5 class="mb-0">icon-credit-card</h4><div class="text-secondary">e93f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-phone"></i><div class="flex-grow-1"><h5 class="mb-0">icon-phone</h4><div class="text-secondary">e942</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-address-book"></i><div class="flex-grow-1"><h5 class="mb-0">icon-address-book</h4><div class="text-secondary">e944</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-envelop"></i><div class="flex-grow-1"><h5 class="mb-0">icon-envelop</h4><div class="text-secondary">e945</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-pushpin"></i><div class="flex-grow-1"><h5 class="mb-0">icon-pushpin</h4><div class="text-secondary">e946</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-location"></i><div class="flex-grow-1"><h5 class="mb-0">icon-location</h4><div class="text-secondary">e947</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-map"></i><div class="flex-grow-1"><h5 class="mb-0">icon-map</h4><div class="text-secondary">e94b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-history"></i><div class="flex-grow-1"><h5 class="mb-0">icon-history</h4><div class="text-secondary">e94d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-clock2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-clock2</h4><div class="text-secondary">e94f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-alarm"></i><div class="flex-grow-1"><h5 class="mb-0">icon-alarm</h4><div class="text-secondary">e950</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-bell"></i><div class="flex-grow-1"><h5 class="mb-0">icon-bell</h4><div class="text-secondary">e951</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-calendar"></i><div class="flex-grow-1"><h5 class="mb-0">icon-calendar</h4><div class="text-secondary">e953</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-printer"></i><div class="flex-grow-1"><h5 class="mb-0">icon-printer</h4><div class="text-secondary">e954</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-keyboard"></i><div class="flex-grow-1"><h5 class="mb-0">icon-keyboard</h4><div class="text-secondary">e955</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-display"></i><div class="flex-grow-1"><h5 class="mb-0">icon-display</h4><div class="text-secondary">e956</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-laptop"></i><div class="flex-grow-1"><h5 class="mb-0">icon-laptop</h4><div class="text-secondary">e957</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-mobile"></i><div class="flex-grow-1"><h5 class="mb-0">icon-mobile</h4><div class="text-secondary">e958</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-tablet"></i><div class="flex-grow-1"><h5 class="mb-0">icon-tablet</h4><div class="text-secondary">e95a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-floppy-disk"></i><div class="flex-grow-1"><h5 class="mb-0">icon-floppy-disk</h4><div class="text-secondary">e962</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-drive"></i><div class="flex-grow-1"><h5 class="mb-0">icon-drive</h4><div class="text-secondary">e963</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-database"></i><div class="flex-grow-1"><h5 class="mb-0">icon-database</h4><div class="text-secondary">e964</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-undo"></i><div class="flex-grow-1"><h5 class="mb-0">icon-undo</h4><div class="text-secondary">e965</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-redo"></i><div class="flex-grow-1"><h5 class="mb-0">icon-redo</h4><div class="text-secondary">e966</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-undo2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-undo2</h4><div class="text-secondary">e967</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-redo2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-redo2</h4><div class="text-secondary">e968</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-forward"></i><div class="flex-grow-1"><h5 class="mb-0">icon-forward</h4><div class="text-secondary">e969</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-reply"></i><div class="flex-grow-1"><h5 class="mb-0">icon-reply</h4><div class="text-secondary">e96a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-bubble"></i><div class="flex-grow-1"><h5 class="mb-0">icon-bubble</h4><div class="text-secondary">e96b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-user"></i><div class="flex-grow-1"><h5 class="mb-0">icon-user</h4><div class="text-secondary">e971</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-users"></i><div class="flex-grow-1"><h5 class="mb-0">icon-users</h4><div class="text-secondary">e972</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-user-plus"></i><div class="flex-grow-1"><h5 class="mb-0">icon-user-plus</h4><div class="text-secondary">e973</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-user-minus"></i><div class="flex-grow-1"><h5 class="mb-0">icon-user-minus</h4><div class="text-secondary">e974</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-user-check"></i><div class="flex-grow-1"><h5 class="mb-0">icon-user-check</h4><div class="text-secondary">e975</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-quotes-left"></i><div class="flex-grow-1"><h5 class="mb-0">icon-quotes-left</h4><div class="text-secondary">e977</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-quotes-right"></i><div class="flex-grow-1"><h5 class="mb-0">icon-quotes-right</h4><div class="text-secondary">e978</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-hour-glass"></i><div class="flex-grow-1"><h5 class="mb-0">icon-hour-glass</h4><div class="text-secondary">e979</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-spinner"></i><div class="flex-grow-1"><h5 class="mb-0">icon-spinner</h4><div class="text-secondary">e97a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-spinner11"></i><div class="flex-grow-1"><h5 class="mb-0">icon-spinner11</h4><div class="text-secondary">e984</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-search"></i><div class="flex-grow-1"><h5 class="mb-0">icon-search</h4><div class="text-secondary">e986</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-zoom-in"></i><div class="flex-grow-1"><h5 class="mb-0">icon-zoom-in</h4><div class="text-secondary">e987</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-zoom-out"></i><div class="flex-grow-1"><h5 class="mb-0">icon-zoom-out</h4><div class="text-secondary">e988</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-enlarge"></i><div class="flex-grow-1"><h5 class="mb-0">icon-enlarge</h4><div class="text-secondary">e989</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-shrink"></i><div class="flex-grow-1"><h5 class="mb-0">icon-shrink</h4><div class="text-secondary">e98a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-enlarge2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-enlarge2</h4><div class="text-secondary">e98b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-shrink2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-shrink2</h4><div class="text-secondary">e98c</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-key"></i><div class="flex-grow-1"><h5 class="mb-0">icon-key</h4><div class="text-secondary">e98d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-lock"></i><div class="flex-grow-1"><h5 class="mb-0">icon-lock</h4><div class="text-secondary">e98f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-unlocked"></i><div class="flex-grow-1"><h5 class="mb-0">icon-unlocked</h4><div class="text-secondary">e990</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-wrench"></i><div class="flex-grow-1"><h5 class="mb-0">icon-wrench</h4><div class="text-secondary">e991</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-equalizer"></i><div class="flex-grow-1"><h5 class="mb-0">icon-equalizer</h4><div class="text-secondary">e992</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-cog"></i><div class="flex-grow-1"><h5 class="mb-0">icon-cog</h4><div class="text-secondary">e994</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-hammer"></i><div class="flex-grow-1"><h5 class="mb-0">icon-hammer</h4><div class="text-secondary">e996</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-magic-wand"></i><div class="flex-grow-1"><h5 class="mb-0">icon-magic-wand</h4><div class="text-secondary">e997</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-bug"></i><div class="flex-grow-1"><h5 class="mb-0">icon-bug</h4><div class="text-secondary">e999</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-pie-chart"></i><div class="flex-grow-1"><h5 class="mb-0">icon-pie-chart</h4><div class="text-secondary">e99a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-stats-dots"></i><div class="flex-grow-1"><h5 class="mb-0">icon-stats-dots</h4><div class="text-secondary">e99b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-stats-bars2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-stats-bars2</h4><div class="text-secondary">e99d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-gift"></i><div class="flex-grow-1"><h5 class="mb-0">icon-gift</h4><div class="text-secondary">e99f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-glass"></i><div class="flex-grow-1"><h5 class="mb-0">icon-glass</h4><div class="text-secondary">e9a0</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-mug"></i><div class="flex-grow-1"><h5 class="mb-0">icon-mug</h4><div class="text-secondary">e9a2</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-rocket"></i><div class="flex-grow-1"><h5 class="mb-0">icon-rocket</h4><div class="text-secondary">e9a5</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-meter"></i><div class="flex-grow-1"><h5 class="mb-0">icon-meter</h4><div class="text-secondary">e9a6</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-hammer2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-hammer2</h4><div class="text-secondary">e9a8</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-fire"></i><div class="flex-grow-1"><h5 class="mb-0">icon-fire</h4><div class="text-secondary">e9a9</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-bin2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-bin2</h4><div class="text-secondary">e9ad</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-briefcase"></i><div class="flex-grow-1"><h5 class="mb-0">icon-briefcase</h4><div class="text-secondary">e9ae</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-airplane"></i><div class="flex-grow-1"><h5 class="mb-0">icon-airplane</h4><div class="text-secondary">e9af</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-truck"></i><div class="flex-grow-1"><h5 class="mb-0">icon-truck</h4><div class="text-secondary">e9b0</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-shield"></i><div class="flex-grow-1"><h5 class="mb-0">icon-shield</h4><div class="text-secondary">e9b4</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-power"></i><div class="flex-grow-1"><h5 class="mb-0">icon-power</h4><div class="text-secondary">e9b5</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-switch"></i><div class="flex-grow-1"><h5 class="mb-0">icon-switch</h4><div class="text-secondary">e9b6</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-power-cord"></i><div class="flex-grow-1"><h5 class="mb-0">icon-power-cord</h4><div class="text-secondary">e9b7</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-clipboard"></i><div class="flex-grow-1"><h5 class="mb-0">icon-clipboard</h4><div class="text-secondary">e9b8</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-list-numbered"></i><div class="flex-grow-1"><h5 class="mb-0">icon-list-numbered</h4><div class="text-secondary">e9b9</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-list"></i><div class="flex-grow-1"><h5 class="mb-0">icon-list</h4><div class="text-secondary">e9ba</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-list2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-list2</h4><div class="text-secondary">e9bb</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-tree"></i><div class="flex-grow-1"><h5 class="mb-0">icon-tree</h4><div class="text-secondary">e9bc</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-menu"></i><div class="flex-grow-1"><h5 class="mb-0">icon-menu</h4><div class="text-secondary">e9bd</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-menu2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-menu2</h4><div class="text-secondary">e9be</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-cloud"></i><div class="flex-grow-1"><h5 class="mb-0">icon-cloud</h4><div class="text-secondary">e9c1</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-cloud-download"></i><div class="flex-grow-1"><h5 class="mb-0">icon-cloud-download</h4><div class="text-secondary">e9c2</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-cloud-upload"></i><div class="flex-grow-1"><h5 class="mb-0">icon-cloud-upload</h4><div class="text-secondary">e9c3</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-cloud-check"></i><div class="flex-grow-1"><h5 class="mb-0">icon-cloud-check</h4><div class="text-secondary">e9c4</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-download2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-download2</h4><div class="text-secondary">e9c5</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-upload2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-upload2</h4><div class="text-secondary">e9c6</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-download3"></i><div class="flex-grow-1"><h5 class="mb-0">icon-download3</h4><div class="text-secondary">e9c7</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-upload3"></i><div class="flex-grow-1"><h5 class="mb-0">icon-upload3</h4><div class="text-secondary">e9c8</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-sphere"></i><div class="flex-grow-1"><h5 class="mb-0">icon-sphere</h4><div class="text-secondary">e9c9</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-earth"></i><div class="flex-grow-1"><h5 class="mb-0">icon-earth</h4><div class="text-secondary">e9ca</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-link"></i><div class="flex-grow-1"><h5 class="mb-0">icon-link</h4><div class="text-secondary">e9cb</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-flag"></i><div class="flex-grow-1"><h5 class="mb-0">icon-flag</h4><div class="text-secondary">e9cc</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-attachment"></i><div class="flex-grow-1"><h5 class="mb-0">icon-attachment</h4><div class="text-secondary">e9cd</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-eye"></i><div class="flex-grow-1"><h5 class="mb-0">icon-eye</h4><div class="text-secondary">e9ce</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-eye-plus"></i><div class="flex-grow-1"><h5 class="mb-0">icon-eye-plus</h4><div class="text-secondary">e9cf</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-eye-minus"></i><div class="flex-grow-1"><h5 class="mb-0">icon-eye-minus</h4><div class="text-secondary">e9d0</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-eye-blocked"></i><div class="flex-grow-1"><h5 class="mb-0">icon-eye-blocked</h4><div class="text-secondary">e9d1</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-bookmark"></i><div class="flex-grow-1"><h5 class="mb-0">icon-bookmark</h4><div class="text-secondary">e9d2</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-star-empty"></i><div class="flex-grow-1"><h5 class="mb-0">icon-star-empty</h4><div class="text-secondary">e9d7</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-star-half"></i><div class="flex-grow-1"><h5 class="mb-0">icon-star-half</h4><div class="text-secondary">e9d8</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-star-full"></i><div class="flex-grow-1"><h5 class="mb-0">icon-star-full</h4><div class="text-secondary">e9d9</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-heart"></i><div class="flex-grow-1"><h5 class="mb-0">icon-heart</h4><div class="text-secondary">e9da</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-heart-broken"></i><div class="flex-grow-1"><h5 class="mb-0">icon-heart-broken</h4><div class="text-secondary">e9db</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-smile2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-smile2</h4><div class="text-secondary">e9e2</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-sad2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-sad2</h4><div class="text-secondary">e9e6</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-warning"></i><div class="flex-grow-1"><h5 class="mb-0">icon-warning</h4><div class="text-secondary">ea07</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-notification"></i><div class="flex-grow-1"><h5 class="mb-0">icon-notification</h4><div class="text-secondary">ea08</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-question"></i><div class="flex-grow-1"><h5 class="mb-0">icon-question</h4><div class="text-secondary">ea09</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-plus"></i><div class="flex-grow-1"><h5 class="mb-0">icon-plus</h4><div class="text-secondary">ea0a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-minus"></i><div class="flex-grow-1"><h5 class="mb-0">icon-minus</h4><div class="text-secondary">ea0b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-info"></i><div class="flex-grow-1"><h5 class="mb-0">icon-info</h4><div class="text-secondary">ea0c</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-cancel-circle"></i><div class="flex-grow-1"><h5 class="mb-0">icon-cancel-circle</h4><div class="text-secondary">ea0d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-blocked"></i><div class="flex-grow-1"><h5 class="mb-0">icon-blocked</h4><div class="text-secondary">ea0e</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-cross"></i><div class="flex-grow-1"><h5 class="mb-0">icon-cross</h4><div class="text-secondary">ea0f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-checkmark"></i><div class="flex-grow-1"><h5 class="mb-0">icon-checkmark</h4><div class="text-secondary">ea10</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-enter"></i><div class="flex-grow-1"><h5 class="mb-0">icon-enter</h4><div class="text-secondary">ea13</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-exit"></i><div class="flex-grow-1"><h5 class="mb-0">icon-exit</h4><div class="text-secondary">ea14</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-play3"></i><div class="flex-grow-1"><h5 class="mb-0">icon-play3</h4><div class="text-secondary">ea1c</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-pause2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-pause2</h4><div class="text-secondary">ea1d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-stop2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-stop2</h4><div class="text-secondary">ea1e</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-backward2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-backward2</h4><div class="text-secondary">ea1f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-forward3"></i><div class="flex-grow-1"><h5 class="mb-0">icon-forward3</h4><div class="text-secondary">ea20</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-first"></i><div class="flex-grow-1"><h5 class="mb-0">icon-first</h4><div class="text-secondary">ea21</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-last"></i><div class="flex-grow-1"><h5 class="mb-0">icon-last</h4><div class="text-secondary">ea22</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-previous2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-previous2</h4><div class="text-secondary">ea23</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-next2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-next2</h4><div class="text-secondary">ea24</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-eject"></i><div class="flex-grow-1"><h5 class="mb-0">icon-eject</h4><div class="text-secondary">ea25</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-volume-high"></i><div class="flex-grow-1"><h5 class="mb-0">icon-volume-high</h4><div class="text-secondary">ea26</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-volume-medium"></i><div class="flex-grow-1"><h5 class="mb-0">icon-volume-medium</h4><div class="text-secondary">ea27</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-volume-low"></i><div class="flex-grow-1"><h5 class="mb-0">icon-volume-low</h4><div class="text-secondary">ea28</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-volume-mute"></i><div class="flex-grow-1"><h5 class="mb-0">icon-volume-mute</h4><div class="text-secondary">ea29</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-volume-mute2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-volume-mute2</h4><div class="text-secondary">ea2a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-volume-increase"></i><div class="flex-grow-1"><h5 class="mb-0">icon-volume-increase</h4><div class="text-secondary">ea2b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-volume-decrease"></i><div class="flex-grow-1"><h5 class="mb-0">icon-volume-decrease</h4><div class="text-secondary">ea2c</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-loop"></i><div class="flex-grow-1"><h5 class="mb-0">icon-loop</h4><div class="text-secondary">ea2d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-loop2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-loop2</h4><div class="text-secondary">ea2e</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-arrow-up"></i><div class="flex-grow-1"><h5 class="mb-0">icon-arrow-up</h4><div class="text-secondary">ea32</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-arrow-right"></i><div class="flex-grow-1"><h5 class="mb-0">icon-arrow-right</h4><div class="text-secondary">ea34</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-arrow-down"></i><div class="flex-grow-1"><h5 class="mb-0">icon-arrow-down</h4><div class="text-secondary">ea36</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-arrow-left"></i><div class="flex-grow-1"><h5 class="mb-0">icon-arrow-left</h4><div class="text-secondary">ea38</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-circle-up"></i><div class="flex-grow-1"><h5 class="mb-0">icon-circle-up</h4><div class="text-secondary">ea41</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-circle-right"></i><div class="flex-grow-1"><h5 class="mb-0">icon-circle-right</h4><div class="text-secondary">ea42</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-circle-down"></i><div class="flex-grow-1"><h5 class="mb-0">icon-circle-down</h4><div class="text-secondary">ea43</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-circle-left"></i><div class="flex-grow-1"><h5 class="mb-0">icon-circle-left</h4><div class="text-secondary">ea44</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-move-up"></i><div class="flex-grow-1"><h5 class="mb-0">icon-move-up</h4><div class="text-secondary">ea46</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-move-down"></i><div class="flex-grow-1"><h5 class="mb-0">icon-move-down</h4><div class="text-secondary">ea47</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-sort-alpha-asc"></i><div class="flex-grow-1"><h5 class="mb-0">icon-sort-alpha-asc</h4><div class="text-secondary">ea48</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-sort-alpha-desc"></i><div class="flex-grow-1"><h5 class="mb-0">icon-sort-alpha-desc</h4><div class="text-secondary">ea49</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-sort-numeric-asc"></i><div class="flex-grow-1"><h5 class="mb-0">icon-sort-numeric-asc</h4><div class="text-secondary">ea4a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-sort-numberic-desc"></i><div class="flex-grow-1"><h5 class="mb-0">icon-sort-numberic-desc</h4><div class="text-secondary">ea4b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-sort-amount-asc"></i><div class="flex-grow-1"><h5 class="mb-0">icon-sort-amount-asc</h4><div class="text-secondary">ea4c</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-sort-amount-desc"></i><div class="flex-grow-1"><h5 class="mb-0">icon-sort-amount-desc</h4><div class="text-secondary">ea4d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-checkbox-checked"></i><div class="flex-grow-1"><h5 class="mb-0">icon-checkbox-checked</h4><div class="text-secondary">ea52</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-checkbox-unchecked"></i><div class="flex-grow-1"><h5 class="mb-0">icon-checkbox-unchecked</h4><div class="text-secondary">ea53</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-radio-checked"></i><div class="flex-grow-1"><h5 class="mb-0">icon-radio-checked</h4><div class="text-secondary">ea54</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-radio-checked2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-radio-checked2</h4><div class="text-secondary">ea55</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-radio-unchecked"></i><div class="flex-grow-1"><h5 class="mb-0">icon-radio-unchecked</h4><div class="text-secondary">ea56</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-make-group"></i><div class="flex-grow-1"><h5 class="mb-0">icon-make-group</h4><div class="text-secondary">ea58</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-scissors"></i><div class="flex-grow-1"><h5 class="mb-0">icon-scissors</h4><div class="text-secondary">ea5a</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-filter"></i><div class="flex-grow-1"><h5 class="mb-0">icon-filter</h4><div class="text-secondary">ea5b</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-table2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-table2</h4><div class="text-secondary">ea71</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-insert-template"></i><div class="flex-grow-1"><h5 class="mb-0">icon-insert-template</h4><div class="text-secondary">ea72</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-embed"></i><div class="flex-grow-1"><h5 class="mb-0">icon-embed</h4><div class="text-secondary">ea7f</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-embed2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-embed2</h4><div class="text-secondary">ea80</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-terminal"></i><div class="flex-grow-1"><h5 class="mb-0">icon-terminal</h4><div class="text-secondary">ea81</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-share2"></i><div class="flex-grow-1"><h5 class="mb-0">icon-share2</h4><div class="text-secondary">ea82</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-google"></i><div class="flex-grow-1"><h5 class="mb-0">icon-google</h4><div class="text-secondary">ea88</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-facebook"></i><div class="flex-grow-1"><h5 class="mb-0">icon-facebook</h4><div class="text-secondary">ea90</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-youtube"></i><div class="flex-grow-1"><h5 class="mb-0">icon-youtube</h4><div class="text-secondary">ea9d</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-github"></i><div class="flex-grow-1"><h5 class="mb-0">icon-github</h4><div class="text-secondary">eab0</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-pinterest"></i><div class="flex-grow-1"><h5 class="mb-0">icon-pinterest</h4><div class="text-secondary">ead1</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-paypal"></i><div class="flex-grow-1"><h5 class="mb-0">icon-paypal</h4><div class="text-secondary">ead8</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-file-pdf"></i><div class="flex-grow-1"><h5 class="mb-0">icon-file-pdf</h4><div class="text-secondary">eadf</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-file-word"></i><div class="flex-grow-1"><h5 class="mb-0">icon-file-word</h4><div class="text-secondary">eae1</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-file-excel"></i><div class="flex-grow-1"><h5 class="mb-0">icon-file-excel</h4><div class="text-secondary">eae2</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-libreoffice"></i><div class="flex-grow-1"><h5 class="mb-0">icon-libreoffice</h4><div class="text-secondary">eae3</div></div></div>
<div class="d-flex p-2 bg-white border"><i class="imoon icon-html-five"></i><div class="flex-grow-1"><h5 class="mb-0">icon-html-five</h4><div class="text-secondary">eae4</div></div></div>
</div>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>