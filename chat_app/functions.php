<?php
function getUsers($excludeUserId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id != :excludeUserId");
    $stmt->execute(['excludeUserId' => $excludeUserId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUserById($userId)
{
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :userId");
    $stmt->execute(['userId' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getGroups($userId)
{
    global $conn;
    try {
        // First, fetch group IDs from group_users table for the given user ID
        $stmt = $conn->prepare("SELECT group_id FROM group_users WHERE user_id = :user_id");
        $stmt->execute(['user_id' => $userId]);
        $groupIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // If there are no groups for the user, return an empty array
        if (empty($groupIds)) {
            return [];
        }

        // Use the fetched group IDs to fetch group details from the groups table
        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $stmt = $conn->prepare("SELECT * FROM `groups` WHERE id IN ($placeholders)");
        $stmt->execute($groupIds);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Handle the exception gracefully
        error_log("Error fetching groups: " . $e->getMessage());
        return []; // Return an empty array if an error occurs
    }
}


function getMessages($userId, $contactId = null, $groupId = null)
{
    global $conn;
    if ($groupId) {
        $stmt = $conn->prepare("SELECT * FROM messages WHERE group_id = :groupId ORDER BY created_at");
        $stmt->execute(['groupId' => $groupId]);
    } else {
        $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = :userId AND receiver_id = :contactId) OR (sender_id = :contactId AND receiver_id = :userId) ORDER BY created_at");
        $stmt->execute(['userId' => $userId, 'contactId' => $contactId]);
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createGroup($groupName, $createdBy)
{
    global $conn;
    try {
        $stmt = $conn->prepare("INSERT INTO `groups` (name, created_by) VALUES (:name, :created_by)");
        $stmt->execute(['name' => $groupName, 'created_by' => $createdBy]);
        return $conn->lastInsertId();
    } catch (PDOException $e) {
        error_log("Error creating group: " . $e->getMessage());
        throw $e; // re-throw the exception to be handled elsewhere
    }
}

function addUserToGroup($groupId, $userId)
{
    global $conn;
    $stmt = $conn->prepare("INSERT INTO group_users (group_id, user_id) VALUES (:group_id, :user_id)");
    return $stmt->execute(['group_id' => $groupId, 'user_id' => $userId]);
}

?>