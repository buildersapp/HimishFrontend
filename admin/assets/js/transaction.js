var companyTable = $("#transactionTable").DataTable({
    dom: '<"top">rt<"bottom"lp><"clear">', // Add "B" for buttons
    pageLength: 500,
    lengthMenu: [ [10, 25, 50, 100, 500], [10, 25, 50, 100, 500] ],
    buttons: [
        {
            extend: 'excelHtml5',
            text: 'Export to Excel',
            titleAttr: 'Export to Excel',
            className: 'd-none'
        }
    ],
    drawCallback: function() {
    $('html, body').animate({ scrollTop: 0 }, 'fast');
    },
    language: {
    searchPlaceholder: 'Enter your search term...',
    emptyTable: ''
    },
    serverSide: true,
    processing: false,
    sorting: false,
    ajax: {
    url: "ajax.php?action=get_transactions", // JSON datasource for companies
    type: "post",  // method, by default get
    cache: true,
    "data": function(data) {
    },
    beforeSend: function() {
        openScreenLoader('Fetching Process. Do not refresh this page...');
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

        // Show or hide no data message
        if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
            $("#transactionTable_wrapper").hide();
            $("#noDataMessage").show();
        } else {
            $("#transactionTable_wrapper").show();
            $("#noDataMessage").hide();
        }

        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Highlight search results
        if (companyTable.search()) {
            var regex = new RegExp('(' + companyTable.search() + ')', 'gi');
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

$('#searchTransaction').on('input', function() {
    var searchTerm = $(this).val();
    companyTable.search(searchTerm).draw();
});


// Delete Button 
function toggleDeleteButton() {
    const anyChecked = $(".table .chk-box:checked").length > 0;
    $("#transactionDeleteButton").toggle(anyChecked); // Show if any checkbox is checked, hide otherwise
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

// delete post
function deleteTransaction(selectedIds) {
    // Perform AJAX request
    $.ajax({
        url: "ajax.php?action=delete_transactions",
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

$('.btn-ex').on('click', function(e) {
    e.preventDefault(); // Prevent default link behavior
    companyTable.button('.buttons-excel').trigger(); // Trigger the export to Excel button
});

// Handle the delete button click
$("#transactionDeleteButton").on("click", function () {
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
    
        deleteTransaction(selectedIds);
 });
