$('#community').select2({placeholder: "Select community", // Placeholder text
allowClear: true,
search:true,
});

function base64Encode(input) {
    return btoa(input);
}



// Attach event listener for clicks on selected <li> items
$(document).on('click', '.select2-selection__choice', function (e) {
    // Get the database ID from data-select2-id
    const title = $(this).attr('title');

    const dataSelect2Id = $('#community option[value="' + title + '"]').data('id');

    if (dataSelect2Id) {
        // Encode the selected ID to Base64
        const encodedId = base64Encode(dataSelect2Id);
        
        // Construct the URL with the encoded ID
        const url = `community-details.php?id=${encodedId}`;
        
        // Open the URL in a new tab
        window.open(url, '_blank');
    }
});

var companyTable = $("#adsTable").DataTable({
dom: '<"top">rt<"bottom"lp><"clear">',
pageLength: 500,
lengthMenu: [ [10, 25, 50, 100, 500], [10, 25, 50, 100, 500] ],
drawCallback: function() {
$('html, body').animate({ scrollTop: 0 }, 'fast');
},
language: {
searchPlaceholder: 'Enter your search term...',
emptyTable: ''
},
serverSide: true,
processing: false,
ajax: {
url: "ajax.php?action=getAds", // JSON datasource for companies
type: "post",  // method, by default get
cache: true,
sorting: true,
columnDefs: [
    { orderable: false, targets: 0 },
    { orderable: true, targets: "_all" }    // Disable sorting for all other columns
],
order: [[1, 'asc']],
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
        $("#adsTable_wrapper").hide();
        $("#noDataMessage").show();
    } else {
        $("#adsTable_wrapper").show();
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

$('#adsSearch').on('input', function() {
    var searchTerm = $(this).val();
    companyTable.search(searchTerm).draw();
});


// Delete Button 
function toggleDeleteButton() {
    const anyChecked = $(".table .chk-box:checked").length > 0;
    $("#adsDeleteButton").toggle(anyChecked); // Show if any checkbox is checked, hide otherwise
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
function deleteWalletHistory(selectedIds) {
    // Perform AJAX request
    $.ajax({
        url: "ajax.php?action=deleteMultiAds",
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
$("#adsDeleteButton").on("click", function () {
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
    
        deleteWalletHistory(selectedIds);
 });

//  $('#end_date').on('change', function () {
//     const val = $(this).val();
//     console.log("Changed value:", val); // should log "2025-06-28" style
//     if (inputVal.includes('/')) {
//         const [mm, dd, yyyy] = inputVal.split('/');
//         const formatted = `${yyyy}-${mm.padStart(2, '0')}-${dd.padStart(2, '0')}`;
//         $(this).val(formatted); // Now it will show correctly
//         console.log("Fixed date:", formatted);
//     }
// });

flatpickr("#end_date", {
    dateFormat: "m/d/Y", // MM/DD/YYYY
    allowInput: true
});
 // EditAd
$('.editAd').on('click', function () {
    // Create FormData object
        const formData = new FormData($('#myAdsForm')[0]);
    
        // images
        const __image = $('#imageShow')[0].files[0];
        if (__image) {
            console.log("File exists, appending to formData.");
            formData.append('images', __image);
        }

    
        // Send the AJAX request
        $.ajax({
            url: "ajax.php?action=update_ads",// The form's action attribute
            method: 'POST', // The form's method attribute
            data: formData, // Serialized form data
            processData: false,      // Prevent jQuery from serializing the FormData
            contentType: false, 
            beforeSend: function() {
                openScreenLoader('Updating process. Do not refresh this page...');
            },
            success: function (response) {
                window.location.href = 'ads.php';
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
                    console.error("Unexpected server response:", e);
                    alert("An unexpected error occurred. Please try again later.");
                }
            },
            complete: function(data) {
                // Close loader
                closeScreenLoader();
            }
        });
});