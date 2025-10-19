// Delete Button 
function toggleDeleteButton() {
    const anyChecked = $(".table .chk-box:checked").length > 0;
    $("#webAdsDltButton").toggle(anyChecked); // Show if any checkbox is checked, hide otherwise
}

// Handle "Select All" checkbox click
$(".select-all").on("click", function () {
    var isChecked = $(this).prop("checked");
    // Set the checked state of all row checkboxes
    $(".table .chk-box").prop("checked", isChecked);
    toggleDeleteButton()

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

// delete Web Ads
function deleteWebAds(selectedIds) {
     // Perform AJAX request
    $.ajax({
        url: "ajax.php?action=delete_web_ads",
        type: "POST",
        data: { ids: selectedIds },
        success: function (response) {
            location.reload();
        },
        complete: function (response) {
             // Close loader
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
$("#webAdsDltButton").on("click", function () {
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

    deleteWebAds(selectedIds);
});