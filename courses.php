<?php
require_once 'db.php';

$result = $conn->query("SELECT Course_ID, Course_name FROM Course ORDER BY Course_name");
$courses = $result->fetch_all(MYSQLI_ASSOC);

header('Content-Type: application/json');
echo json_encode($courses);
