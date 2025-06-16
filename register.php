<?php

require_once 'db.php';

function is_fetch_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) ||
        (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $password = $_POST['password'] ?? '';
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? '';

    if (!$name || !$surname || !$password || !$email || !$role) {
        if (is_fetch_request()) {
            http_response_code(400);
            echo 'Пожалуйста, заполните все поля.';
        } else {
            echo 'Пожалуйста, заполните все поля.';
        }
        exit();
    }

    $stmt = $conn->prepare('SELECT 1 FROM Student WHERE Email = ? UNION SELECT 1 FROM Tutor WHERE Email = ?');
    $stmt->bind_param('ss', $email, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        if (is_fetch_request()) {
            http_response_code(409);
            echo 'Пользователь с таким email уже существует.';
        } else {
            echo 'Пользователь с таким email уже существует.';
        }
        exit();
    }
    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    if ($role === 'student') {
        $stmt = $conn->prepare('INSERT INTO Student (Name, Surname, Password, Email) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $surname, $hashed_password, $email);
        $success = $stmt->execute();
        $stmt->close();
    } elseif ($role === 'tutor') {
        $stmt = $conn->prepare('INSERT INTO Tutor (Name, Surname, Password, Email) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $surname, $hashed_password, $email);
        $success = $stmt->execute();
        $stmt->close();
    } else {
        if (is_fetch_request()) {
            http_response_code(400);
            echo 'Некорректная роль.';
        } else {
            echo 'Некорректная роль.';
        }
        exit();
    }

    if ($success) {
        if (is_fetch_request()) {
            echo '✅ Регистрация прошла успешно!';
        } else {
            header('Location: login.html');
        }
    } else {
        if (is_fetch_request()) {
            http_response_code(500);
            echo '❌ Ошибка при регистрации.';
        } else {
            echo '❌ Ошибка при регистрации.';
        }
    }
    exit();
}
