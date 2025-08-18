<?php
declare(strict_types=1);

// Enable CORS for local dev if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	exit(0);
}

// JSON helpers
function respond_json($data, int $status = 200): void {
	header('Content-Type: application/json');
	http_response_code($status);
	echo json_encode($data, JSON_PRETTY_PRINT);
	exit();
}

function respond_error(string $message, int $status = 400): void {
	respond_json([ 'error' => $message ], $status);
}

// SQLite connection
$dbPath = __DIR__ . '/../../data/app.sqlite';
@mkdir(dirname($dbPath), 0777, true);

try {
	$pdo = new PDO('sqlite:' . $dbPath);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Throwable $e) {
	respond_error('Database connection failed: ' . $e->getMessage(), 500);
}

// Initialize schema if not exists
$pdo->exec('CREATE TABLE IF NOT EXISTS images (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	url TEXT NOT NULL,
	title TEXT,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

$pdo->exec('CREATE TABLE IF NOT EXISTS articles (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	title TEXT NOT NULL,
	content TEXT NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)');

// Seed some demo data if empty
$countImages = (int)$pdo->query('SELECT COUNT(*) FROM images')->fetchColumn();
if ($countImages === 0) {
	$seedImages = [
		['https://images.unsplash.com/photo-1446776811953-b23d57bd21aa?q=80&w=1600&auto=format&fit=crop', 'Veil Nebula'],
		['https://images.unsplash.com/photo-1462331940025-496dfbfc7564?q=80&w=1600&auto=format&fit=crop', 'Spacewalker'],
		['https://images.unsplash.com/photo-1445905595283-21f8ae8a33d2?q=80&w=1600&auto=format&fit=crop', 'Orbital Dawn'],
		['https://images.unsplash.com/photo-1454789548928-9efd52dc4031?q=80&w=1600&auto=format&fit=crop', 'Lunar Surface'],
		['https://images.unsplash.com/photo-1447433819943-74a20887a81e?q=80&w=1600&auto=format&fit=crop', 'Star Field'],
	];

	$stmt = $pdo->prepare('INSERT INTO images (url, title) VALUES (:url, :title)');
	foreach ($seedImages as $img) {
		$stmt->execute([':url' => $img[0], ':title' => $img[1]]);
	}
}

$countArticles = (int)$pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
if ($countArticles === 0) {
	$stmt = $pdo->prepare('INSERT INTO articles (title, content) VALUES (:title, :content)');
	$stmt->execute([
		':title' => 'Welcome to NASA Explorer',
		':content' => 'This is a demo article seeded by the backend. Replace with real mission notes or concepts.'
	]);
}

// Expose PDO for other scripts
return $pdo;

