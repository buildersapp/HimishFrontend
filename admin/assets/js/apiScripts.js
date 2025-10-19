
const tableConfig = {
    users: {
        deleteFunction: deleteUser,
        deleteTitle: "Deleting User",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">user</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    profileGroups: {
        deleteFunction: deleteProfileGroup,
        deleteTitle: "Deleting Profile Group",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">group</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    countries: {
        deleteFunction: deleteCountry,
        deleteTitle: "Deleting Country",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">country</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    community_members: {
        deleteFunction: deleteCommunityMember,
        deleteTitle: "Deleting Member From Community",
        deleteDescription: `<p><b>Do you really want to remove this <span class="text-danger">member</span> from community ?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    faqs: {
        deleteFunction: deleteFaq,
        deleteTitle: "Deleting Faq",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">faq</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    comment_sugessions: {
        deleteFunction: deleteCommentSugessions,
        deleteTitle: "Deleting Comment",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">comment</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    communities: {
        deleteFunction: deleteCommunity,
        deleteTitle: "Deleting Community",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">community</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    referral_links: {
        deleteFunction: deleteLink,
        deleteTitle: "Deleting Referral Link",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">Link</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    companies: {
        deleteFunction: deleteCompany,
        deleteTitle: "Deleting Company",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">company</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    company_recommends: {
        deleteFunction: deleteCompanyRecommends,
        deleteTitle: "Deleting Company Recommend",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">recommend</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    company_members: {
        deleteFunction: deleteCompanyMember,
        deleteTitle: "Deleting Company Member",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">member</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    sponser_ads: {
        deleteFunction: deleteAD,
        deleteTitle: "Deleting Ad",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">AD</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    anonymous_user_report_posts: {
        deleteFunction: deleteAnanomousRequest,
        deleteTitle: "Deleting Request",
        deleteDescription: `<p><b>Do you really want to delete this <span class="text-danger">Request</span>?</b></p><small>Once you click delete, the action can’t be undone.</small>`
    },
    posts: {
        deleteFunction: deleteSinglePost,
        deleteTitle: "Deleting Post",
        deleteDescription: `
            <p><b>Do you really want to delete this <span class="text-danger">post</span>?</b></p>
            <small>Once you click delete, the action can’t be undone.</small>`
    }
};


/*******
 * 
 * **************************************************************************
 * **************************************************************************
 * **************************************************************************
 * **************************************************************************
 * 
 * 
 *******/


$(document).ready(function () {

    // loginUser
    $('#loginUser').on('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        var email = $('#email').val();
        var password = $('#password').val();
        var timezone = $('#timezone').val();

        var data = {
            email: email,
            password: password,
            timezone: timezone,
            device_token: 'web',
            device_type: 3,
            device_id: 'dftgdddcfgc',
            device_name: 'web',
        };

        $.ajax({
            url: 'ajax.php?action=loginUser',
            type: 'POST',
            data: data,
            beforeSend: function () {
                $('#loginButton').prop('disabled', true).text('Logging in...');
                openScreenLoader('Logging in. Do not refresh this page...');
            },
            success: function (response) {
                var jsonResponse = JSON.parse(response);
                var userRole = jsonResponse.body.account_type;
                var redirectUrl = 'dashboard.php';
                if(userRole === 3){
                    redirectUrl = 'sp-dashboard.php';
                }
                handleResponse(response, redirectUrl);
            },
            error: function (xhr, status, error) {
                console.error("Error: " + error);
                closeScreenLoader();
            },
            complete: function () {
                $('#loginButton').prop('disabled', false).text('Login');
                closeScreenLoader();
            }
        });
    });

    // logoutUser
    $('#logoutUser').on('click', function (e) {
        e.preventDefault(); // Prevent the default form submission

        $.ajax({
            url: 'ajax.php?action=logoutUser',
            type: 'PUT',
            data: {},
            beforeSend: function () {
                openScreenLoader('Logging out. Do not refresh this page...');
            },
            success: function (response) {
                handleResponse(response, 'index.php');
            },
            error: function (xhr, status, error) {
                console.error("Error: " + error);
                closeScreenLoader();
            },
            complete: function () {
                closeScreenLoader();
            }
        });
    });

    // load Users
    var userTable = $("#userTable").DataTable({
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
        processing : false,
        debug: true, // Enable debugging
        sorting: true,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_users", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.status = $('#statusSelect').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Users. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#userTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#userTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(userTable.search()){
                    var regex = new RegExp('('+userTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    // load Profile Groups
    var profileGroupsTable = $("#profileGroupsTable").DataTable({
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
        processing : false,
        debug: true, // Enable debugging
        sorting: true,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_profile_groups", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.status = $('#statusSelect').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Profile Groups. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#profileGroupsTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#profileGroupsTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(profileGroupsTable.search()){
                    var regex = new RegExp('('+profileGroupsTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    // load Sub Admins
    var subAdminTable = $("#subAdminTable").DataTable({
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
        processing : false,
        debug: true, // Enable debugging
        sorting: true,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_sub_admins", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.status = $('#statusSelect').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Sub Admins. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#subAdminTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#subAdminTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(subAdminTable.search()){
                    var regex = new RegExp('('+subAdminTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    // load Countries
    var countryTable = $("#countryTable").DataTable({
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
        processing : false,
        debug: true, // Enable debugging
        sorting: true,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_countries", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.status = $('#statusSelect').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Countries. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#countryTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#countryTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(countryTable.search()){
                    var regex = new RegExp('('+countryTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    // load Sales Persons
    var salesPersonTable = $("#salesPersonTable").DataTable({
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
        processing : false,
        debug: true, // Enable debugging
        sorting: true,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_sales_person", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.status = $('#statusSalesSelect').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Sales Person. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#salesPersonTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#salesPersonTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(salesPersonTable.search()){
                    var regex = new RegExp('('+salesPersonTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    // load sales person community
    var salesPersonCommunityTable = $("#salesPersonCommunityTable").DataTable({
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
        processing : false,
        debug: true, // Enable debugging
        sorting: true,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_sales_communities", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.status = $('#salesCommunityStatus').val();
                data.is_private = $('#pubPri').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Sales Person Community. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#salesPersonCommunityTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#salesPersonCommunityTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(salesPersonCommunityTable.search()){
                    var regex = new RegExp('('+salesPersonCommunityTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });
    // load sales person community
    var salesPersonLinkTable = $("#getReferralLink").DataTable({
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
        processing : false,
        debug: true, // Enable debugging
        sorting: true,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_referral_inks", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.status = $('#salesLinkStatus').val();
                data.user_id = $('#u_id').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Sales Person Link. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#salesPersonLinkTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#salesPersonLinkTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(salesPersonLinkTable.search()){
                    var regex = new RegExp('('+salesPersonLinkTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    // load Ocr Data
    var ocrTable = $("#ocrTable").DataTable({
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
        processing : false,
        debug: true, // Enable debugging
        sorting: true,
        columnDefs: [
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_ocr_data", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            beforeSend: function () {
                openScreenLoader('Fetching OCR Data. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#ocrTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#ocrTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // Initialize the popover with custom settings
                $('[data-toggle="modal"]').on('click', function () {
                    var jsonData = $(this).attr('data-json');
                    var imageData = $(this).attr('data-image');

                    if (imageData) {
                        var imgContent = `<img src="${imageData}" alt="Image" class="img-fluid" onerror="this.onerror=null;this.src=\'assets/img/fav-icon.png\';" />`;
                        $('#imageContent').html(imgContent);
                    } else {
                        $('#imageContent').html('<img class="img-placeholder" src="assets/img/fav-icon.png" alt="No Image" />');
                    }

                    // Parse the JSON data and apply jsonView
                    $('#jsonDataContent').html(jsonData);
                    $('#jsonModal').modal('show');
                });

                $('#closeJsonModal').on('click', function () {
                    $('#jsonModal').modal('hide');
                });

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(ocrTable.search()){
                    var regex = new RegExp('('+ocrTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchUser').on('input', function() {
        var searchTerm = $(this).val();
        userTable.search(searchTerm).draw();
    });

    $('#searchAdmin').on('input', function() {
        var searchTerm = $(this).val();
        subAdminTable.search(searchTerm).draw();
    });

    $('#searchPG').on('input', function() {
        var searchTerm = $(this).val();
        profileGroupsTable.search(searchTerm).draw();
    });

    $('#searchSalesPerson').on('input', function() {
        var searchTerm = $(this).val();
        salesPersonTable.search(searchTerm).draw();
    });

    $('#searchCountry').on('input', function() {
        var searchTerm = $(this).val();
        countryTable.search(searchTerm).draw();
    });

    $('#searchSalesPersonComunity').on('input', function() {
        var searchTerm = $(this).val();
        salesPersonCommunityTable.search(searchTerm).draw();
    });
    $('#searchSalesPersonLink').on('input', function() {
        var searchTerm = $(this).val();
        salesPersonLinkTable.search(searchTerm).draw();
    });


    $('#statusSelect').on('input', function() {
        userTable.draw();
    });
    $('#statusSalesSelect').on('input', function() {
        salesPersonTable.draw();
    });

    $('#salesCommunityStatus').on('input', function() {
        salesPersonCommunityTable.draw();
    });
    $('#pubPri').on('input', function() {
        salesPersonCommunityTable.draw();
    });
    $('#link_st').on('input', function() {
        salesPersonLinkTable.draw();
    });
    $('#u_id').on('input', function() {
        salesPersonLinkTable.draw();
    });

    // load Looking For
    var webAdTable = $("#webAdTable").DataTable({
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
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_web_feed_ads", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.position = $('#positionFil').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Looking For. Do not refresh this page...');
            },
            error: function(jqXHR) {
                try {
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
            complete : function(data){

                $('.user-dropdown').select2();

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#webAdTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#webAdTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // Handle the increase share count
                $(".increaseShareBtn").on("click", function () {
                    var ad_id = $(this).data('post-id');
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
                        url: "ajax.php?action=increase_share_count_web_Ad",
                        type: "POST",
                        data: { increaseAmount: increaseAmount, ad_id: ad_id },
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

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(webAdTable.search()){
                    var regex = new RegExp('('+webAdTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchWebAd').on('input', function() {
        var searchTerm = $(this).val();
        webAdTable.search(searchTerm).draw();
    });

    $('#positionFil').on('change', function() {
        webAdTable.draw();
    });

    // load Looking For
    var lookingForTable = $("#lookingForTable").DataTable({
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
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_looking_for", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.listingType = $('#listingType').val();
            },
            beforeSend: function () {
                openScreenLoader('Fetching Looking For. Do not refresh this page...');
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
            complete : function(data){

                $('.user-dropdown').select2();

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#lookingForTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#lookingForTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

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

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(lookingForTable.search()){
                    var regex = new RegExp('('+lookingForTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchLookingFor').on('input', function() {
        var searchTerm = $(this).val();
        lookingForTable.search(searchTerm).draw();
    });

    // dealsTable
    var dealsTable = $("#dealsTable").DataTable({
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
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_deals", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {

            },
            beforeSend: function () {
                openScreenLoader('Fetching Deals. Do not refresh this page...');
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
            complete : function(data){

                $('.user-dropdown').select2();

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#dealsTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#dealsTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
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

                // Highlight search results
                if(dealsTable.search()){
                    var regex = new RegExp('('+dealsTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchDeals').on('input', function() {
        var searchTerm = $(this).val();
        dealsTable.search(searchTerm).draw();
    });

    // dealShareTable
    var dealShareTable = $("#dealShareTable").DataTable({
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
        order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
        ajax:{
            url :"ajax.php?action=get_deal_share", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {

            },
            beforeSend: function () {
                openScreenLoader('Fetching Deal Share. Do not refresh this page...');
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
            complete : function(data){

                $('.user-dropdown').select2();

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#dealShareTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#dealShareTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

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

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(dealShareTable.search()){
                    var regex = new RegExp('('+dealShareTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchDealShare').on('input', function() {
        var searchTerm = $(this).val();
        dealShareTable.search(searchTerm).draw();
    });

    // communitiesTable
    var communitiesTable = $("#communitiesTable").DataTable({
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
        processing : true,
        sorting: true,
        columnDefs: [
            { orderable: true, targets: "_all" }    // Disable sorting for all other columns
        ],
        order: [[1, 'asc']],
        ajax:{
            url :"ajax.php?action=get_communities", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {

            },
            beforeSend: function () {
                openScreenLoader('Fetching Communities. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#communitiesTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#communitiesTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(communitiesTable.search()){
                    var regex = new RegExp('('+communitiesTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchCommunities').on('input', function() {
        var searchTerm = $(this).val();
        communitiesTable.search(searchTerm).draw();
    });

    // masterTable
    var masterTable = $("#masterTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_master_sub_cat", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                data.type = $("#dataTypeSelector").val()
            },
            beforeSend: function () {
                openScreenLoader('Fetching Master Categories. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#masterTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#masterTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(masterTable.search()){
                    var regex = new RegExp('('+masterTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchMaster').on('input', function() {
        var searchTerm = $(this).val();
        masterTable.search(searchTerm).draw();
    });

    $("#dataTypeSelector").on("change", function () {
        masterTable.draw();
    });

    // faqsTable
    var faqsTable = $("#faqsTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_faqs", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                //
            },
            beforeSend: function () {
                openScreenLoader('Fetching Faqs. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#faqsTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#faqsTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(faqsTable.search()){
                    var regex = new RegExp('('+faqsTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchFaqs').on('input', function() {
        var searchTerm = $(this).val();
        faqsTable.search(searchTerm).draw();
    });

    // commentTable
    var commentTable = $("#commentTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_comments", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                //
            },
            beforeSend: function () {
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#commentTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#commentTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(commentTable.search()){
                    var regex = new RegExp('('+commentTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchComment').on('input', function() {
        var searchTerm = $(this).val();
        commentTable.search(searchTerm).draw();
    });

    // plansTable
    var plansTable = $("#plansTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_plans", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                //
            },
            beforeSend: function () {
                openScreenLoader('Fetching Plans. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#plansTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#plansTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(plansTable.search()){
                    var regex = new RegExp('('+plansTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    // claimedBusinessTable
    var claimedBusinessTable = $("#claimedBusinessTable").DataTable({
        dom: '<"top">rt<"bottom"lp><"clear">',
        pageLength: 500,
        order: [[0, 'desc']],
        lengthMenu: [ [10, 25, 50, 100, 500], [10, 25, 50, 100, 500] ],
        drawCallback: function() {
            $('html, body').animate({ scrollTop: 0 }, 'fast');
        },
        language: {
            searchPlaceholder: 'Enter your search term...',
            emptyTable: ''
        },
        serverSide: true,
        processing : true,
        ajax:{
            url :"ajax.php?action=get_claimed_business_requests", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                const urlParams = new URLSearchParams(window.location.search);
                const searchParam = urlParams.get('search');

                if (searchParam) {
                    data.extra_search = searchParam; // This gets sent to the server
                }
            },
            beforeSend: function () {
                openScreenLoader('Fetching Business Requests. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#claimedBusinessTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#claimedBusinessTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(claimedBusinessTable.search()){
                    var regex = new RegExp('('+claimedBusinessTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchClaimedBusiness').on('input', function() {
        var searchTerm = $(this).val();
        claimedBusinessTable.search(searchTerm).draw();
    });

    // adsRequestsTable
    var adsRequestsTable = $("#adsRequestsTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_ads_requests", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                //
            },
            beforeSend: function () {
                openScreenLoader('Fetching Ads Requests. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#adsRequestsTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#adsRequestsTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(adsRequestsTable.search()){
                    var regex = new RegExp('('+adsRequestsTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchAds').on('input', function() {
        var searchTerm = $(this).val();
        adsRequestsTable.search(searchTerm).draw();
    });

    // disputeRequestsTable
    var disputeRequestsTable = $("#disputeRequestsTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_dispute_requests", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                //
            },
            beforeSend: function () {
                openScreenLoader('Fetching Dispute Requests. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#disputeRequestsTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#disputeRequestsTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(disputeRequestsTable.search()){
                    var regex = new RegExp('('+disputeRequestsTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchDAds').on('input', function() {
        var searchTerm = $(this).val();
        disputeRequestsTable.search(searchTerm).draw();
    });

    // communityRequestsTable
    var communityRequestsTable = $("#communityRequestsTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_community_requests", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                //
            },
            beforeSend: function () {
                openScreenLoader('Fetching Community Requests. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#communityRequestsTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#communityRequestsTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(communityRequestsTable.search()){
                    var regex = new RegExp('('+communityRequestsTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });

    $('#searchCR').on('input', function() {
        var searchTerm = $(this).val();
        communityRequestsTable.search(searchTerm).draw();
    });

    // emailModuleTable
    var emailModuleTable = $("#emailModuleTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_email_templates", // json datasource
            type: "post",  // method  , by default get
            cache:true,
            "data":function(data) {
                //
            },
            beforeSend: function () {
                openScreenLoader('Fetching Email Templates. Do not refresh this page...');
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#emailModuleTable_wrapper").hide();
                    $("#noDataMessage").show();
                } else {
                    $("#emailModuleTable_wrapper").show();
                    $("#noDataMessage").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(emailModuleTable.search()){
                    var regex = new RegExp('('+emailModuleTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });
});

/***
 * 
 * notification module email templates
 * 
 */
$(document).on('click','.notiTabs',function(){
    var Id = $(this).attr("data-uid");
    $('.notiTabs').removeClass('btn-primary').addClass('btn-default');
    $(this).addClass('btn-primary').removeClass('btn-default');
    loadNotiTable(Id);
});

// edit notification module
$(document).on('click', '.editNM', function () {
    // Set values
    $("#Id").val($(this).attr("data-id"));
    $("#code").val($(this).attr("data-code"));
    $("#title").val($(this).attr("data-title"));
    $("#name").val($(this).attr("data-name"));
    $("#after_expire_time").val($(this).attr("data-after_expire_time"));
    $("#email_text").val($(this).attr("data-email_text"));
    $("#notification_message").val($(this).attr("data-notification_message"));
    $("#push_message").val($(this).attr("data-push_message"));

    $("#checkbox_notification_message").val($(this).attr("data-checkbox_notification_message"));
    $("#checkbox_push_message").val($(this).attr("data-checkbox_push_message"));
    $("#checkbox_email_text").val($(this).attr("data-checkbox_email_text"));

    // Checkbox states
    $('#checkbox_email_text').prop("checked", $('#checkbox_email_text').val() === '1');
    $('#checkbox_push_message').prop("checked", $('#checkbox_push_message').val() === '1');
    $("#checkbox_notification_message").prop("checked", $('#checkbox_notification_message').val() === '1');

    // Disable/Enable textareas
    $('#email_text').prop("disabled", $('#checkbox_email_text').val() !== '1');
    $('#push_message').prop("disabled", $('#checkbox_push_message').val() !== '1');
    $("#notification_message").prop("disabled", $('#checkbox_notification_message').val() !== '1');

    // Handle CKEditor 5
    if ($(this).attr("data-email_text") && $('#checkbox_email_text').val() === '1') {
        if (emailEditorInstance) {
            emailEditorInstance.destroy().then(() => {
                initCKEditor();
            });
        } else {
            initCKEditor();
        }
    } else {
        if (emailEditorInstance) {
            emailEditorInstance.destroy().then(() => {
                emailEditorInstance = null;
            });
        }
    }

    $("#editNModal").modal("show");
});

function initCKEditor() {
    ClassicEditor
        .create(document.querySelector('#email_text'), {
            toolbar: [
                'bold', 'italic', 'underline', 'strikethrough',
                '|', 'numberedList', 'bulletedList', 'outdent', 'indent',
                '|', 'fontSize', 'fontColor', 'fontBackgroundColor',
                '|', 'link', 'undo', 'redo'
            ]
        })
        .then(editor => {
            emailEditorInstance = editor;
        })
        .catch(error => {
            console.error('CKEditor 5 initialization error:', error);
        });
}

// edit email module
$(document).on('click','.editEM',function(){
    $("#Id").val($(this).attr("data-id"));
    $("#code").val($(this).attr("data-code"));
    $("#title").val($(this).attr("data-title"));
    $("#subject").val($(this).attr("data-subject"));
    $("#template").val($(this).attr("data-template"));
    CKEDITOR.replace('template', {
        height: 200,
        // Optional: Configure CKEditor settings here
        toolbar: [
            { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
            { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent'] },
            { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
            { name: 'colors', items: ['TextColor', 'BGColor'] },
            { name: 'insert', items: ['Link', 'Unlink'] },
        ],
        removeButtons: 'Subscript,Superscript',
        // You can add more configurations as needed
    });
    $("#editEModal").modal("show");
});

function loadNotiTable(Id) {
    if ($('#notiModuleTable').length) {
        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#notiModuleTable')) {
            $("#notiModuleTable").DataTable().destroy(); // Destroy existing instance
        }
        var notiModuleTable = $("#notiModuleTable").DataTable({
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
            processing : true,
            columns: [
                { className: 'nf-col-1' },
                { className: 'nf-col-2' },
                { className: 'nf-col-3' },
                { className: 'nf-col-4' },
                { className: 'nf-col-5' },
                { className: 'nf-col-6' },
                { className: 'nf-col-7' },
                { className: 'nf-col-8' },

            ],
            ajax:{
                url :"ajax.php?action=get_noti_templates", // json datasource
                type: "post",  // method  , by default get
                cache:true,
                data: {},
                beforeSend: function () {
                    openScreenLoader('Fetching Templates. Do not refresh this page...');
                },
                error: function(jqXHR) {
                    try {
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
                complete : function(data){

                    // show no data
                    if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                        $("#notiModuleTable_wrapper").hide();
                        $("#noDataMessageUC").show();
                    } else {
                        $("#notiModuleTable_wrapper").show();
                        $("#noDataMessageUC").hide();
                    }

                    // tooltip
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });

                    // Highlight search results
                    if(notiModuleTable.search()){
                        var regex = new RegExp('('+notiModuleTable.search()+')', 'gi');
                        $('.content').each(function() {
                            var content = $(this);
                            var text = content.text();
                            if (text.match(regex)) {
                                content.parent().addClass('highlight');
                            }
                        });
                    }

                    // close loader
                    closeScreenLoader();
                }
            },
            rowCallback: function(row, data, index) {
                if (index % 2 === 0) {
                    $(row).addClass('first-row'); // Even row
                } else {
                    $(row).addClass('second-row'); // Odd row
                }
            }
        });
        $('#searchNotification').on('input', function() {
            var searchTerm = $(this).val();
            notiModuleTable.search(searchTerm).draw();
        });

    }
}



// user details
$(document).on('click','#userDetailsTab',function(){

    // show / hide Btns
    $("#walletBtn").hide();
    $("#walletBtn").removeClass("d-flex");

    $("#increaseViewsBtn").hide();
    $("#increaseViewsBtn").removeClass("d-flex");

    $("#editUserBtn").show();
    $("#editUserBtn").addClass("d-flex");

});

// user companies
$(document).on('click','#userCompaniesTab',function(){

    // show / hide Btns
    $("#walletBtn").hide();
    $("#walletBtn").removeClass("d-flex");

    $("#increaseViewsBtn").hide();
    $("#increaseViewsBtn").removeClass("d-flex");

    $("#editUserBtn").hide();
    $("#editUserBtn").removeClass("d-flex");

    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#userCompaniesTable')) {
        $("#userCompaniesTable").DataTable().destroy(); // Destroy existing instance
    }
    if ($('#userCompaniesTable').length) {
        var userId = $('#userCompaniesTable').attr('data-user-id');
        var userCompaniesTable = $("#userCompaniesTable").DataTable({
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
            processing : true,
            ajax:{
                url :"ajax.php?action=get_user_companies&user_id="+userId, // json datasource
                type: "post",  // method  , by default get
                cache:true,
                data: {userId: userId},
                beforeSend: function () {
                    openScreenLoader('Fetching User Companies. Do not refresh this page...');
                },
                error: function(jqXHR) {
                    try {
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
                complete : function(data){

                    // show no data
                    if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                        $("#userCompaniesTable_wrapper").hide();
                        $("#noDataMessageUC").show();
                    } else {
                        $("#userCompaniesTable_wrapper").show();
                        $("#noDataMessageUC").hide();
                    }

                    // tooltip
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });

                    // Highlight search results
                    if(userCompaniesTable.search()){
                        var regex = new RegExp('('+userCompaniesTable.search()+')', 'gi');
                        $('.content').each(function() {
                            var content = $(this);
                            var text = content.text();
                            if (text.match(regex)) {
                                content.parent().addClass('highlight');
                            }
                        });
                    }

                    // close loader
                    closeScreenLoader();
                }
            },
            rowCallback: function(row, data, index) {
                if (index % 2 === 0) {
                    $(row).addClass('first-row'); // Even row
                } else {
                    $(row).addClass('second-row'); // Odd row
                }
            }
        });
    }
});

// user posts
$(document).on('click','#userPostsTab',function(){

    // show / hide Btns
    $("#walletBtn").hide();
    $("#walletBtn").removeClass("d-flex");

    $("#increaseViewsBtn").show();
    $("#increaseViewsBtn").addClass("d-flex");

    $("#editUserBtn").hide();
    $("#editUserBtn").removeClass("d-flex");

    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#userPostsTable')) {
        $("#userPostsTable").DataTable().destroy(); // Destroy existing instance
    }
    if ($('#userPostsTable').length) {
        var userId = $('#userPostsTable').attr('data-user-id');
        var userWalletTxnTable = $("#userPostsTable").DataTable({
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
            processing : true,
            ajax:{
                url :"ajax.php?action=get_user_posts&user_id="+userId, // json datasource
                type: "post",  // method  , by default get
                cache:true,
                data: {userId: userId},
                beforeSend: function () {
                    openScreenLoader('Fetching User Posts. Do not refresh this page...');
                },
                error: function(jqXHR) {
                    try {
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
                complete : function(data){

                    // show no data
                    if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                        $("#userPostsTable_wrapper").hide();
                        $("#noDataMessageUP").show();
                    } else {
                        $("#userPostsTable_wrapper").show();
                        $("#noDataMessageUP").hide();
                    }

                    // tooltip
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });

                    // Highlight search results
                    if(userWalletTxnTable.search()){
                        var regex = new RegExp('('+userWalletTxnTable.search()+')', 'gi');
                        $('.content').each(function() {
                            var content = $(this);
                            var text = content.text();
                            if (text.match(regex)) {
                                content.parent().addClass('highlight');
                            }
                        });
                    }

                    // close loader
                    closeScreenLoader();
                }
            },
            rowCallback: function(row, data, index) {
                if (index % 2 === 0) {
                    $(row).addClass('first-row'); // Even row
                } else {
                    $(row).addClass('second-row'); // Odd row
                }
            }
        });
    }

});

// user wallet Txns
$(document).on('click','#userWalletTab',function(){

    // show / hide Btns
    $("#walletBtn").show();
    $("#walletBtn").addClass("d-flex");

    $("#increaseViewsBtn").hide();
    $("#increaseViewsBtn").removeClass("d-flex");

    $("#editUserBtn").hide();
    $("#editUserBtn").removeClass("d-flex");

    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#userWalletTxnTable')) {
        $("#userWalletTxnTable").DataTable().destroy(); // Destroy existing instance
    }
    if ($('#userWalletTxnTable').length) {
        var userId = $('#userWalletTxnTable').attr('data-user-id');
        var userWalletTxnTable = $("#userWalletTxnTable").DataTable({
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
            processing : true,
            ajax:{
                url :"ajax.php?action=get_user_wallet_txns&user_id="+userId, // json datasource
                type: "post",  // method  , by default get
                cache:true,
                data: {userId: userId},
                beforeSend: function () {
                    openScreenLoader('Fetching User Wallet. Do not refresh this page...');
                },
                error: function(jqXHR) {
                    try {
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
                complete : function(data){

                    // show no data
                    if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                        $("#userWalletTxnTable_wrapper").hide();
                        $("#noDataMessageUW").show();
                    } else {
                        $("#userWalletTxnTable_wrapper").show();
                        $("#noDataMessageUW").hide();
                    }

                    // tooltip
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    });

                    // Highlight search results
                    if(userWalletTxnTable.search()){
                        var regex = new RegExp('('+userWalletTxnTable.search()+')', 'gi');
                        $('.content').each(function() {
                            var content = $(this);
                            var text = content.text();
                            if (text.match(regex)) {
                                content.parent().addClass('highlight');
                            }
                        });
                    }

                    // close loader
                    closeScreenLoader();
                }
            },
            rowCallback: function(row, data, index) {
                if (index % 2 === 0) {
                    $(row).addClass('first-row'); // Even row
                } else {
                    $(row).addClass('second-row'); // Odd row
                }
            }
        });
    }
});

// edit user - show update button
$(document).on('click','#editUserBtn',function(){
    $("#editUserBtn").remove();
    $("#updateUserBtn").show();
    $('html, body').animate({
        scrollTop: $(window).scrollTop() + 120
    }, 200);

    // remove readonly from form
    $("#userEditForm :input").prop("readonly", false);
});

// edit community - show update button
$(document).on('click','#editCommunityBtn',function(){
    $("#editCommunityBtn").remove();
    $("#updateCommunityBtn").show();
    $('html, body').animate({
        scrollTop: $(window).scrollTop() + 120
    }, 200);

    // remove readonly from form
    $("#communityEditForm :input").prop("readonly", false);
});

// show image preview
$(document).on('change', '#imgUploadUser', function (event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#imgPreview').attr('src', e.target.result);
            $('#imgPreview').attr('width', 80);
            $('#imgPreview').attr('height', 80);
        };
        reader.readAsDataURL(file);
    }
});
// show image preview
$(document).on('change', '#imgUploadnewCreditCard', function (event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#imgPreviewCredit').attr('src', e.target.result);
            $('#imgPreviewCredit').attr('width', 80);
            $('#imgPreviewCredit').attr('height', 80);
        };
        reader.readAsDataURL(file);
    }
});

// community members - communityMembers
// Check if DataTable is already initialized
if ($.fn.DataTable.isDataTable('#communityMembersTable')) {
    $("#communityMembersTable").DataTable().destroy(); // Destroy existing instance
}
if ($('#communityMembersTable').length) {
    var Id = $('#communityMembersTable').attr('data-community-id');
    var communityMembersTable = $("#communityMembersTable").DataTable({
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
        processing : true,
        ajax:{
            url :"ajax.php?action=get_community_members&community_id="+Id, // json datasource
            type: "post",  // method  , by default get
            cache:true,
            data: {Id: Id},
            beforeSend: function () {
                openScreenLoader('Fetching Community Members. Do not refresh this page...');
            },
            error: function(jqXHR) {
                try {
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
            complete : function(data){

                // show no data
                if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                    $("#communityMembersTable_wrapper").hide();
                    $("#noDataMessageUC").show();
                } else {
                    $("#communityMembersTable_wrapper").show();
                    $("#noDataMessageUC").hide();
                }

                // tooltip
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });

                // Highlight search results
                if(communityMembersTable.search()){
                    var regex = new RegExp('('+communityMembersTable.search()+')', 'gi');
                    $('.content').each(function() {
                        var content = $(this);
                        var text = content.text();
                        if (text.match(regex)) {
                            content.parent().addClass('highlight');
                        }
                    });
                }

                // close loader
                closeScreenLoader();
            }
        },
        rowCallback: function(row, data, index) {
            if (index % 2 === 0) {
                $(row).addClass('first-row'); // Even row
            } else {
                $(row).addClass('second-row'); // Odd row
            }
        }
    });
}

// community members - communityMembers
$(document).on('click','#communityDetailsTab',function(){
    $("#editCommunityBtn").show();
    $("#editCommunityBtn").addClass("d-flex");
});

/***
 * 
 * Master Sub Cat
 * 
 */

$('#uploadExcelBtn').click(function() {
    $('#excelFileInput').click();  // Open the file picker
});

// Handle file selection and automatically submit the form
$('#excelFileInput').change(function(event) {
    const file = event.target.files[0];  // Get the selected file

    if (file) {
        // If file is selected, submit the form
        $('#uploadForm').submit();
    } else {
        alert('Please select a file.');
    }
});


/*******
 * 
 * **************************************************************************
 * **************************************************************************
 * **************************************************************************
 * **************************************************************************
 * 
 * 
 *******/


// handleResponse
function handleResponse(response, redirectPath = '') {
    response = JSON.parse(response);
    
    // Check if response indicates success or failure
    if (response.success === 1) {
        
        // display toast
        $.toastr.success(response.message, {position: 'top-center',time: 5000});
        
        if(redirectPath){
            setTimeout(function() {
                window.location.href = redirectPath;
            }, 1000);
        }
    } else {
        // display toast
        $.toastr.error(response.message, {position: 'top-center',time: 5000});
    }
}

// openScreenLoader
function openScreenLoader (msg) {
    HoldOn.open({
        theme:"sk-bounce",
        message: msg,
    });
}

// openScreenLoader
function openScreenLoadeScan (msg) {
    HoldOn.open({
        theme:"sk-bounce",
        message: `<div style="text-align: center;">
                    <p>${msg}</p>
                    <button onclick="stopScan()" 
                        style="padding: 8px 12px; background: red; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        Stop Scan
                    </button>
                  </div>`

    });
}

function stopScan() {
    // Close the loader
    HoldOn.close();

    // Store stop status in localStorage
    localStorage.setItem("scanStatus", 1);

    console.log("Scan stopped and status updated in localStorage.");
}

// closeScreenLoader
function closeScreenLoader (msg) {
    HoldOn.close();
}

// changeUserStatus
function changeUserStatus(element){

    var userId = element.getAttribute("user-id");
    var status = element.getAttribute("status");

    var data = {
        userId: userId,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeUserStatus',
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
}

// changeProfileGroupStatus
function changeProfileGroupStatus(element){

    var id = element.getAttribute("pg-id");
    var status = element.getAttribute("status");

    var data = {
        id: id,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeProfileGroupStatus',
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
}

// changeCountryStatus
function changeCountryStatus(element){

    var code = element.getAttribute("code");

    var data = {
        code: code,
    };

    $.ajax({
        url: 'ajax.php?action=changeCountryStatus',
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
}

// changeFaqStatus
function changeFaqStatus(element){

    var Id = element.getAttribute("user-id");
    var status = element.getAttribute("status");

    var data = {
        Id: Id,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeFaqStatus',
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
}
// changeCommentStatus
function changeCommentStatus(element){

    var Id = element.getAttribute("user-id");
    var status = element.getAttribute("status");

    var data = {
        Id: Id,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeCommentStatus',
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
}

// changeCommunityStatus
function changeLinkStatus(element){

    var Id = element.getAttribute("id");
    var status = element.getAttribute("status");

    var data = {
        id: Id,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeLinkStatus',
        type: 'POST',
        data: data,
        beforeSend: function () {
            openScreenLoader('Updating Link. Do not refresh this page...');
        },
        success: function (response) {
             // Dynamically update the element
             if (status == "1") {
                $(element)
                    .removeClass("userInactive")
                    .addClass("userActive")
                    .attr("status", "0")
                    .attr("data-bs-original-title", "click to deactivate")
                    .html("Active");
            } else {
                $(element)
                    .removeClass("userActive")
                    .addClass("userInactive")
                    .attr("status", "1")
                    .attr("data-bs-original-title", "click to active")
                    .html("Flagged");
            }

            window.location.href = window.location.pathname + '?redirect=referral-tab';

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
// changeCommunityStatus
function changeCommunityStatus(element){

    var Id = element.getAttribute("id");
    var status = element.getAttribute("status");

    var data = {
        Id: Id,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeCommunityStatus',
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

// changeWebAdStatus
function changeWebAdStatus(element){

    var Id = element.getAttribute("id");
    var status = element.getAttribute("status");

    var data = {
        Id: Id,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeWebAdStatus',
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

// changePostImageStatus
function changePostImageStatus(element){

    var userId = element.getAttribute("user-id");
    var show_image = element.getAttribute("show_image");

    var data = {
        post_id: userId,
        show_image: show_image,
    };

    $.ajax({
        url: 'ajax.php?action=changePostImageStatus',
        type: 'POST',
        data: data,
        beforeSend: function () {
            openScreenLoader('Updating Image Status. Do not refresh this page...');
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

// triggerPostEmail
function triggerPostEmail(element){

    var userId = element.getAttribute("user-id");

    var data = {
        post_id: userId,
    };

    $.ajax({
        url: 'ajax.php?action=triggerPostEmail',
        type: 'POST',
        data: data,
        beforeSend: function () {
            openScreenLoader('Sending Email & Text. Do not refresh this page...');
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

// changeDealFeaturedStatus
function changeDealFeaturedStatus(element){

    var userId = element.getAttribute("user-id");
    var status = element.getAttribute("status");

    var data = {
        post_id: userId,
        status: status,
    };

    $.ajax({
        url: 'ajax.php?action=changeDealFeaturedStatus',
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

// Fetch table-specific configuration
function getTableConfig(table) {
    return tableConfig[table] || {};
}

// Handle action buttons
function handleActionButtons(button) {
    const action = button.getAttribute('data-action');
    const Id = button.getAttribute('data-id');
    const table = button.getAttribute('data-table');
    const editLink = button.getAttribute('data-edit-link');
    const isEditPopWindow = button.getAttribute('data-pop-up');
    const viewLink = button.getAttribute('data-view-link');
    const { deleteFunction, deleteTitle, deleteDescription } = getTableConfig(table);

    switch (action) {
        case 'view':
            if (viewLink === "modal") {
                openModal(Id);
            } else {
                window.location.href = viewLink+Id;
            }
            break;

        case 'edit':
            if(isEditPopWindow){
                // Calculate the screen's width and height
                var screenWidth = window.innerWidth;
                var screenHeight = window.innerHeight;

                // Define the popup window's size
                var windowWidth = 800;
                var windowHeight = 600;

                // Calculate the position to center the popup
                var top = Math.max(0, (screenHeight - windowHeight) / 2);
                var left = Math.max(0, (screenWidth - windowWidth) / 2);

                // Open the edit page in a centered popup window
                window.open(editLink + Id, 'editPopup', `width=${windowWidth},height=${windowHeight},top=${top},left=${left},resizable=yes,scrollbars=yes`);
            }else{
                window.location.href = editLink+Id;
            }
            break;

        case 'delete':
            if (deleteFunction) {
                showConfirmationModal(Id, deleteFunction, deleteTitle, deleteDescription, "Delete", "Cancel");
            } else {
                console.error(`Delete function not defined for table: ${table}`);
            }
            break;

        default:
            console.log("Unknown action: " + action);
    }
}

// Show confirmation modal
function showConfirmationModal(Id, yesCallback, popupTitle, popupDescription, confirmText, cancelText) {
    $.confirm({
        title: popupTitle,
        text: popupDescription,
        text: popupDescription,
        confirm: function() {
            yesCallback(Id);
        },
        cancel: function() {
            location.reload();
        },
        confirmButton: confirmText,
        cancelButton: cancelText
    });

    // Center the modal dialog if the header exists
    if ($('.confirmation-modal .modal-header').length > 0) {
        $('.confirmation-modal .modal-dialog').addClass('modal-dialog-centered');
    }
}

// delete user
function deleteUser(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteUser',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting User. Do not refresh this page...');
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
}

// delete profile group
function deleteProfileGroup(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteProfileGroup',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting Profile Group. Do not refresh this page...');
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
}

// delete country
function deleteCountry(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteCountry',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting User. Do not refresh this page...');
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
}

// delete deleteAnanomousRequest
function deleteAnanomousRequest(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteAnanomousRequest',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting Request. Do not refresh this page...');
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
}

// deleteCommunityMember
function deleteCommunityMember(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteCommunityMember',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting Member. Do not refresh this page...');
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
}
// deleteLink
function deleteLink(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteLink',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting Link. Do not refresh this page...');
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
}

// delete faq
function deleteFaq(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteFaq',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting Faq. Do not refresh this page...');
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
}
// delete faq
function deleteCommentSugessions(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteComment',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting Process. Do not refresh this page...');
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
}

// delete community
function deleteCommunity(Id) {
    $.ajax({
        url: 'ajax.php?action=deleteCommunity',
        type: 'POST',
        data: {id: Id},
        beforeSend: function () {
            openScreenLoader('Deleting Community. Do not refresh this page...');
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
}

// delete company
function deleteCompany(id) {
    $.ajax({
        url: 'ajax.php?action=deleteSingleCompany',
        type: 'POST',
        data: {id: id},
        beforeSend: function () {
            openScreenLoader('Deleting Process. Do not refresh this page...');
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
}

function deleteSinglePost(id){
    id = [atob(id)];
    $.ajax({
        url: 'ajax.php?action=delete_posts',
        type: 'POST',
        data: {ids: id},
        beforeSend: function () {
            openScreenLoader('Deleting Process. Do not refresh this page...');
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
}
function deleteCompanyRecommends(id){
       $.ajax({
        url: 'ajax.php?action=deleteCompanyRecommend',
        type: 'POST',
        data: {id: id},
        beforeSend: function () {
            openScreenLoader('Deleting Process. Do not refresh this page...');
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
}
function deleteAD(id){
    $.ajax({
        url: 'ajax.php?action=deleteSingleAds',
        type: 'POST',
        data: {id: id},
        beforeSend: function () {
            openScreenLoader('Deleting Process. Do not refresh this page...');
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
}
function deleteCompanyMember(id){
    $.ajax({
        url: 'ajax.php?action=deleteCompanyMember',
        type: 'POST',
        data: {id: id},
        beforeSend: function () {
            openScreenLoader('Deleting Process. Do not refresh this page...');
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
}

// auto complete
function initAutocomplete() {
    var input = document.getElementById('address');

    // var options = {
    //     types: ["(regions)"], // Limits results to cities and states
    // };

    var options = {
        types: [], // You can change types based on your requirements, e.g., ['geocode'] for more general searches
        //componentRestrictions: { country: 'us' }, // Optional: restrict to a specific country
    };

    // Create the Autocomplete object
    var autocomplete = new google.maps.places.Autocomplete(input, options);

    // Modify the autocomplete UI to remove "USA" from suggestions
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();

        if (place.geometry) {
            var lat = place.geometry.location.lat();
            var lng = place.geometry.location.lng();

            // Set the latitude and longitude in the hidden inputs
            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            // Now extract city and state from the address_components
            var city = '';
            var state = '';
            var country = '';
            for (var i = 0; i < place.address_components.length; i++) {
                var component = place.address_components[i];
                var types = component.types;

                // Get city (locality)
                if (types.includes('locality')) {
                    city = component.long_name;
                } else if (types.includes('sublocality_level_1')) {
                    city = component.long_name;
                } else if (types.includes('sublocality')) {
                    city = component.long_name;
                }

                // Get state (administrative_area_level_1)
                if (types.indexOf('administrative_area_level_1') !== -1) {
                    state = component.short_name;
                }

                // Get country (country)
                if (types.indexOf('country') !== -1) {
                    country = component.short_name;
                }
            }

            // Set city and state in hidden inputs
            console.log(city,state,country);
            document.getElementById('city').value = city;
            document.getElementById('state').value = state;
            document.getElementById('country_code').value = country;
        }
    });
}

// auto complete
function initAutocompleteMulti() {
    const addressInputs = document.querySelectorAll('.address-autocomplete');

    addressInputs.forEach(input => {
        const index = input.id.split('_')[1];

        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: [],
        });

        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            const place = autocomplete.getPlace();

            if (!place.geometry) return;

            const lat = place.geometry.location.lat();
            const lng = place.geometry.location.lng();

            document.getElementById(`latitude_${index}`).value = lat;
            document.getElementById(`longitude_${index}`).value = lng;

            let city = '', state = '', country = '', countryName = '';

            place.address_components.forEach(component => {
                const types = component.types;

                if (types.includes('locality') || types.includes('sublocality') || types.includes('sublocality_level_1')) {
                    city = component.long_name;
                }

                if (types.includes('administrative_area_level_1')) {
                    state = component.short_name;
                }

                if (types.includes('country')) {
                    country = component.short_name;
                    countryName = component.long_name; // for removing from input
                }
            });

            document.getElementById(`city_${index}`).value = city;
            document.getElementById(`state_${index}`).value = state;
            document.getElementById(`country_code_${index}`).value = country;

            // Remove country name from the address input field
            let formattedAddress = place.formatted_address || input.value;
            if (country.toLowerCase() === 'united states' || country.toLowerCase() === 'usa' || country.toLowerCase() === 'us') {
                formattedAddress = formattedAddress.substring(0, formattedAddress.lastIndexOf(',')).trim();
            }
            input.value = formattedAddress.trim();
        });
    });
}

google.maps.event.addDomListener(window, 'load', initAutocompleteMulti);

function createBranchIOLink(button) {
    const id = button.getAttribute('data-id');
    const type = button.getAttribute('data-type');
    //alert(type);
    // Perform AJAX request first
    $.ajax({
        url: 'ajax.php?action=get_deep_link',
        type: 'POST',
        data: {id: id, type: type},
        beforeSend: function () {
            openScreenLoader('Generating Link. Do not refresh this page...');
        },
        success: function (response) {
            response = JSON.parse(response);
            var shareLink = response.link;
            // On successful AJAX request, copy the URL to clipboard
            if (navigator.clipboard) {
                navigator.clipboard.writeText(shareLink)  // Try to write to clipboard
                    .then(() => {
                        $.toastr.success('Copied', {position: 'top-center', time: 5000});
                    })
                    .catch(err => {
                        console.error('Error copying to clipboard: ', err);
                    });
            } else {
                alert('Share Link : ' + shareLink);
            }
        },
        error: function (xhr, status, error) {
            console.error("Error: " + error);
            closeScreenLoader();
        },
        complete: function () {
            closeScreenLoader();
        }
    });
}

function updatePostUser(selectElement, post_id){
    const userId = selectElement.value;
    $.ajax({
        url: 'ajax.php?action=updatePostUser',
        type: 'POST',
        data: {post_id: post_id, user_id: userId},
        beforeSend: function () {
            openScreenLoader('Updating User. Do not refresh this page...');
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
}

// load Logs
var getLogsTable = $("#getLogsTable").DataTable({
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
    processing : false,
    debug: true, // Enable debugging
    sorting: true,
    columnDefs: [
        { orderable: false, targets: 0 },
        { orderable: true, targets: "_all" }    // Disable sorting for all other columns
    ],
    order: [[1, 'asc']], // Default sorting: second column (USER) in ascending order
    ajax:{
        url :"ajax.php?action=get_logs", // json datasource
        type: "post",  // method  , by default get
        cache:true,
        "data":function(data) {
            data.status = $('#statusSelect').val();
        },
        beforeSend: function () {
            openScreenLoader('Fetching Logs. Do not refresh this page...');
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
        complete : function(data){

            // show no data
            if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                $("#getLogsTable_wrapper").hide();
                $("#noDataMessage").show();
            } else {
                $("#getLogsTable_wrapper").show();
                $("#noDataMessage").hide();
            }

            // tooltip
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });

            // Highlight search results
            if(getLogsTable.search()){
                var regex = new RegExp('('+getLogsTable.search()+')', 'gi');
                $('.content').each(function() {
                    var content = $(this);
                    var text = content.text();
                    if (text.match(regex)) {
                        content.parent().addClass('highlight');
                    }
                });
            }

            // close loader
            closeScreenLoader();
        }
    },
    rowCallback: function(row, data, index) {
        if (index % 2 === 0) {
            $(row).addClass('first-row'); // Even row
        } else {
            $(row).addClass('second-row'); // Odd row
        }
    }
});

$('#searchLogs').on('input', function() {
    var searchTerm = $(this).val();
    getLogsTable.search(searchTerm).draw();
});

/**
 * Test Notification Fn.
*/
$(document).on("click", ".test-noti-fn", function () {
  var button = $(this);
  var noti_id = button.data("id");
  openScreenLoader('Sending Notification to Admin. Do not refresh this page...');
  $.ajax({
      url: 'ajax.php?action=test_notification_fn',
      type: 'POST',
      data: { noti_id: noti_id },
      success: function (response) {
          try {
              var jsonResponse = JSON.parse(response);
              if (jsonResponse.success) {
                $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                location.reload();
              }else{
                $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
              }
          } catch (e) {
              console.error("Invalid JSON response:", response);
          }
      },
      error: function () {
          console.log("Failed to test noti.");
      }
  });
});