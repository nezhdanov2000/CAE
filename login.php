<?php
session_start();

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    if (!$email || !$password || !$role) {
        echo 'Пожалуйста, заполните все поля.';
        exit();
    }

    if ($role === 'student') {
        $stmt = $conn->prepare('SELECT Student_ID, Password, Name, Surname FROM Student WHERE Email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hash, $name, $surname);
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = 'student';
                $_SESSION['name'] = $name;
                $_SESSION['surname'] = $surname;
                header('Location: dashboard.html');
                exit();
            }
        }
        echo 'Неверный email или пароль.';
        exit();
    } elseif ($role === 'tutor') {
        $stmt = $conn->prepare('SELECT Tutor_ID, Password, Name, Surname FROM Tutor WHERE Email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hash, $name, $surname);
            $stmt->fetch();
            if (password_verify($password, $hash)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['role'] = 'tutor';
                $_SESSION['name'] = $name;
                $_SESSION['surname'] = $surname;
                header('Location: dashboard.html');
                exit();
            }
        }
        echo 'Неверный email или пароль.';
        exit();
    } else {
        echo 'Некорректная роль.';
        exit();
    }
}

$stmt->close();
$conn->close();
?>
