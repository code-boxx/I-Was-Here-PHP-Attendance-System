<?php
if ($_SESSION['user']['role']=="T") { require PATH_PAGES . "ADMIN-attend.php"; }
else { require PATH_PAGES . "PAGE-attend.php"; }