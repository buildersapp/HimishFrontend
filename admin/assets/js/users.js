// toggleDeleteButton 
function toggleDeleteButton() {
    const anyChecked = $(".table .chk-box:checked").length > 0;
    $("#deleteAllBtn").toggle(anyChecked); // Show if any checkbox is checked, hide otherwise
    $("#showUpdCommBtn").toggle(anyChecked);
}

// Handle "Select All" checkbox click
$(".select-all").on("click", function () {
    var isChecked = $(this).prop("checked");
    // Set the checked state of all row checkboxes
    $(".table .chk-box").prop("checked", isChecked);
    toggleDeleteButton();

});

// Handle individual row checkbox click to update "Select All" state
$(".table").on("change", ".chk-box", function () {
    // Check if all checkboxes are checked
    var allChecked = $(".table .chk-box").length === $(".table .chk-box:checked").length;
    // Update "Select All" checkbox state
    $(".select-all").prop("checked", allChecked);
    // Toggle button visibility
    toggleDeleteButton();
});

// delete users
function deleteUsers(selectedIds) {
    // Perform AJAX request
    $.ajax({
        url: "ajax.php?action=deleteMultiUser",
        type: "POST",
        data: { ids: selectedIds },
        success: function (response) {
            location.reload();
        },
        complete: function (response) {
            closeScreenLoader();
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
            alert("An error occurred while deleting rows. Please try again.");
        },
        beforeSend: function() {
            openScreenLoader('Deleting Process. Do not refresh this page...');
        },
    });
}

// Handle the delete button click
$("#deleteAllBtn").on("click", function () {
   // Get all checked checkboxes
    const selectedIds = $(".table .chk-box:checked")
        .map(function () {
            return $(this).val();
        })
        .get();

    if (selectedIds.length === 0) {
        alert("No rows selected!");
        return;
    }

    // Confirm delete action
    if (!confirm("Are you sure you want to delete the selected rows?")) {
        return;
    }

    deleteUsers(selectedIds);
});

// Handle the update community button click
// Handle the update community button click
$("#showUpdCommBtn").on("click", function () {
    // Get all checked checkboxes
    const selectedIds = $(".table .chk-box:checked")
        .map(function () {
            return $(this).val();
        })
        .get();

    if (selectedIds.length === 0) {
        alert("No rows selected!");
        return;
    }

    // Add # before each ID
    const hashedIds = selectedIds.map(id => `#${id}`);

    // Set the comma-separated hashed IDs
    $("#auc_user_ids").val(selectedIds);
    $('#label_AUC').html('Add Community for : ' + hashedIds.join(", "));  // Display as "#123, #456"

    // Initialize select2 inside the modal
    $('#communityAUC').select2({
        placeholder: "Select community",
        allowClear: true,
        dropdownParent: $('#addUpdateCommunityModal') // Important for z-index issue
    });

    $("#addUpdateCommunityModal").modal('show');
});
