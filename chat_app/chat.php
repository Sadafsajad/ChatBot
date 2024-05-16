<?php
require 'db.php';
require 'auth.php';
require 'functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getUserById($_SESSION['user_id']);
$contacts = getUsers($user['id']);
$groups = getGroups($user['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Chatbot</title>
    <style>
        /* Reset some default styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f2f2f2;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .chat-container {
            display: flex;
            max-width: 800px;
            width: 100%;
            height: 600px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .contact-list {
            width: 250px;
            background-color: #3f51b5;
            color: #fff;
            padding: 20px;
        }

        .contact-list-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .contact-search input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: none;
            margin-bottom: 20px;
        }

        .contact-list-items {
            list-style: none;
        }

        .contact-list-items li {
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .contact-list-items li:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .chat-window {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .chat-header {
            background-color: #3f51b5;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-info {
            font-weight: bold;
        }

        .chat-actions a {
            color: #fff;
            text-decoration: none;
            margin-left: 10px;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .chat-input {
            background-color: #f2f2f2;
            padding: 10px;
            display: flex;
            align-items: center;
            position: relative;
        }

        .chat-input input {
            flex: 1;
            padding: 10px;
            border-radius: 5px;
            border: none;
            padding-right: 40px; 
        }

        .chat-input button {
            background-color: #3f51b5;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            margin-left: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .chat-input button:hover {
            background-color: #303f9f;
        }
        .welcome-message {
            background-color: #d4edda; /* Light green background */
            color: #155724; /* Dark green text color */
            padding: 10px 20px; /* Padding for content */
            border-radius: 10px; /* Rounded corners */
            margin: auto; /* Center the message horizontally */
            margin-top: 20px; /* Add some space at the top */
            max-width: 80%; /* Limit width to 80% of container */
        }
        .file-icon {
            position: absolute; /* Position the icon */
            right: 30px; /* Adjust position from the right */
            top: 50%; /* Center vertically */
            transform: translateY(-50%); /* Center vertically */
            color: #999; /* Icon color */
            cursor: pointer; /* Show cursor on hover */
        }
        .send-icon {
            position: absolute; /* Position the icon */
            right: 10px; /* Adjust position from the right */
            top: 50%; /* Center vertically */
            transform: translateY(-50%); /* Center vertically */
            color: #3f51b5; /* Icon color */
            cursor: pointer; /* Show cursor on hover */
        }
        .file-icon:hover,
        .send-icon:hover {
            color: #303f9f; /* Change color on hover */
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="chat-container">
        <div class="contact-list">
            <div class="contact-list-header">Messages</div>
            <div class="contact-search">
                <input type="text" placeholder="Search contacts">
            </div>
            <ul class="contact-list-items">
                <?php foreach ($contacts as $contact): ?>
                    <li data-id="<?php echo $contact['id']; ?>"><?php echo $contact['username']; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="chat-window">
            <div class="chat-header">
                <div class="chat-info">
                    <!-- Contact name and status -->
                </div>
                <div class="chat-actions"><?php echo $user['username']; ?> <a href="logout.php">(Logout)</a>
                    <!-- Chat actions like video call, etc. -->
                </div>
            </div>
            <div class="chat-messages">
                <!-- Messages will be dynamically added here -->
            </div>
            <div class="chat-input">
                <!-- Input field with link icon for file selection -->
                <input type="text" id="message-input" placeholder="Type a message...">
                <i class="fas fa-link file-icon" id="file-select-icon"></i>
                <!-- File input field (hidden) -->
                <input type="file" id="file-input" style="display: none;">
                <!-- Plane icon for sending message -->
                <i class="fas fa-paper-plane send-icon" id="send-icon"></i>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
    var userId = <?php echo $_SESSION['user_id']; ?>;
            var contactId;

            // Function to display welcome message
            function displayWelcomeMessage() {
                // Apply CSS directly to the welcome message
                $('.chat-messages').html('<div class="message welcome-message">Welcome to the chat! Select a contact to start chatting.</div>');
            }

            // Function to load messages
            function loadMessages() {
                $.ajax({
                    url: 'messages.php',
                    type: 'POST',
                    data: {
                        user_id: userId,
                        contact_id: contactId
                    },
                    success: function (data) {
                        $('.chat-messages').html(data);
                    }
                });
            }

            // Function to update chat header with contact's name
            function updateChatHeader(contactName) {
                $('.chat-header .chat-info').text(contactName);
            }

            // Initialize chat window with welcome message
            displayWelcomeMessage();

            // Click event handler for contacts
            $('.contact-list-items li').on('click', function () {
                contactId = $(this).data('id');
                var contactName = $(this).text(); // Get the clicked contact's name
                updateChatHeader(contactName); // Update chat header with contact's name
                loadMessages(); // Load messages for the selected contact
            });

            // Click event handler for plane icon (send message)
            $('#send-icon').on('click', function () {
                var message = $('#message-input').val();
                if (message.trim() !== '') {
                    $.ajax({
                        url: 'messages.php',
                        type: 'POST',
                        data: {
                            user_id: userId,
                            contact_id: contactId,
                            message: message
                        },
                        success: function () {
                            loadMessages();
                            $('#message-input').val('');
                        }
                    });
                }
            });

            // Click event handler for link icon (file selection)
            $('#file-select-icon').on('click', function () {
                $('#file-input').click(); // Trigger click event on hidden file input
            });

            // Change event handler for file input
            $('#file-input').on('change', function () {
                var fileInput = $(this)[0].files[0];
                var formData = new FormData();
                formData.append('file', fileInput);
                $.ajax({
                    url: 'upload.php', // PHP script to handle file upload
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function () {
                        loadMessages();
                    }
                });
            });
        });

    </script>