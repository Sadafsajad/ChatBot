<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db.php';
require 'functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $groupName = $_POST['group_name'];
    $userId = $_POST['user_id'];

    $groupId = createGroup($groupName, $userId);
    addUserToGroup($groupId, $userId);

    echo json_encode(['group_id' => $groupId, 'group_name' => $groupName]);
}
?>