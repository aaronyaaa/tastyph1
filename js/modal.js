$(document).ready(function() {
    $('#editProfileModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var sellerId = button.data('seller-id'); // Extract seller ID

        $.ajax({
            url: '../helpers/fetch_store_details.php',
            type: 'POST',
            data: { seller_id: sellerId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#business_name').val(response.business_name);
                    $('#description').val(response.description);
                    $('#streetname').val(response.streetname);
                    $('#barangay').val(response.barangay);
                    $('#province').val(response.province);

                    if (response.profile_pics) {
                        $('#storeProfilePic').attr('src', '../uploads/' + response.profile_pics);
                    } else {
                        $('#storeProfilePic').attr('src', '../uploads/default-store.jpg');
                    }
                } else {
                    alert('Failed to fetch store details.');
                }
            },
            error: function() {
                alert('Error fetching store details.');
            }
        });
    });
});
