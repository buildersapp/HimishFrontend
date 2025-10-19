document.addEventListener('DOMContentLoaded', function () {
    // Add new phone number
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('add-phone')) {
            const branchIndex = event.target.getAttribute('data-branch-index');
            const formRow = document.querySelector(`#user-form-row-${branchIndex}`);
            const phoneInputs = formRow.querySelectorAll(`input[name^="company_branches[${branchIndex}][phone_numbers]"]`);
            const phoneCount = phoneInputs.length;

            const newPhoneDiv = document.createElement('div');
            newPhoneDiv.className = 'col-md-5';
            newPhoneDiv.innerHTML = `
                <div>
                    <label for="phone-${branchIndex}-${phoneCount}">Company Phone ${phoneCount + 1}:</label>
                    <input type="text" name="company_branches[${branchIndex}][phone_numbers][]" 
                        id="phone-${branchIndex}-${phoneCount}" 
                        placeholder="Phone Number" 
                        class="form-control">
                </div>
            `;
            formRow.insertBefore(newPhoneDiv, event.target.closest('.col-md-2'));
        }
    });
});
// add address
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('userForm');

    // Add address functionality
    form.addEventListener('click', (e) => {
        if (e.target.classList.contains('add-address')) {
            const branchIndex = e.target.dataset.branchIndex; // Get branch index
            const newBranchIndex = document.querySelectorAll('.user-form-row').length; // Total rows for new index
            
            const newSection = `
                <div class="row user-form-row" id="user-form-row-${newBranchIndex}" data-branch-index="${newBranchIndex}">
                    <!-- Location Name -->
                    <div class="col-md-5">
                        <div>
                            <label for="name-${newBranchIndex}">Location Name:</label>
                            <input type="text" name="company_branches[${newBranchIndex}][name]" 
                                id="name-${newBranchIndex}" 
                                placeholder="Location Name" 
                                class="form-control">
                        </div>
                    </div>
                    
                    <!-- Phone Numbers -->
                    <div class="col-md-5">
                        <div>
                            <label for="phone-${newBranchIndex}-0">Company Phone 1:</label>
                            <input type="text" name="company_branches[${newBranchIndex}][phone_numbers][]" 
                                id="phone-${newBranchIndex}-0" 
                                placeholder="Phone Number" 
                                class="form-control">
                        </div>
                    </div>
                    
                    <!-- Add Phone Number Button -->
                    <div class="col-md-2 align-self-end">
                        <div class="addMore">
                            <button type="button" class="btn p-0 add-phone" data-branch-index="${newBranchIndex}">+ Phone Number</button>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="col-md-10">
                        <div>
                            <label for="address-${newBranchIndex}">Address:</label>
                            <input type="text" name="company_branches[${newBranchIndex}][address]" 
                                id="address_${newBranchIndex}" 
                                placeholder="Address" 
                                class="form-control address-autocomplete">
                        </div>
                    </div>

                        <input type ="hidden" name="company_branches[${newBranchIndex}][state]">
                        <input type ="hidden" name="company_branches[${newBranchIndex}][city]">
                        <input type ="hidden" name="company_branches[${newBranchIndex}][zipcode]">
                        <input type ="hidden" name="company_branches[${newBranchIndex}][latitude]" >
                        <input type ="hidden" name="company_branches[${newBranchIndex}][longitude]">
                    
                    <!-- Remove Section Button -->
                    <div class="col-md-2 align-self-end">
                        <div class="addMore">
                            <button type="button" class="btn p-0 text-danger remove-section" data-branch-index="${newBranchIndex}">- Remove Address</button>
                        </div>
                    </div>


                    
                </div>
            
            `;

            // Insert the new section after the clicked Add Address button
            e.target.closest('.user-form-row').insertAdjacentHTML('afterend', newSection);
        }
        // Remove section functionality
        if (e.target.classList.contains('remove-section')) {
            const section = e.target.closest('.user-form-row');
            if (section) {
                section.remove();
            }
        }
    });
});


