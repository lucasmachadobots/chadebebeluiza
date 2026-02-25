<?php
header('Content-Type: application/json; charset=utf-8');

$conn = require __DIR__ . 'db_connect.php';

$limit = (int)($_GET['limit'] ?? 20);
if ($limit < 1) $limit = 20;
if ($limit > 100) $limit = 100;

$onlyApproved = ($_GET['all'] ?? '') !== '1'; 
if ($onlyApproved) {
  $stmt = $conn->prepare("
    SELECT id, nome, mensagem, created_at
    FROM recados
    WHERE aprovado=1
    ORDER BY id DESC
    LIMIT ?
  ");
  $stmt->bind_param("i", $limit);
} else {
  $stmt = $conn->prepare("
    SELECT id, nome, mensagem, aprovado, created_at
    FROM recados
    ORDER BY id DESC
    LIMIT ?
  ");
  $stmt->bind_param("i", $limit);
}

$stmt->execute();
$res = $stmt->get_result();

$out = [];
while ($r = $res->fetch_assoc()) {
  $out[] = $r;
}

echo json_encode(['success' => true, 'items' => $out]);