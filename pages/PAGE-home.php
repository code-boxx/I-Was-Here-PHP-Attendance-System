<?php
// JUST FORWARD TO RESPECTIVE HOME PAGE
$_CORE->redirect(strtolower($_SESS["user"]["user_role"]));