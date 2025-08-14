<?php
$servername = "localhost";
$username = "root";
$password = "QscWdzEax753861942";
$dbname = "cae_database";

$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Создаем базу данных если её нет
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");

// Выбираем базу данных
$conn->select_db($dbname);
?> 