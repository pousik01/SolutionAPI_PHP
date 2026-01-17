<?php
$host = '127.0.1.22';
$username = 'root';
$password = '';
$database = 'booksDB';

// Создаем подключение
$connect = mysqli_connect($host, $username, $password, $database);

// Проверяем подключение
if (!$connect) {
    echo("Connection failed: " . mysqli_connect_error());
}else{
    echo "Успешное соединение";
}
?>