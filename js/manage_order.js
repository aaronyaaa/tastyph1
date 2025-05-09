document.addEventListener('DOMContentLoaded', function () {
    // Select All functionality
    const selectAllCheckbox = document.getElementById('select-all');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('input[name="order_ids[]"]');
            checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        });
    }

    // Handle individual status updates using AJAX
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.addEventListener('change', function () {
            const orderId = this.getAttribute('data-order-id');
            const newStatus = this.value;

            // Send AJAX request to update status
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('order_status', newStatus);

            fetch('../update_order_status.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.text())
            .then(result => {
                if (result === 'Success') {
                    // Update the status on the page without reloading
                    const statusBadge = document.getElementById('status-badge-' + orderId);
                    statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    statusBadge.className = 'badge ' + getStatusClass(newStatus);
                } else {
                    alert('Error updating status: ' + result);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('There was an issue updating the status.');
            });
        });
    });

    // Function to get appropriate badge class for the status
    function getStatusClass(status) {
        switch (status) {
            case 'pending': return 'bg-warning';
            case 'confirmed': return 'bg-primary';
            case 'packed': return 'bg-info';
            case 'delivered': return 'bg-success';
            case 'cancelled': return 'bg-danger';
            default: return 'bg-secondary';
        }
    }
});
