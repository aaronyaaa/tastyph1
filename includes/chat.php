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
    <title>Chat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/chat.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/modal.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function toggleTimestamp(messageElement) {
            $(messageElement).next('.timestamp').toggle();
        }

        function triggerFileInput() {
            document.getElementById("imageInput").click();
        }

        function previewImage(event) {
            let file = event.target.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $("#imagePreview").attr("src", e.target.result).show();
                    $("#sendImageForm").submit(); // Auto-submit when an image is selected
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>

<body>

    <?php include("../includes/nav_" . strtolower($userType) . ".php"); ?>

    <div class="container mt-5 chat-container">
        <!-- User List with Profile Pictures -->

        <ul class="list-group">
            <?php while ($user = $users->fetch_assoc()): ?>
                <li class="list-group-item d-flex align-items-center">
                    <a href="?receiver_id=<?= $user['id'] ?>" class="d-flex align-items-center text-decoration-none text-dark">
                        <!-- Display Profile Picture -->
                        <?php if (!empty($user['profile_pics'])): ?>
                            <img src="../uploads/<?= htmlspecialchars($user['profile_pics']) ?>" alt="User Photo"
                                class="rounded-circle me-2"
                                style="width: 40px; height: 40px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2"
                                style="width: 40px; height: 40px;">
                                <i class="bi bi-person-fill text-light fs-4"></i>
                            </div>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></span>
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>

        <div class="chat-box-container">
            <?php if ($receiver_id): ?>
                <!-- Fetch Receiver's Profile Picture and Name -->
                <?php
                $sql = "SELECT first_name, last_name, profile_pics FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $receiver_id);
                $stmt->execute();
                $receiver = $stmt->get_result()->fetch_assoc();
                ?>

                <!-- Chat Header with Profile Picture -->
                <h3 class="d-flex align-items-center">
                    <?php if (!empty($receiver['profile_pics'])): ?>
                        <img src="../uploads/<?= htmlspecialchars($receiver['profile_pics']) ?>" alt="User Photo"
                            class="rounded-circle me-2"
                            style="width: 40px; height: 40px; object-fit: cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2"
                            style="width: 40px; height: 40px;">
                            <i class="bi bi-person-fill text-light fs-4"></i>
                        </div>
                    <?php endif; ?>

                    <span><?= htmlspecialchars($receiver['first_name'] . ' ' . $receiver['last_name']) ?></span>
                </h3>

                <div class="chat-box">
                    <?php foreach ($messagesArray as $row): ?>
                        <div class="d-flex <?= $row['sender_id'] == $sender_id ? 'justify-content-end' : 'justify-content-start' ?> align-items-center mb-2">

                            <!-- Fetch sender's profile picture -->
                            <?php
                            $senderProfileQuery = "SELECT profile_pics FROM users WHERE id = ?";
                            $stmt = $conn->prepare($senderProfileQuery);
                            $stmt->bind_param("i", $row['sender_id']);
                            $stmt->execute();
                            $senderProfile = $stmt->get_result()->fetch_assoc();
                            $senderProfilePic = $senderProfile['profile_pics'] ?? '';
                            ?>

                            <!-- Show Profile Picture for Received Messages -->
                            <?php if ($row['sender_id'] != $sender_id): ?>
                                <?php if (!empty($senderProfilePic)): ?>
                                    <img src="../uploads/<?= htmlspecialchars($senderProfilePic) ?>" alt="Sender Photo"
                                        class="rounded-circle me-2"
                                        style="width: 40px; height: 40px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center me-2"
                                        style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill text-light fs-4"></i>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>

                            <div class="<?= $row['sender_id'] == $sender_id ? 'text-end' : 'text-start' ?> message-container">
                                <!-- Display multiple images -->
                                <?php if (!empty($row['image_url'])):
                                    $images = explode(',', $row['image_url']);
                                    foreach ($images as $image): ?>
                                        <img src="../<?= htmlspecialchars(trim($image)) ?>" class="chat-image img-thumbnail me-1"
                                            style="max-width: 80px; cursor: pointer;"
                                            onclick="window.open(this.src, '_blank')">
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <div class="message-content">
                <!-- Message Text -->
                <?php if (!empty($row['message_text'])): ?>
                    <p class="message"><?= htmlspecialchars($row['message_text']) ?></p>
                <?php endif; ?>

                <!-- Reactions Display -->
                <div class="reaction-display">
                    <?= htmlspecialchars($row['reactions']) ?>
                </div>

                <!-- Timestamp -->
                <small class="timestamp"><?= date('Y-m-d H:i:s', strtotime($row['timestamp'])) ?></small>
            </div>

            <!-- Message Options Menu -->
            <div class="message-options position-absolute top-0 end-0" style="display: none;">
                <button class="btn btn-sm btn-light react-btn">😀</button>
                <button class="btn btn-sm btn-danger delete-message" data-message-id="<?= $row['message_id'] ?>">Unsend</button>
            </div>

            <!-- Reaction Emoji Menu -->
            <div class="reaction-menu position-absolute top-0 end-0 bg-light p-1 rounded shadow-sm" style="display: none;">
                <button class="btn btn-sm" data-message-id="<?= $row['message_id'] ?>">❤️</button>
                <button class="btn btn-sm" data-message-id="<?= $row['message_id'] ?>">😂</button>
                <button class="btn btn-sm" data-message-id="<?= $row['message_id'] ?>">😮</button>
                <button class="btn btn-sm" data-message-id="<?= $row['message_id'] ?>">😢</button>
                <button class="btn btn-sm" data-message-id="<?= $row['message_id'] ?>">😡</button>
                <button class="btn btn-sm" data-message-id="<?= $row['message_id'] ?>">👍</button>
            </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>



                <!-- Message Input -->
                <form id="chatForm" method="POST" action="../helpers/send_message.php" enctype="multipart/form-data">
                    <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($receiver_id) ?>" />

                    <form id="chatForm" method="POST" action="../helpers/send_message.php" enctype="multipart/form-data" class="chat-input-container">
                        <input type="hidden" name="receiver_id" value="<?= htmlspecialchars($receiver_id) ?>" />

                        <!-- Image Preview Above Text Box -->
                        <div id="imagePreviewContainer"></div>

                        <div class="input-group">
                            <!-- Multi-image input -->
                            <input type="file" name="images[]" id="imageInput" accept="image/*" class="d-none" multiple onchange="previewImages(event)" />

                            <!-- Add Image Button -->
                            <button type="button" class="btn btn-outline-secondary add-image-btn" onclick="triggerFileInput()">📷</button>

                            <!-- Text Message -->
                            <textarea name="message" id="messageInput" rows="1" class="form-control border-0" placeholder="Type a message..." style="resize: none;"></textarea>

                            <!-- Send Button -->
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </form>


                </form>

                <img id="imagePreview" src="" class="preview-img d-none" />
            <?php else: ?>
                <p>Select a user to start a conversation.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/chat.js"></script>



</body>

</html>