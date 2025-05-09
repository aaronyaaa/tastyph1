function toggleOptions(element, messageId) {
    document.querySelectorAll('.message-options').forEach(el => el.style.display = 'none');
    document.getElementById('options-' + messageId).style.display = 'block';
}

function reactToMessage(id) {
    alert("Reacting to message: " + id);
}

function replyToMessage(id) {
    alert("Replying to message: " + id);
}

function pinMessage(id) {
    alert("Message pinned: " + id);
}

function removeMessage(id) {
    alert("Message removed: " + id);
}

function triggerFileInput() {
    document.getElementById("imageInput").click();
}


let selectedImages = []; // Store selected images

function triggerFileInput() {
    document.getElementById("imageInput").click();
}

function previewImages(event) {
    let files = event.target.files;
    let previewContainer = document.getElementById("imagePreviewContainer");

    let newFiles = []; // Store newly selected files
    let dt = new DataTransfer(); // DataTransfer to keep track of files

    for (let i = 0; i < files.length; i++) {
        let file = files[i];

        // Check if file is already added
        if (!selectedImages.some(img => img.name === file.name)) {
            selectedImages.push(file);
            newFiles.push(file);

            let reader = new FileReader();
            reader.onload = function(e) {
                let previewDiv = document.createElement("div");
                previewDiv.classList.add("position-relative", "me-1");

                let previewImage = document.createElement("img");
                previewImage.src = e.target.result;
                previewImage.classList.add("rounded", "shadow-sm");
                previewImage.style.width = "50px";
                previewImage.style.height = "50px";
                previewImage.style.objectFit = "cover";
                previewImage.style.marginRight = "5px";

                let removeBtn = document.createElement("button");
                removeBtn.innerHTML = "×";
                removeBtn.classList.add("btn", "btn-sm", "btn-danger", "position-absolute", "top-0", "end-0");
                removeBtn.style.fontSize = "12px";
                removeBtn.style.lineHeight = "12px";
                removeBtn.style.padding = "0px 4px";

                removeBtn.onclick = function () {
                    previewDiv.remove();
                    selectedImages = selectedImages.filter(img => img.name !== file.name);
                    updateFileInput();
                };

                previewDiv.appendChild(previewImage);
                previewDiv.appendChild(removeBtn);
                previewContainer.appendChild(previewDiv);

                // Show preview container when there are images
                previewContainer.style.display = "flex";
            };
            reader.readAsDataURL(file);
        }
    }

    // Keep previously selected files in input
    selectedImages.forEach(file => dt.items.add(file));
    document.getElementById("imageInput").files = dt.files;

    // Toggle preview container visibility
    togglePreviewContainer();
}

function updateFileInput() {
    let dt = new DataTransfer();
    selectedImages.forEach(file => dt.items.add(file));
    document.getElementById("imageInput").files = dt.files;

    // Toggle preview container visibility
    togglePreviewContainer();
}

// Show/Hide the preview container dynamically
function togglePreviewContainer() {
    let previewContainer = document.getElementById("imagePreviewContainer");
    if (selectedImages.length > 0) {
        previewContainer.style.display = "flex"; // Show container
    } else {
        previewContainer.style.display = "none"; // Hide container when empty
    }
}



document.addEventListener("DOMContentLoaded", function () {
    // Show/hide message options when hovering
    document.querySelectorAll(".message-container").forEach(container => {
        let optionsMenu = container.querySelector(".message-options");
        let reactionMenu = container.querySelector(".reaction-menu");

        container.addEventListener("mouseenter", () => {
            optionsMenu.style.display = "block";
        });

        container.addEventListener("mouseleave", () => {
            optionsMenu.style.display = "none";
            reactionMenu.style.display = "none"; // Hide reactions
        });

        // Toggle reaction menu
        let reactBtn = container.querySelector(".react-btn");
        if (reactBtn) {
            reactBtn.addEventListener("click", () => {
                reactionMenu.style.display = reactionMenu.style.display === "block" ? "none" : "block";
            });
        }
    });

    // Handle message deletion
    document.querySelectorAll(".delete-message").forEach(button => {
        button.addEventListener("click", function () {
            let messageId = this.getAttribute("data-message-id");

            if (confirm("Are you sure you want to unsend this message?")) {
                fetch("../helpers/delete_message.php", {
                    method: "POST",
                    body: JSON.stringify({ message_id: messageId }),
                    headers: { "Content-Type": "application/json" }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`message-${messageId}`).remove();
                    } else {
                        alert("Failed to delete message.");
                    }
                });
            }
        });
    });

    // Handle emoji reactions
    document.querySelectorAll(".reaction-menu button").forEach(button => {
        button.addEventListener("click", function () {
            let messageId = this.getAttribute("data-message-id");
            let reaction = this.innerText;

            fetch("../helpers/react_message.php", {
                method: "POST",
                body: JSON.stringify({ message_id: messageId, reaction: reaction }),
                headers: { "Content-Type": "application/json" }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let reactionDisplay = document.querySelector(`#message-${messageId} .reaction-display`);
                    reactionDisplay.innerText = reaction;
                }
            });
        });
    });
});
