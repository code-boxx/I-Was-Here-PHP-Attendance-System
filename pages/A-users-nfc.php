<?php
// (A) GET USER
$_POST["hash"] = "NFC";
$user = $_CORE->autoCall("Users", "get");
if (!is_array($user)) { exit("Invalid user"); }
?>
<h3 class="mb-3">USER NFC LOGIN TOKEN</h3>

<!-- (B) CREATE NEW TOKEN -->
<div class="fw-bold text-danger mb-2">CREATE NEW TOKEN</div>
<div class="bg-white border p-4 mb-3">
  <button id="nfc-btn" disabled class="my-1 btn btn-primary d-flex-inline" onclick="unfc.add(<?=$_POST["id"]?>)">
    <i class="ico-sm icon-feed"></i> <span id="nfc-stat">Initializing</span>
  </button>
  <div class="text-secondary mt-2">
    * A user can only have one login token, creating a new token will nullify the previous one.
  </div>
</div>

<!-- (C) NULL NFC TOKEN -->
<div class="fw-bold text-danger mb-2">NULLIFY NFC TOKEN</div>
<div class="bg-white border p-4 mb-3">
  <button id="nfc-null" class="my-1 btn btn-primary d-flex-inline"
          onclick="unfc.del(<?=$_POST["id"]?>)"<?=$user["hash_code"]==""?" disabled":""?>>
    <i class="ico-sm icon-blocked"></i> Nullify Login Token
  </button>
  <div class="text-secondary mt-2">
    * The user's NFC login token will be nullified, but the login email/password remains unaffected.
  </div>
</div>

<button type="button" class="my-1 btn btn-primary d-flex-inline" onclick="unfc.back()">
  <i class="ico-sm icon-undo2"></i> Back
</button>