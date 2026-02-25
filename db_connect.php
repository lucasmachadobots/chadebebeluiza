<?php
mysqli_report(MYSQLI_REPORT_OFF);

$DB_HOST = 'srv1897.hstgr.io';
$DB_NAME = 'u780811042_chadaluiza';
$DB_USER = 'u780811042_chadaluiza';
$DB_PASS = 'Lucas@iluubs1@';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['success' => false, 'message' => 'Falha ao conectar no banco.']);
  exit;
}

$conn->set_charset('utf8mb4');
return $conn;