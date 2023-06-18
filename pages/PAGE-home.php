<?php
// JUST FORWARD TO RESPECTIVE HOME PAGE
$_CORE->redirect(strtolower($_SESSION["user"]["user_level"]));