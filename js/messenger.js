document.addEventListener('DOMContentLoaded', function () {
    // Open chat modal with user information
    var chatModal = document.getElementById('chatModal');
    var chatUserId = 0;

    // Open the modal and load messages when triggered
    chatModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; // Button that triggered the modal
        chatUserId = button.getAttribute('data-user-id'); // Extract user ID

        // Load previous messages for the selected user
        loadMessages(chatUserId);
    });

    // Sending a message
    var messageForm = document.getElementById('message-form');
    if (messageForm) {
        messageForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var messageInput = document.getElementById('message-input');
            var messageText = messageInput.value.trim();

            if (messageText) {
                sendMessage(chatUserId, messageText);
                messageInput.value = ''; // Clear the input after sending
            }
        });
    }

    // Function to load messages
    function loadMessages(userId) {
        var chatBox = document.getElementById('chat-box');
        chatBox.innerHTML = ''; // Clear existing messages

        // Fetch messages using an AJAX request
        fetch('fetch_messages.php?user_id=' + userId)
            .then(response => response.json())
            .then(messages => {
                messages.forEach(function (message) {
                    var messageDiv = document.createElement('div');
                    messageDiv.classList.add('message');
                    messageDiv.classList.add(message.sender_id == <?php echo $user_id; ?> ? 'sent' : 'received');
                    messageDiv.textContent = message.message;
                    chatBox.appendChild(messageDiv);
                });

                // Scroll to the latest message
                chatBox.scrollTop = chatBox.scrollHeight;
            })
            .catch(error => {
                console.error('Error loading messages:', error);
            });
    }

    // Function to send a message
    function sendMessage(userId, messageText) {
        fetch('send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                sender_id: <?php echo $user_id; ?>, // PHP variable for sender ID
                receiver_id: userId,
                message: messageText
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    loadMessages(userId); // Reload messages after sending
                } else {
                    console.error('Message sending failed');
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
            });
    }
});
