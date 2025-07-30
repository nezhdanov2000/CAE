<?php
require_once '../common/auth.php';
require_once '../common/db.php';

require_tutor();

$tutor_id = get_current_user_id();

$course_name = trim($_POST['course_name'] ?? '');
$date = $_POST['date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $course_name = trim($_POST['course_name'] ?? '');

    if (!$date || !$start_time || !$end_time || !$course_name) {
        if (is_fetch_request()) {
            send_json_error('Please fill in all fields.', 400);
        } else {
            echo 'Please fill in all fields.';
        }
        exit();
    }

    // Check for time slot conflicts
    $stmt = $conn->prepare("
        SELECT t.Start_Time, t.End_Time 
        FROM Timeslot t 
        JOIN Tutor_Creates tc ON t.Timeslot_ID = tc.Timeslot_ID 
        WHERE tc.Tutor_ID = ? AND t.Date = ?
    ");
    $stmt->bind_param("is", $tutor_id, $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $conflicts = false;
    while ($row = $result->fetch_assoc()) {
        $existing_start = $row['Start_Time'];
        $existing_end = $row['End_Time'];
        
        // Check if new slot overlaps with existing slot
        // Overlap occurs when: new_start < existing_end AND new_end > existing_start
        if ($start_time < $existing_end && $end_time > $existing_start) {
            $conflicts = true;
            break;
        }
    }
    $stmt->close();
    
    if ($conflicts) {
        if (is_fetch_request()) {
            send_json_error('Time slot conflicts with existing slots. Please choose a different time.', 400);
        } else {
            echo 'Time slot conflicts with existing slots. Please choose a different time.';
        }
        exit();
    }

    // 1. Check if the course exists
    $stmt = $conn->prepare("SELECT Course_ID FROM Course WHERE Course_name = ?");
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $course_id = $row['Course_ID'];
    } else {
        // 2. Add the course
        $insert_course = $conn->prepare("INSERT INTO Course (Course_name) VALUES (?)");
        $insert_course->bind_param("s", $course_name);
        $insert_course->execute();
        $course_id = $conn->insert_id;
        $insert_course->close();
    }
    $stmt->close();

    // 3. Add the timeslot
    $stmt = $conn->prepare('INSERT INTO Timeslot (Date, Start_Time, End_Time, Course_ID) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('sssi', $date, $start_time, $end_time, $course_id);
    $success = $stmt->execute();
    $timeslot_id = $conn->insert_id;
    $stmt->close();

    if ($success) {
        // 4. Link the slot with the tutor
        $stmt2 = $conn->prepare('INSERT INTO Tutor_Creates (Tutor_ID, Timeslot_ID) VALUES (?, ?)');
        $stmt2->bind_param('ii', $tutor_id, $timeslot_id);
        $success2 = $stmt2->execute();
        $stmt2->close();
        if ($success2) {
            if (is_fetch_request()) {
                send_json_success(null, 'Timeslot successfully created!');
            } else {
                echo 'Timeslot successfully created!';
            }
        } else {
            if (is_fetch_request()) {
                send_json_error('Error linking the tutor.', 500);
            } else {
                echo 'Error linking the tutor.';
            }
        }
    } else {
        if (is_fetch_request()) {
            send_json_error('Error creating the timeslot.', 500);
        } else {
            echo 'Error creating the timeslot.';
        }
    }
    exit();
}

$conn->close();
?> 