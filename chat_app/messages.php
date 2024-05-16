<?php
require 'db.php';
require 'functions.php';

$userId = $_POST['user_id'];
$contactId = $_POST['contact_id'];
$groupId = $_POST['group_id'];
$message = $_POST['message'];

// Check if the message is not empty
if (!empty($message)) {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, group_id, message) VALUES (:sender_id, :receiver_id, :group_id, :message)");
    $stmt->execute(['sender_id' => $userId, 'receiver_id' => $contactId, 'group_id' => $groupId, 'message' => $message]);
}

// Check if a file was uploaded
if (!empty($_FILES['file']['name'])) {
    // Get the file details
    $file_name = $_FILES['file']['name'];
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_path = 'uploads/' . $file_name;

    // Check if the file was successfully uploaded
    if (move_uploaded_file($file_tmp, $file_path)) {
        // Insert file path into the database
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, group_id, file_path) VALUES (:sender_id, :receiver_id, :group_id, :file_path)");
        $stmt->execute(['sender_id' => $userId, 'receiver_id' => $contactId, 'group_id' => $groupId, 'file_path' => $file_path]);
    } else {
        // Handle the case where file upload fails
        echo "File upload failed. Error code: " . $_FILES['file']['error'];
    }
}



// Retrieve and display messages
$messages = getMessages($userId, $contactId, $groupId);
foreach ($messages as $msg) {
    // Determine if the message is sent by the current user or received from another user
    $isSender = ($msg['sender_id'] == $userId);

    // Define classes based on the sender/receiver status
    $messageClass = ($isSender) ? 'sender-message' : 'receiver-message';

    // Echo the message div with appropriate classes
    echo "<div class='message $messageClass'>" . $msg['message'] . "</div>";
    if ($msg['file_path']) {
        // For images, you can set width and height directly in the style attribute
        echo "<div class='message $messageClass'><img src='" . $msg['file_path'] . "' width='50%' height='50%'></div>";
    }
}
$action = $_POST['action'] ?? null;

if ($action === 'getContactCreatedAt') {
    $contactId = $_POST['contact_id'] ?? null;
    if ($contactId !== null) {
        echo json_encode(['status' => 'success', 'created_at' => getContactCreatedAt($contactId)]);
    } else {
        echo json_encode(['status' => 'error', 'error' => 'Missing contact ID']);
    }
}
function getContactCreatedAt($contactId)
{
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT created_at FROM messages WHERE sender_id = :contactId ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(['contactId' => $contactId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['created_at'];
    } catch (PDOException $e) {
        // Handle the exception gracefully
        error_log("Error fetching contact created_at: " . $e->getMessage());
        return null; // Return null if an error occurs
    }
}

?>