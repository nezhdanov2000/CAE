<?php

require_once 'db.php';



if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$surname = $_POST['surname'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];

if ($role == 'student') {
    $stmt = $conn->prepare("INSERT INTO Student (Name, Surname, Password) VALUES (?, ?, ?)");
} else {
    $stmt = $conn->prepare("INSERT INTO Tutor (Name, Surname, Password) VALUES (?, ?, ?)");
}

$stmt->bind_param("sss", $name, $surname, $password);

if ($stmt->execute()) {
    echo "✅ Регистрация прошла успешно!";
} else {
    echo "❌ Ошибка: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
