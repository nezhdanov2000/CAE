<?php
session_start();

require_once 'db.php';

$name = $_POST['name'];
$surname = $_POST['surname'];
$password = $_POST['password'];
$role = $_POST['role'];

if ($role === 'student') {
    $query = "SELECT * FROM Student WHERE Name = ? AND Surname = ?";
} else {
    $query = "SELECT * FROM Tutor WHERE Name = ? AND Surname = ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $name, $surname);
$stmt->execute();
$result = $stmt->get_result();

if ($userRow = $result->fetch_assoc()) {
    if (password_verify($password, $userRow['Password'])) {
        $_SESSION['user_id'] = $userRow[$role === 'student' ? 'Student_ID' : 'Tutor_ID'];
        $_SESSION['name'] = $userRow['Name'];
        $_SESSION['surname'] = $userRow['Surname'];
        $_SESSION['role'] = $role;
        header("Location: dashboard.html");
        exit();
    } else {
        echo "❌ Неверный пароль";
    }
} else {
    echo "❌ Пользователь не найден";
}

$stmt->close();
$conn->close();
?>
