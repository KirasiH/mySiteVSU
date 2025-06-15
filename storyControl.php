<?php

header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

$host = $HOST;
$dbname = $DBNAME;
$db_username = $DB_USERNAME;
$db_password = $DB_PASSWORD;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    exit(json_encode([
        'status' => -1,
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode([
        'status' => -4,
        'message' => 'Invalid request method'
    ]));
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    exit(json_encode([
        'status' => -5,
        'message' => 'Invalid JSON input',
        'error' => json_last_error_msg()
    ]));
}

// Извлечение данных
$move = $data['action'] ?? '';
$story_id = $data['id'] ?? '';
$topik = $data['topik'] ?? '';
$story = $data['story'] ?? '';
$token = $_COOKIE['auth_token'] ?? null;



// Проверка токена
if (empty($token)) {
    exit(json_encode([
        'status' => -1,
        'message' => 'Token is required'
    ]));
}

// Старт сессии (после всех возможных выводов)
session_start();

try {
    // Получение пользователя по токену
    $stmt = $pdo->prepare("SELECT * FROM users WHERE token = :token LIMIT 1");
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        exit(json_encode([
            'status' => -2,
            'message' => 'Invalid token',
            'error_code' => 'INVALID_TOKEN'
        ]));
    }

    // Проверка срока действия токена
    if ($user['token_expires_at'] && $user['token_expires_at'] < time()) {
        exit(json_encode([
            'status' => -3,
            'message' => 'Token expired',
            'error_code' => 'TOKEN_EXPIRED',
            'data' => $user['token_expires_at']
        ]));
    }


    switch ($move) {
        case '-1': 
            try {
                $stmt = $pdo->prepare("INSERT INTO stories (id_author, title, story) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $topik, $story]);
                
                exit(json_encode([
                    'status' => 1,
                    'message' => 'Data updated successfully'
                ]));
            } catch (PDOException $e) {
                exit(json_encode([
                    'status' => -1,
                    'message' => 'Update failed',
                    'error' => $e->getMessage()
                ]));
            }
            break;
            
        case '1':
            

            try {
                if (empty($story_id)){
                    exit(json_encode([
                    'status' => -1,
                    'message' => 'No delete story',
                ]));
                }

                $stmt = $pdo->prepare("DELETE FROM stories WHERE id = :id");
                $stmt->bindParam(':id', $story_id);
                $stmt->execute();

                exit(json_encode([
                    'status' => 1,
                    'message' => 'Story deleted'
                ]));
            } catch (PDOException $e) {

                exit(json_encode([
                    'status' => -1,
                    'message' => 'Update failed',
                    'error' => $e->getMessage()
                ]));
            }
            break;
    }

} catch (PDOException $e) {
    exit(json_encode([
        'status' => -1,
        'message' => 'Database error',
        'error_code' => 'DB_ERROR',
        'details' => $e->getMessage()
    ]));
}

?>