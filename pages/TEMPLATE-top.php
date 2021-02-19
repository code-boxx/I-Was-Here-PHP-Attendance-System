<!DOCTYPE html>
<html>
  <head>
    <title>I Was Here</title>
    <meta name="description" content="Student Attendance System">
    <link rel="shortcut icon" href="<?=URL_PUBLIC?>favicon.png">
    <link href="<?=URL_PUBLIC?>common.css" rel="stylesheet">
    <script>var urlroot="<?=URL_BASE?>",urlpub="<?=URL_PUBLIC?>",urlapi="<?=URL_API?>",urladmin="<?=URL_ADMIN?>";</script>
    <script src="<?=URL_PUBLIC?>common.js"></script>
  </head>
  <body>
    <!-- (A) NOW LOADING SPINNER -->
    <div id="page-loader">
      <img id="page-loader-spin" src="<?=URL_PUBLIC?>/cube-loader.svg">
    </div>

    <!-- (B) TOAST MESSAGE -->
    <div id="page-toast"></div>

    <?php if (isset($_SESSION['user'])) { ?>
    <!-- (C) SIDE BAR -->
    <nav id="page-sidebar"><?php
      require PATH_PAGES .
      ($_SESSION['user']['role']=="T" ? "MENU-teacher.php" : "MENU-student.php");
    ?></nav>
    <?php } ?>

    <!-- (D) CONTENTS -->
    <div id="page-main">
      <?php if (isset($_SESSION['user'])) { ?>
      <!-- (D1) NAVIGATION BAR -->
      <nav id="page-nav">
        <div id="page-button-side" onclick="common.side();">&#9776;</div>
        <div id="page-button-out" onclick="common.bye();">&#9747;</div>
      </nav>
      <?php } ?>

      <!-- (D2) PAGE CONTENTS -->
      <main id="page-contents">
        <div id="pageA" class="page-body">