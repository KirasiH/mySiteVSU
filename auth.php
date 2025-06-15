<?php
// auth.php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/config.php';

$host = $HOST;
$dbname = $DBNAME;
$db_username = $DB_USERNAME;
$db_password = $DB_PASSWORD;

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $db_username, $db_password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => -3, 'message' => 'Database connection error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);  

    $login = $data['email'] ?? ''; // Может быть email или username
    $password = $data['password'] ?? '';

    // Валидация
    $errors = [];

    if (empty($login)) {
        $errors['login'] = 'Username or email is required';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    if (!empty($errors)) {
        echo json_encode(['status' => -2, 'errors' => $errors]);
        exit;
    }

    try {
        // Ищем пользователя по email или username
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :login");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['status' => -1, 'message' => 'Invalid credentials status 1']);
            exit;
        }

        // Проверяем пароль
        if (!password_verify($password, $user['password'])) {
            echo json_encode([
                'status' => -1,
                'message' => 'Invalid credentials status 2',
                'debug_info' => [
                    'input_password' => $password,
                    'stored_hash' => $user['password'],
                    'hash_match' => password_verify($password, $user['password']) ? 'true' : 'false',
                    'hash_info' => password_get_info($user['password'])
                ]
            ]);
            exit;
        }

        // Успешная аутентификация


        $token = bin2hex(random_bytes(32));
        $expire = time() + 60 * 60 * 24 * 7; // 30 дней

            // Обновляем токен в базе
        $stmt = $db->prepare("UPDATE users SET token = :token, token_expires_at = :expire WHERE id = :id");
        $stmt->execute([
            ':token' => $token,
            ':expire' => $expire,
            ':id' => $user['id']
        ]);
        $_SESSION['token'] = $token;
        $_SESSION['expire'] = $expire;
        $_SESSION['about'] = $user['about'];
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
            // Устанавливаем куки
        setcookie('auth_token', $token, $expire, '/', '', true, true);
        

        // Возвращаем успешный ответ
        echo json_encode([
            'status' => 1,
            'message' => 'Authentication successful',
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ],
            'redirect' => 'dashboard.php'
        ]);

    } catch (PDOException $e) {
        echo json_encode(['status' => -3, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => -4, 'message' => 'Invalid request method']);
}
?>