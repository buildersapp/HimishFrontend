var companyTable = $("#companyTableN").DataTable({
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
sorting: true,
columnDefs: [
    { orderable: false, targets: 0 },
    { orderable: true, targets: "_all" }    // Disable sorting for all other columns
],
order: [[1, 'asc']],    
ajax: {
url: "ajax.php?action=get_companies", // JSON datasource for companies
type: "post",  // method, by default get
cache: true,
"data": function(data) {
    data.user_id = $('#userSelect').val();
    data.status = $('#selectStatus').val();
    data.address = $('#address').val();
},
beforeSend: function() {
    openScreenLoader('Fetching Companies. Do not refresh this page...');
},
// error: function(data) {  // error handling
  
//     //$("#table-grid").append('<tbody class="table-grid-error"><tr><th colspan="6">No data found!</th></tr></tbody>');
//     //$("#table-grid_processing").css("display","none");
// },
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
        $("#companyTable_wrapper").hide();
        $("#noDataMessage").show();
    } else {
        $("#companyTable_wrapper").show();
        $("#noDataMessage").hide();
    }

    // Handle the increase share count
    $(".increaseShareBtn").on("click", function () {
        var companyId = $(this).data('company-id');
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
            data: { increaseAmount: increaseAmount, postId: 0, companyId : companyId},
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

$('#searchCompany').on('input', function() {
var searchTerm = $(this).val();
companyTable.search(searchTerm).draw();
});
$('#filterS').on('click', function() {
    // Pass the search term to the DataTable and redraw it
    companyTable.draw();
});

// changeCompanyStatus
function changeCompanyStatus(element){

    var userId = element.getAttribute("user-id");
    var status = element.getAttribute("status");

    var data = {
        company_id: userId,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeCompanyStatus',
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

// Delete Button 
function toggleDeleteButton() {
    const anyChecked = $(".table .chk-box:checked").length > 0;
    $("#compnyDltButton").toggle(anyChecked); // Show if any checkbox is checked, hide otherwise
    $("#mergeCompany").toggle(anyChecked)
    
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

$('#category').on('change', function () {
    // Get the selected option
    const selectedOption = $(this).find(':selected'); // Get the currently selected option

    // Get the data-tab_label_option_posts attribute
    var tabLabelOptionPosts = selectedOption.data('tab_label_option_posts'); // Access the data-* attribute
    var __val = $('#selectedKeywords').text();
    if (__val) {
        // Split __val into an array by commas and trim spaces
        let newValues = __val.split(',').map(value => value.trim());
    
        // Iterate over each value in newValues
        newValues.forEach(value => {
            // Check if the value is not already in tabLabelOptionPosts (after trimming spaces)
            if (!tabLabelOptionPosts.split(',').map(item => item.trim()).includes(value)) {
                // If not, concatenate it with a comma
                tabLabelOptionPosts += ',' + value;
            }
        });
    }
    
    

    // Set the service field value
    $('#service').val(tabLabelOptionPosts || ''); // Update the input field or set it to an empty string if null
});

// delete post
function deleteCompany(selectedIds) {
    // Perform AJAX request
    $.ajax({
        url: "ajax.php?action=delete_company",
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
$("#compnyDltButton").on("click", function () {
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

    deleteCompany(selectedIds);
});

// Handle the merge button click
$("#mergeCompany").on("click", function () {
    // Get all checked checkboxes
    const selectedIds = $(".table .chk-box:checked")
         .map(function () {
             return $(this).val();
         })
         .get();
    const companyName = $(".table .chk-box:checked")
         .map(function () {
             return $(this).attr('rel')
         })
         .get();
        console.log(companyName,"==========companyName")
 
    if (selectedIds.length === 0) {
         alert("No rows selected!");
         return;
    }
    if (selectedIds.length ==1) {
         alert("Please select minimum 2 company");
         return;
    }

    $.ajax({
        method:'POST',
        url: 'ajax.php?action=getCompanyBYId',
        data: {ids:selectedIds},
        success:function(data){
            // console.log(data, "Raw response from server");
            var __data;
            try {
                __data = JSON.parse(data);
                // console.log(__data, "Parsed JSON data");
            } catch (e) {
                console.error("Failed to parse JSON:", e);
                return;
            }
             // Proceed only if `__data.body` exists and is an object or array
            if (!__data.body || !Array.isArray(__data.body)) {
                console.error("Invalid response structure. Expected an array in `body`.");
                return;
            }
            // console.log(__data,"==========__data")
            var __html ='<label for="email">Select Master Company:</label><select name="master_id" class="form-select" required><option value="">Please Select Master Company</option>'

            for (var i in __data.body) {
                const branch = __data.body[i].company_branches[0]; // Access the first branch
                __html += `<option value="${__data.body[i].id}">
                            ${__data.body[i].name}
                            ${branch?.address ? `  ----------------> ${branch.address}` : ''}
                            ${branch?.phone_numbers ? ` ----------------> ${branch.phone_numbers}` : ''} 
                           </option>`;
            }
            
            __html += '</select>'
           // console.log(__html,"=====html")
            $("#apendD").html(__html);
            $("#cc_id").val(selectedIds.join(","));
            $("#mergeCompanyModal").modal('show');
        }
    })

 });
