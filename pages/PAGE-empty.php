<?php
$_PMETA = [
  "title" => "TITLE",
  "desc" => "OPTIONAL DESCRIPTION",
  /* OPTIONAL - LOAD EXTRA SCRIPTS
  "load" => [
    ["s", HOST_ASSETS."A.js"],
    ["s", HOST_ASSETS."B.js", "defer"],
    ["s", HOST_ASSETS."C.js", "defer async"],
    ["c", HOST_ASSETS."D.css"],
  ]
  */
];
require PATH_PAGES . "TEMPLATE-top.php"; ?>
YOUR CONTENT HERE
<?php require PATH_PAGES . "TEMPLATE-bottom.php"; ?>