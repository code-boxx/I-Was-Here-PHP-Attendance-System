<?php
// (A) GET ALL SETTINGS
$settings = $_CORE->Settings->getAll();

// (B) SETTINGS LIST
$_PMETA = ["load" => [["s", HOST_ASSETS."A-settings.js", "defer"]]];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
<h3 class="mb-3">SYSTEM SETTINGS</h3>
<form id="set-list" onsubmit="return save()">
  <?php foreach ($settings as $o) { ?>
  <div class="form-floating mb-3">
    <input type="text" class="form-control" required
           name="<?=$o["setting_name"]?>" value="<?=$o["setting_value"]?>">
    <label><?=$o["setting_description"]?></label>
  </div>
  <?php } ?>

  <button type="submit" class="my-1 btn btn-primary d-flex-inline">
    <i class="ico-sm icon-floppy-disk"></i> Save Settings
  </button>
</form>
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>