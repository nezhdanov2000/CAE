<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

if (!isset($_POST['timeslot_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing timeslot ID']);
    exit();
}

$tutor_id = $_SESSION['user_id'];
$timeslot_id = (int) $_POST['timeslot_id'];

// Проверим, принадлежит ли слот преподавателю
$check = $conn->prepare("SELECT 1 FROM Tutor_Creates WHERE Tutor_ID = ? AND Timeslot_ID = ?");
$check->bind_param("ii", $tutor_id, $timeslot_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit();
}

try {
    $conn->begin_transaction();

    $stmt1 = $conn->prepare("DELETE FROM Tutor_Creates WHERE Timeslot_ID = ?");
    $stmt1->bind_param("i", $timeslot_id);
    $stmt1->execute();
    if ($stmt1->error) throw new Exception($stmt1->error);
    $stmt1->close();

    $stmt2 = $conn->prepare("DELETE FROM Timeslot WHERE Timeslot_ID = ?");
    $stmt2->bind_param("i", $timeslot_id);
    $stmt2->execute();
    if ($stmt2->error) throw new Exception($stmt2->error);
    $stmt2->close();

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
