<?php
// public/index.php - только точка входа

require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/Router.php';

session_start();

try {
    $pdo = getDB();
    $router = new Router($pdo);
    $router->dispatch();
} catch (Exception $e) {
    http_response_code(500);
    echo "Ошибка: " . $e->getMessage() . "<br>";
    echo "Файл: " . $e->getFile() . "<br>";
    echo "Строка: " . $e->getLine();
}
