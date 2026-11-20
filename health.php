<?php
header("Content-Type: application/json; charset=utf-8");
try {
  require __DIR__ . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR . "CORE-Go.php";
  $_CORE->DB->fetchCol("SELECT 1");
  http_response_code(200);
  echo json_encode(["status" => "ok"]);
} catch (Throwable $e) {
  error_log("Health check failed: ".$e->getMessage());
  http_response_code(503);
  echo json_encode(["status" => "unavailable"]);
}
