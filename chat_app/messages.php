<?php
require 'db.php';
require 'functions.php';

$userId = $_POST['user_id'];
$contactId = $_POST['contact_id'];
$groupId = $_POST['group_id'];
$message = $_POST['message'];

if ($message) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, group_id, message) VALUES (:sender_id, :receiver_id, :group_id, :message)");
    $stmt->execute(['sender_id' => $userId, 'receiver_id' => $contactId, 'group_id' => $groupId, 'message' => $message]);
}

// Check if any files were uploaded
if (!empty($_FILES['file']['name'][0])) {
    // Process each uploaded file
    foreach ($_FILES['file']['name'] as $key => $name) {
        $tmp_name = $_FILES['file']['tmp_name'][$key];
        $file_path = 'uploads/' . $name; // Set file path
        // Move uploaded file to the uploads directory
        move_uploaded_file($tmp_name, $file_path);
        // Insert file path into the database
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, group_id, file_path) VALUES (:sender_id, :receiver_id, :group_id, :file_path)");
        $stmt->execute(['sender_id' => $userId, 'receiver_id' => $contactId, 'group_id' => $groupId, 'file_path' => $file_path]);
    }
}

// Retrieve and display messages
$messages = getMessages($userId, $contactId, $groupId);
foreach ($messages as $msg) {
    if ($msg['message']) {
        echo "<div class='message'>" . $msg['message'] . "</div>";
    }
    if ($msg['file_path']) {
        echo "<div class='message'><a href='" . $msg['file_path'] . "' target='_blank'>" . basename($msg['file_path']) . "</a></div>";
    }
}
?>