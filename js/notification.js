// notification.js

// When the modal is shown, load the request details dynamically
$('#requestModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var requestId = button.data('request-id'); // Extract request ID from data-* attributes

    // Fetch the details for the specific request using AJAX
    $.ajax({
        url: 'fetch_request_details.php',
        method: 'GET',
        data: { request_id: requestId },
        success: function(response) {
            $('#requestDetails').html(response); // Insert response data into the modal
        }
    });
});


