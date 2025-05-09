document.querySelectorAll(".ingredient-link").forEach(function(element) {
    element.addEventListener("click", function(event) {
        event.preventDefault();
        const ingredient = this.getAttribute("data-ingredient");
        if (ingredient) {
            fetchSearchResults(ingredient);  // Trigger search dynamically
        }
    });
});

// Function to fetch search results based on the ingredient clicked
function fetchSearchResults(query) {
    fetch(`search_page.php?ajax=1&query=${query}`)
        .then(response => response.json())
        .then(data => {
            const resultsContainer = document.getElementById("search-results");
            resultsContainer.innerHTML = "";  // Clear previous results

            if (data.length > 0) {
                data.forEach(item => {
                    let resultItem = document.createElement("div");
                    resultItem.classList.add("result-item");

                    if (item.type === "product") {
                        resultItem.innerHTML = `
                            <div class="card">
                                <img src="../uploads/${item.image_url}" class="card-img-top" alt="${item.name}">
                                <div class="card-body">
                                    <h5 class="card-title">${item.name}</h5>
                                    <p><strong>₱${parseFloat(item.price).toFixed(2)}</strong></p>
                                </div>
                            </div>
                        `;
                    } else if (item.type === "ingredient") {
                        resultItem.innerHTML = `
                            <div class="card">
                                <img src="../uploads/${item.image_url}" class="card-img-top" alt="${item.name}">
                                <div class="card-body">
                                    <h5 class="card-title">${item.name}</h5>
                                    <p><strong>₱${parseFloat(item.price).toFixed(2)}</strong></p>
                                </div>
                            </div>
                        `;
                    } else if (item.type === "seller") {
                        resultItem.innerHTML = `
                            <div class="card">
                                <img src="../uploads/${item.image_url}" class="card-img-top" alt="${item.name}">
                                <div class="card-body">
                                    <h5 class="card-title">${item.name}</h5>
                                </div>
                            </div>
                        `;
                    }

                    // Append result item to results container
                    resultsContainer.appendChild(resultItem);
                });
            } else {
                resultsContainer.innerHTML = "<p>No results found.</p>";
            }
        })
        .catch(err => {
            console.log('Error fetching results: ', err);
        });
}
document.addEventListener('DOMContentLoaded', function () {
    const stickyNote = document.getElementById('stickyNote');
    const closeButton = document.getElementById('closeStickyNote');

    // Make sticky note draggable
    let offsetX, offsetY, isDragging = false;

    stickyNote.addEventListener('mousedown', function (e) {
        isDragging = true;
        offsetX = e.clientX - stickyNote.offsetLeft;
        offsetY = e.clientY - stickyNote.offsetTop;
        stickyNote.classList.add('dragging');
    });

    document.addEventListener('mousemove', function (e) {
        if (isDragging) {
            stickyNote.style.left = `${e.clientX - offsetX}px`;
            stickyNote.style.top = `${e.clientY - offsetY}px`;
        }
    });

    document.addEventListener('mouseup', function () {
        isDragging = false;
        stickyNote.classList.remove('dragging');
        // Save the position of the sticky note
        localStorage.setItem('stickyNotePosition', JSON.stringify({
            left: stickyNote.style.left,
            top: stickyNote.style.top
        }));
    });

    // Close sticky note on button click
    closeButton.addEventListener('click', function () {
        stickyNote.style.display = 'none';
        localStorage.setItem('stickyNoteVisible', 'false');
    });

    // Check for stored position and visibility
    const storedPosition = JSON.parse(localStorage.getItem('stickyNotePosition'));
    const isStickyNoteVisible = localStorage.getItem('stickyNoteVisible') !== 'false';

    if (storedPosition && isStickyNoteVisible) {
        stickyNote.style.left = storedPosition.left;
        stickyNote.style.top = storedPosition.top;
    } else {
        stickyNote.style.display = 'none';
    }
});
