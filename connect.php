<?php
$host = 'MySQL-8.0';
$username = 'root';
$password = '';
$database = 'booksDB';

// Создаем подключение
$connect = mysqli_connect($host, $username, $password, $database);

// Проверяем подключение
if (!$connect) {
    error_log("Connection failed: " . mysqli_connect_error());

    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}
?>