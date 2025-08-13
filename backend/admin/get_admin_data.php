<?php
require_once 'auth.php';
require_once 'db.php';

// Проверяем авторизацию
require_admin_auth();

// Получаем данные текущего администратора
$admin_id = get_current_admin_id();

$stmt = $conn->prepare('SELECT Name, Surname, Role FROM Admin WHERE Admin_ID = ?');
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$stmt->bind_result($name, $surname, $role);
$stmt->fetch();

if ($stmt->num_rows > 0) {
    send_json_success([
        'admin' => [
            'name' => $name . ' ' . $surname,
            'role' => ucfirst(str_replace('_', ' ', $role))
        ]
    ]);
} else {
    send_json_error('Admin data not found', 404);
}

$stmt->close();
$conn->close();
?> 