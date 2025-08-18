<?php
declare(strict_types=1);

/** @var PDO $pdo */
$pdo = require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
	$limit = isset($_GET['limit']) ? max(1, min(50, (int)$_GET['limit'])) : 10;
	$stmt = $pdo->prepare('SELECT id, title, content, created_at FROM articles ORDER BY created_at DESC, id DESC LIMIT :limit');
	$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
	$stmt->execute();
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

	header('Content-Type: application/json');
	echo json_encode($rows, JSON_PRETTY_PRINT);
	exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$input = json_decode(file_get_contents('php://input'), true) ?? [];
	$title = trim((string)($input['title'] ?? ''));
	$content = trim((string)($input['content'] ?? ''));
	if ($title === '' || $content === '') {
		header('Content-Type: application/json');
		http_response_code(400);
		echo json_encode(['error' => 'title and content are required'], JSON_PRETTY_PRINT);
		exit();
	}
	$stmt = $pdo->prepare('INSERT INTO articles (title, content) VALUES (:title, :content)');
	$stmt->execute([':title' => $title, ':content' => $content]);
	$createdId = (int)$pdo->lastInsertId();

	header('Content-Type: application/json');
	echo json_encode(['id' => $createdId], JSON_PRETTY_PRINT);
	exit();
}

http_response_code(405);
echo 'Method Not Allowed';

