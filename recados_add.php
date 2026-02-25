<?php
header('Content-Type: application/json; charset=utf-8');

$conn = require __DIR__ . 'db_connect.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'JSON inválido.']);
  exit;
}

$nome = trim((string)($data['nome'] ?? ''));
$whats = trim((string)($data['whatsapp'] ?? ''));
$msg  = trim((string)($data['mensagem'] ?? ''));

if ($nome === '' || $msg === '') {
  http_response_code(422);
  echo json_encode(['success' => false, 'message' => 'Nome e mensagem são obrigatórios.']);
  exit;
}

if (mb_strlen($nome) > 120) $nome = mb_substr($nome, 0, 120);
if (mb_strlen($whats) > 40) $whats = mb_substr($whats, 0, 40);
if (mb_strlen($msg) > 1000) $msg = mb_substr($msg, 0, 1000);

// anti-spam simples
$ip = $_SERVER['REMOTE_ADDR'] ?? '';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ua = mb_substr($ua, 0, 255);

// limite: 1 recado por IP a cada 20s
$stmt = $conn->prepare("SELECT created_at FROM recados WHERE ip=? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $ip);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
  $last = strtotime($row['created_at']);
  if ($last && (time() - $last) < 20) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Aguarde alguns segundos e tente novamente.']);
    exit;
  }
}

$aprovado = 1; // mude para 0 se quiser aprovar manualmente no painel

$stmt = $conn->prepare("
  INSERT INTO recados (nome, whatsapp, mensagem, aprovado, ip, user_agent)
  VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("sssiss", $nome, $whats, $msg, $aprovado, $ip, $ua);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Erro ao salvar.']);
  exit;
}

echo json_encode(['success' => true]);