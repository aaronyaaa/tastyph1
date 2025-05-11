<?php
session_start();
include('../database/config.php');


$userType = $_SESSION['usertype'] ?? 'user'; // Default to 'user' if not set

$sender_id = $_SESSION['userId'];
$sql = "SELECT id, first_name, last_name FROM users WHERE id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $sender_id);
$stmt->execute();
$users = $stmt->get_result();
$userType = $_SESSION['usertype'] ?? 'user';

$receiver_id = $_GET['receiver_id'] ?? null;
$messagesArray = [];
if ($receiver_id) {
    $sql = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY timestamp ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    $stmt->execute();
    $messages = $stmt->get_result();

    while ($message = $messages->fetch_assoc()) {
        $messagesArray[] = $message;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messenger Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/messenger.css">
    <link rel="stylesheet" href="../css/nav.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle" title="Toggle Sidebar">
        <i class="bi bi-list"></i>
    </button>

    <div class="chat-container">
        <!-- User List Sidebar -->
        <div class="user-list-container" id="userListContainer">
            <div class="user-list-header">
                <h2>Chats</h2>
            </div>
            <div class="user-list">
                <?php while ($user = $users->fetch_assoc()): ?>
                    <a href="?receiver_id=<?= $user['id'] ?>" 
                       class="user-item <?= ($receiver_id == $user['id']) ? 'active' : '' ?>">
                        <?php if (!empty($user['profile_pics'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($user['profile_pics']) ?>" 
                                 alt="Profile" 
                                 class="user-avatar">
                        <?php else: ?>
                            <div class="user-avatar bg-secondary d-flex align-items-center justify-content-center">
                                <i class="bi bi-person-fill text-light"></i>
                            </div>
                        <?php endif; ?>
                        <div class="user-info">
                            <div class="user-name">
                                <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Chat Box Container -->
        <div class="chat-box-container">
            <?php if ($receiver_id): ?>
                <?php
                $sql = "SELECT first_name, last_name, profile_pics FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $receiver_id);
                $stmt->execute();
                $receiver = $stmt->get_result()->fetch_assoc();
                ?>

                <!-- Chat Header -->
                <div class="chat-header">
                    <?php if (!empty($receiver['profile_pics'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($receiver['profile_pics']) ?>" 
                             alt="Profile" 
                             class="user-avatar">
                    <?php else: ?>
                        <div class="user-avatar bg-secondary d-flex align-items-center justify-content-center">
                            <i class="bi bi-person-fill text-light"></i>
                        </div>
                    <?php endif; ?>
                    <div class="user-info ms-3">
                        <div class="user-name">
                            <?= htmlspecialchars($receiver['first_name'] . ' ' . $receiver['last_name']) ?>
                        </div>
                    </div>
                </div>

                <!-- Chat Messages -->
                <div class="chat-box" id="chatBox">
                    <?php foreach ($messagesArray as $row): ?>
                        <div class="d-flex <?= $row['sender_id'] == $sender_id ? 'justify-content-end' : 'justify-content-start' ?> mb-3">
                            <div class="message-container">
                                <?php if (!empty($row['image_url'])): ?>
                                    <div class="message-images">
                                        <?php 
                                        $images = explode(',', $row['image_url']);
                                        foreach ($images as $image): 
                                        ?>
                                            <img src="../<?= htmlspecialchars(trim($image)) ?>" 
                                                 class="img-fluid rounded mb-2" 
                                                 style="max-width: 200px; cursor: pointer;"
                                                 onclick="window.open(this.src, '_blank')">
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($row['message_text'])): ?>
                                    <div class="message-bubble <?= $row['sender_id'] == $sender_id ? 'message-sent' : 'message-received' ?>">
                                        <?= htmlspecialchars($row['message_text']) ?>
                                    </div>
                                <?php endif; ?>

                                <div class="message-time">
                                    <?= date('g:i A', strtotime($row['timestamp'])) ?>
                                </div>

                                <?php if (!empty($row['reactions'])): ?>
                                    <div class="reaction-container">
                                        <?= htmlspecialchars($row['reactions']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Chat Input -->
                <form id="chatForm" method="POST" action="../helpers/send_message.php" enctype="multipart/form-data" class="chat-input-container">
                    <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($receiver_id) ?>">
                    
                    <div class="input-group">
                        <div class="chat-actions">
                            <button type="button" class="action-button" onclick="triggerFileInput()">
                                <i class="bi bi-image"></i>
                            </button>
                            <input type="file" name="images[]" id="imageInput" accept="image/*" class="d-none" multiple onchange="previewImages(event)">
                        </div>
                        
                        <textarea name="message" 
                                  class="form-control message-input" 
                                  placeholder="Type a message..."
                                  rows="1"
                                  style="resize: none;"></textarea>
                        
                        <button type="submit" class="btn send-button">
                            <i class="bi bi-send-fill"></i>
                        </button>
                    </div>

                    <div id="imagePreviewContainer" class="image-preview-container"></div>
                </form>

            <?php else: ?>
                <div class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-center text-muted">
                        <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                        <h3 class="mt-3">Select a conversation</h3>
                        <p>Choose from your existing conversations or start a new one</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle Functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const userListContainer = document.getElementById('userListContainer');
        let isSidebarCollapsed = false;

        // Check for saved sidebar state
        const savedState = localStorage.getItem('sidebarCollapsed');
        if (savedState === 'true') {
            userListContainer.classList.add('collapsed');
            isSidebarCollapsed = true;
            sidebarToggle.innerHTML = '<i class="bi bi-chevron-right"></i>';
        }

        sidebarToggle.addEventListener('click', () => {
            isSidebarCollapsed = !isSidebarCollapsed;
            userListContainer.classList.toggle('collapsed');
            sidebarToggle.innerHTML = isSidebarCollapsed ? 
                '<i class="bi bi-chevron-right"></i>' : 
                '<i class="bi bi-chevron-left"></i>';
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', isSidebarCollapsed);
        });

        // Mobile sidebar toggle
        if (window.innerWidth <= 768) {
            userListContainer.classList.add('collapsed');
            sidebarToggle.style.display = 'block';
        }

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth <= 768) {
                userListContainer.classList.add('collapsed');
                sidebarToggle.style.display = 'block';
            } else {
                sidebarToggle.style.display = 'none';
                // Restore saved state for desktop
                if (savedState === 'true') {
                    userListContainer.classList.add('collapsed');
                } else {
                    userListContainer.classList.remove('collapsed');
                }
            }
        });

        // Auto-scroll to bottom of chat
        function scrollToBottom() {
            const chatBox = document.getElementById('chatBox');
            if (chatBox) {
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        }

        // Call on page load
        window.onload = scrollToBottom;

        // Preview images before sending
        function previewImages(event) {
            const container = document.getElementById('imagePreviewContainer');
            container.innerHTML = '';
            
            Array.from(event.target.files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'position-relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="preview-image">
                        <button type="button" class="remove-image" onclick="removeImage(${index})">
                            <i class="bi bi-x"></i>
                        </button>
                    `;
                    container.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        // Remove image from preview
        function removeImage(index) {
            const input = document.getElementById('imageInput');
            const dt = new DataTransfer();
            const files = input.files;
            
            for (let i = 0; i < files.length; i++) {
                if (i !== index) {
                    dt.items.add(files[i]);
                }
            }
            
            input.files = dt.files;
            previewImages({ target: input });
        }

        // Auto-resize textarea
        const textarea = document.querySelector('.message-input');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        }

        // Trigger file input
        function triggerFileInput() {
            document.getElementById('imageInput').click();
        }
    </script>
</body>

</html>