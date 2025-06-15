<?php
// register.php
header('Content-Type: application/json');

require_once __DIR__ . '/config.php';

$host = $HOST;
$dbname = $DBNAME;
$username = $DB_USERNAME;
$password = $DB_PASSWORD;

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => -3, 'message' => 'Database connection error']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);  

    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $about = $data['about'] ?? '';

    // Валидация
    $errors = [];

    // Проверка имени пользователя
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    }

    // Проверка email
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email format';
    }

    // Проверка пароля
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    // Если есть ошибки - возвращаем
    if (!empty($errors)) {
        echo json_encode(['status' => -2, 'errors' => $errors, 'username' => $username]);
        exit;
    }

    try {
        // Проверяем, существует ли пользователь
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Записываем в файл debug.log
        file_put_contents('debug.log', print_r($result, true), FILE_APPEND);

        if (!empty($result['id'])) {
            echo json_encode(['status' => -1, 'message' => 'Username or email already exists']);
            exit;
        }



        // Хешируем пароль
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));
        $expire = time() + 60 * 60 * 24 * 1; // 30 дней

        // Создаем пользователя
        $stmt = $db->prepare("INSERT INTO users (username, email, about, password, token, token_expires_at ) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $email, $about, $hashed_password, $token, $expire]);

        // Получаем ID нового пользователя
        $user_id = $db->lastInsertId();

        setcookie('auth_token', $token, $expire, '/', '', true, true);
        
        // Начинаем сессию
        session_start();
        $_SESSION['user_id'] = $user_id; //['id' => $user_id, 'name' =>  $username, 'token' =>  $token, 'expire' => $expire];
        $_SESSION['username'] = $username;
        $_SESSION['about'] = $about;
        $_SESSION['token'] = $token;
        $_SESSION['expire'] = $expire;

        // Возвращаем успешный ответ
        echo json_encode([
            'status' => 1,
            'message' => 'Registration successful',
            'user' => [
                'id' => $user_id,
                'username' => $username,
                'email' => $email
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