<?php
// Включение строгого режима ошибок
declare(strict_types=1);

// Настройка вывода ошибок (для разработки)
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Очистка буфера вывода
while (ob_get_level()) ob_end_clean();

// Установка заголовка JSON
header('Content-Type: application/json; charset=utf-8');

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

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode([
        'status' => -4,
        'message' => 'Invalid request method'
    ]));
}

// Получение и валидация JSON-данных
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
$new_password = $data['new_password'] ?? '';
$password = $data['password'] ?? '';
$new_username = $data['new_username'] ?? '';
$new_about = $data['new_about'] ?? '';
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

    // Обработка действий
    switch ($move) {
        case '-1': // Обновление данных
            if (!password_verify($password, $user['password'])) {
                exit(json_encode([
                    'status' => -1,
                    'message' => 'Invalid password'
                ]));
            }
            
            if (empty($new_password)) {
                $new_password = password_hash($password, PASSWORD_DEFAULT);
            }
            
            if (empty($new_username)) {
                $new_username = $_SESSION['username'];
            }
            
            try {
                $stmt = $pdo->prepare("UPDATE users SET 
                    username = ?, 
                    about = ?, 
                    password = ?
                    WHERE id = ?");
                $stmt->execute([$new_username, $new_about, $new_password, $user['id']]);

                $_SESSION['username'] = $new_username;
                $_SESSION['about'] = $new_about;
                
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
            
        case '0': // Выход из аккаунта
            try {
                $stmt = $pdo->prepare("UPDATE users SET token_expires_at = 0 WHERE id = :id");
                $stmt->bindParam(':id', $user['id']);
                $stmt->execute();

                session_unset();
                session_destroy();
                setcookie('auth_token', '', time() - 3600, '/', '', true, true);

                exit(json_encode([
                    'status' => 1,
                    'message' => 'Logged out successfully'
                ]));
            } catch (PDOException $e) {
                exit(json_encode([
                    'status' => -1,
                    'message' => 'Logout failed',
                    'error' => $e->getMessage()
                ]));
            }
            break;
            
        case '1': // Удаление аккаунта
            if (!password_verify($password, $user['password'])) {
                exit(json_encode([
                    'status' => -1,
                    'message' => 'Invalid password'
                ]));
            }

            try {
                $stmt = $pdo->prepare("DELETE FROM stories WHERE user_id = :user_id");
                $stmt->bindParam(':user_id', $user['id']);
                $stmt->execute();

                $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                $stmt->bindParam(':id', $user['id']);
                $stmt->execute();

                session_unset();
                session_destroy();
                setcookie('auth_token', '', time() - 3600, '/', '', true, true);

                exit(json_encode([
                    'status' => 1,
                    'message' => 'Account deleted successfully'
                ]));
            } catch (PDOException $e) {
                exit(json_encode([
                    'status' => -1,
                    'message' => 'Account deletion failed',
                    'error' => $e->getMessage()
                ]));
            }
            break;

        default:
            exit(json_encode([
                'status' => -1,
                'message' => 'Invalid action',
                'action' => $move
            ]));
    }
} catch (PDOException $e) {
    exit(json_encode([
        'status' => -1,
        'message' => 'Database error',
        'error_code' => 'DB_ERROR',
        'details' => $e->getMessage()
    ]));
}