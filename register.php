<?php

require_once 'db.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';

    // Проверка на пустые поля
    if (!$name || !$surname || !$password || !$email || !$role) {
        echo 'Пожалуйста, заполните все поля.';
        exit();
    }

    // Проверка уникальности email среди студентов и преподавателей
    $stmt = $conn->prepare('SELECT 1 FROM Student WHERE Email = ? UNION SELECT 1 FROM Tutor WHERE Email = ?');
    $stmt->bind_param('ss', $email, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        echo 'Пользователь с таким email уже существует.';
        exit();
    }
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'student') {
        $stmt = $conn->prepare('INSERT INTO Student (Name, Surname, Password, Email) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $surname, $hashed_password, $email);
        $stmt->execute();
        $stmt->close();
    } elseif ($role === 'tutor') {
        $stmt = $conn->prepare('INSERT INTO Tutor (Name, Surname, Password, Email) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $surname, $hashed_password, $email);
        $stmt->execute();
        $stmt->close();
    } else {
        echo 'Некорректная роль.';
        exit();
    }

    header('Location: login.html');
    exit();
}
