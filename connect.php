<?php
$host = 'localhost';
$username = 'mysql';
$password = 'mysql';
$database = 'booksDB';

// Создаем подключение
$connect = mysqli_connect($host, $username, $password, $database);

// Проверяем подключение
if (!$connect) {
    echo("Connection failed: " . mysqli_connect_error());
}
?>