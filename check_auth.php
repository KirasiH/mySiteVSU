<?php
session_start();

function validateAuth() {
    // 1. Проверяем наличие токена в куках
    if (!isset($_COOKIE['auth_token'])) {
        return false;
    }

    // 2. Проверяем наличие данных сессии
    if (!isset($_SESSION['token']) || !isset($_SESSION['expire'])) {
        return false;
    }

    // 3. Сравниваем токены
    if ($_COOKIE['auth_token'] !== $_SESSION['token']) {
        return false;
    }

    // 4. Проверяем срок действия
    if (time() > $_SESSION['expire']) {
        return false;
    }

    return true;
}

?>