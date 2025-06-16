<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'tutor') {
    http_response_code(403);
    echo json_encode([]);
    exit();
}

$tutor_id = $_SESSION['user_id'];

$query = "
    SELECT t.Timeslot_ID, t.Date, t.Start_Time, t.End_Time, c.Course_name
    FROM Timeslot t
    JOIN Tutor_Creates tc ON t.Timeslot_ID = tc.Timeslot_ID
    JOIN Course c ON t.Course_ID = c.Course_ID
    WHERE tc.Tutor_ID = ?
    ORDER BY t.Date, t.Start_Time
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$result = $stmt->get_result();

$slots = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($slots);
?>