// company posts
$(document).on('click','#cpost',function(){
    var id = $(this).attr('rel');
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('.companyPosts')) {
        $(".companyPosts").DataTable().destroy(); // Destroy existing instance
    }
    var companyTable = $(".companyPosts").DataTable({
        dom: '<"top">rt<"bottom"lp><"clear">',
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
        url: "ajax.php?action=get_company_posts&company_id="+id, // JSON datasource for companies
        type: "post",  // method, by default get
        cache: false,
        "data": function(data) {
        
        },
        beforeSend: function() {
            openScreenLoader('Fetching Posts. Do not refresh this page...');
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
                $("#companyPosts_wrapper").hide();
                $("#noDataMessage").show();
            } else {
                $("#companyPosts_wrapper").show();
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
        

})
// company showcase
$(document).on('click','#cshow',function(){
    var id = $(this).attr('rel');
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('.companyShowCase')) {
        $(".companyShowCase").DataTable().destroy(); // Destroy existing instance
    }
    var companyTable = $(".companyShowCase").DataTable({
        dom: '<"top">rt<"bottom"lp><"clear">',
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
        url: "ajax.php?action=company_showcase&company_id="+id, // JSON datasource for companies
        type: "post",  // method, by default get
        cache: false,
        "data": function(data) {
        
        },
        beforeSend: function() {
            openScreenLoader('Fetching Showcase. Do not refresh this page...');
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
                $("#companyShowCase_wrapper").hide();
                $("#noDataMessageShowCase").show();
            } else {
                $("#companyShowCase_wrapper").show();
                $("#noDataMessageShowCase").hide();
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
        

})
// company recommends
$(document).on('click','#cRec',function(){
    var id = $(this).attr('rel');
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#cRecommends')) {
        $("#cRecommends").DataTable().destroy(); // Destroy existing instance
    }
    var companyTable = $("#cRecommends").DataTable({
        dom: '<"top">rt<"bottom"lp><"clear">',
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
        url: "ajax.php?action=company_recommends&company_id="+id, // JSON datasource for companies
        type: "post",  // method, by default get
        cache: false,
        "data": function(data) {
        
        },
        beforeSend: function() {
            openScreenLoader('Fetching Recommends. Do not refresh this page...');
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
                $("#cRecommends_wrapper").hide();
                $("#noDataMessageRec").show();
            } else {
                $("#cRecommends_wrapper").show();
                $("#noDataMessageRec").hide();
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


})
// EditCompany
$('.companyFormSb').on('click', function () {
    // Create FormData object
        const formData = new FormData($('#myCompanyForm')[0]);
        // logo
        const logo = $('#changeLogoUpload')[0].files[0];
        if (logo) {
            console.log("File exists, appending to formData.");
            formData.append('logo', logo);
        }
        // banner
        const banner = $('#imgUpload')[0].files[0];
        if (banner) {
            console.log("File exists, appending to formData.");
            formData.append('company_cover_images', banner);
        }
        // Append listing file
        const company_listing_images = $('#listImg')[0].files[0];
        if (company_listing_images) {
            console.log("File exists, appending to formData.");
            formData.append('company_listing_images', company_listing_images);
        }
    
        // Send the AJAX request
        $.ajax({
            url: "ajax.php?action=update_company",// The form's action attribute
            method: 'POST', // The form's method attribute
            data: formData, // Serialized form data
            processData: false,      // Prevent jQuery from serializing the FormData
            contentType: false, 
            beforeSend: function() {
                openScreenLoader('Updating process. Do not refresh this page...');
            },
            success: function (response) {
                window.location.href = 'companies.php';
            },
            error: function(jqXHR) {
                try {
                    console.log('inner',jqXHR)
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
// EditCompanyLocation
$('.company_branchesubmit').on('click', function () {
    // Create FormData object
        const formData = new FormData($('#userForm')[0]);
        // Send the AJAX request
        $.ajax({
            url: "ajax.php?action=update_company",// The form's action attribute
            method: 'POST', // The form's method attribute
            data: formData, // Serialized form data
            processData: false,      // Prevent jQuery from serializing the FormData
            contentType: false, 
            beforeSend: function() {
                openScreenLoader('Updating process. Do not refresh this page...');
            },
            success: function (response) {
                window.location.href = 'companies.php';
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
// delete company branch
$('.delete-branch').on('click', function () {
    // Create FormData object
    // alert('hiiii')
    var __this = $(this);
        const _id = $(this).attr('id');
        // Send the AJAX request
        $.ajax({
            url: "ajax.php?action=deleteCompanyBranch",// The form's action attribute
            method: 'POST', // The form's method attribute
            data: {id: _id},
            beforeSend: function() {
                openScreenLoader('Deleting process. Do not refresh this page...');
            },
            success: function (response) {
                window.location.reload();
            },
            error: function(jqXHR) {
                try {
                     closeScreenLoader();
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
                     closeScreenLoader();
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