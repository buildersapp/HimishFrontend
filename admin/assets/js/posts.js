var postTable = $(".postsListTable").DataTable({
dom: '<"top">rt<"bottom"lp><"clear">',
pageLength: 500,
drawCallback: function() {
$('html, body').animate({ scrollTop: 0 }, 'fast');
},
language: {
searchPlaceholder: 'Enter your search term...',
emptyTable: ''
},
serverSide: true,
processing: false,
sorting: true,
columnDefs: [
    { orderable: false, targets: 0 }, 
    { orderable: true, targets: "_all" }    // Disable sorting for all other columns
],
order: [[1, "asc"]],
ajax: {
url: "ajax.php?action=get_posts", // JSON datasource for companies
type: "post",  // method, by default get
cache: true,
"data": function(data) {

},
beforeSend: function() {
    openScreenLoader('Fetching Posts. Do not refresh this page...');
},
error: function(jqXHR) {
    try {
        console.log('inner')
        // Attempt to parse the response
        var response = JSON.parse(jqXHR.responseText);

        // Redirect if 401 Unauthorized
        if (response.redirect) {
            window.location.href = response.url;
        } else if (response.message) {
            // Show error message if available
            alert("Error: " + response.message);
        }
    } catch (e) {
        // Handle cases where the response is not JSON
        console.error("Unexpected server response:", jqXHR.responseText);
        alert("An unexpected error occurred. Please try again later.");
    }
},
complete: function(data) {

    $('.user-dropdown').select2();

    // Show or hide no data message
    if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
        $("#postsListTable_wrapper").hide();
        $("#noDataPostMessage").show();
    } else {
        $("#postsListTable_wrapper").show();
        $("#noDataPostMessage").hide();
    }

    // Handle the increase view
    $(".increaseViewBtn").on("click", function () {
        var postId = $(this).data("post-id"); // single row case
        let increaseAmount = prompt("Enter the number of views to increase:");

        // Validate input
        if (increaseAmount === null || isNaN(increaseAmount) || parseInt(increaseAmount) <= 0) {
            alert("Please enter a valid positive number.");
            return;
        }

        // --- Multi-select checkboxes ---
        const selectedIds = $(".table .chk-box:checked")
            .map(function () {
                return $(this).val();
            })
            .get();

        let idsToUpdate = [];
        if (selectedIds.length > 0) {
            // Multi-select mode
            if (!confirm(`Are you sure you want to increase views by ${increaseAmount} for ${selectedIds.length} rows?`)) {
                return;
            }
            idsToUpdate = selectedIds;
        } else {
            // Single row mode
            if (!postId) {
                alert("No rows selected and no post id found!");
                return;
            }
            if (!confirm(`Are you sure you want to increase views by ${increaseAmount} for this row?`)) {
                return;
            }
            idsToUpdate = [postId];
        }

        // Perform AJAX request
        $.ajax({
            url: "ajax.php?action=increase_view",
            type: "POST",
            data: { increaseAmount: increaseAmount, postIds: idsToUpdate },
            traditional: true, // ensures arrays postIds[]=1&postIds[]=2
            beforeSend: function () {
                openScreenLoader(`Increasing ${increaseAmount} views. Do not refresh this page...`);
            },
            success: function (response) {
                location.reload();
            },
            complete: function () {
                closeScreenLoader();
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });


    // Handle the increase share count
    $(".increaseShareBtn").on("click", function () {
        var postId = $(this).data('post-id');
        // Show an input alert for the number of views
        let increaseAmount = prompt("Enter the number of shares to increase:");

        // Validate input
        if (increaseAmount === null || isNaN(increaseAmount) || parseInt(increaseAmount) <= 0) {
            alert("Please enter a valid positive number.");
            return;
        }
        
        // Confirm action
        if (!confirm(`Are you sure you want to increase shares by ${increaseAmount}?`)) {
            return;
        }

        // Perform AJAX request
        $.ajax({
            url: "ajax.php?action=increase_share_count",
            type: "POST",
            data: { increaseAmount: increaseAmount, postId: postId },
            beforeSend: function () {
                openScreenLoader(`Increasing ${increaseAmount} shares. Do not refresh this page...`);
            },
            success: function (response) {
                location.reload();
            },
            complete: function () {
                closeScreenLoader();
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", status, error);
            }
        });
    });

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Highlight search results
    if (postTable.search()) {
        var regex = new RegExp('(' + postTable.search() + ')', 'gi');
        $('.content').each(function() {
            var content = $(this);
            var text = content.text();
            if (text.match(regex)) {
                content.parent().addClass('highlight');
            }
        });
    }

    // Close loader
    closeScreenLoader();
}
},
rowCallback: function(row, data, index) {
if (index % 2 === 0) {
    $(row).addClass('second-row'); // Even row
} else {
    $(row).addClass('first-row'); // Odd row
}
}
});
// datatable search
$('#pSearch').on('input', function() {
var searchTerm = $(this).val();
postTable.search(searchTerm).draw();
});

// Delete Button 
function toggleDeleteButton() {
    const anyChecked = $(".table .chk-box:checked").length > 0;
    $("#postDltButton").toggle(anyChecked); // Show if any checkbox is checked, hide otherwise
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

// changePostStatus
function changePostStatus(element){

    var userId = element.getAttribute("user-id");
    var status = element.getAttribute("status");

    var data = {
        post_id: userId,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changePostStatus',
        type: 'POST',
        data: data,
        beforeSend: function () {
            openScreenLoader('Updating Status. Do not refresh this page...');
        },
        success: function (response) {
            location.reload();
        },
        error: function (xhr, status, error) {
            console.error("Error: " + error);
            closeScreenLoader();
        },
        complete: function () {
            closeScreenLoader();
        }
    });
};



// delete post
function deletePost(selectedIds) {
     // Perform AJAX request
    $.ajax({
        url: "ajax.php?action=delete_posts",
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
$("#postDltButton").on("click", function () {
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

    deletePost(selectedIds);
});
// Handle the increase view
$("#increaseButton").on("click", function () {
    // Show an input alert for the number of views
    let increaseAmount = prompt("Enter the number of views to increase:");

    // Validate input
    if (increaseAmount === null || isNaN(increaseAmount) || parseInt(increaseAmount) <= 0) {
        alert("Please enter a valid positive number.");
        return false;
    }

    // --- Multi-select checkboxes ---
    const selectedIds = $(".table .chk-box:checked")
        .map(function () {
            return $(this).val();
        })
        .get();

    if (selectedIds.length === 0) {
        alert("No rows selected!");
        return false;
    }

    // Confirm action
    if (!confirm(`Are you sure you want to increase views by ${increaseAmount} for ${selectedIds.length} rows?`)) {
        return false;
    }

    // Perform AJAX request
    $.ajax({
        url: "ajax.php?action=increase_view",
        type: "POST",
        data: { increaseAmount: increaseAmount, postIds: selectedIds.join(',') },
        traditional: true, // send arrays as postIds[]=1&postIds[]=2
        beforeSend: function () {
            openScreenLoader(`Increasing views by ${increaseAmount}. Do not refresh this page...`);
        },
        success: function (response) {
            location.reload();
        },
        complete: function () {
            closeScreenLoader();
        },
        error: function (xhr, status, error) {
            console.error("AJAX Error:", status, error);
        }
    });
});


