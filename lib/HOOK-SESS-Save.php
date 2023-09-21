<?php
// CALLED BY $_CORE->SESSION->SAVE()
// USE THIS TO OVERRIDE DATA TO BE SAVED INTO THE JWT

// EXAMPLE - REMOVE USER SETTINGS
// if (isset($data["settings"])) { unset($data["settings"]); }

// ADDED BY INSTALLER - ONLY SAVE USER ID INTO JWT
if (isset($data["user"])) {
  $data["user"] = ["user_id" => $data["user"]["user_id"]];
}