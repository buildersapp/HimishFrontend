var companyTable = $("#ananomousRequestTable").DataTable({
    dom: '<"top">rt<"bottom"lp><"clear">', // Add "B" for buttons
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
    sorting: false,
    ajax: {
    url: "ajax.php?action=ananomousRequest", // JSON datasource for companies
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
            console.log("Unexpected server response:", jqXHR.responseText);
            alert("An unexpected error occurred. Please try again later.");
        }
    },
    complete: function(data) {

        // Show or hide no data message
        if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
            $("#ananomousRequestTable_wrapper").hide();
            $("#noDataMessage").show();
        } else {
            $("#ananomousRequestTable_wrapper").show();
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