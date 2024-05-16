<?php
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