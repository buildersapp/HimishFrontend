function toggleShareOptions() {
    const shareOption = document.getElementById('share_option').value;
    document.getElementById('email_section').style.display = shareOption === 'email' ? 'block' : 'none';
    if(shareOption === 'email'){
        addEmailField();
    }else{
        $("#email_fields").html('');
    }
    updateCouponLimit();
}

function addEmailField() {
    const emailFields = document.getElementById('email_fields');
    const div = document.createElement('div');
    div.classList.add('d-flex', 'mb-2');
    div.innerHTML = `
        <input type="email" name="emails[]" class="form-control" placeholder="Enter email" required>
        <i class="fa fa-times-circle text-danger" onclick="removeEmailField(this)"></i>
    `;
    emailFields.appendChild(div);
}

function removeEmailField(button) {
    button.parentElement.remove();
}

function toggleCouponOptions() {
    const couponSection = document.getElementById('coupon_section');
    couponSection.style.display = document.getElementById('include_coupon').value === 'yes' ? 'block' : 'none';
}

function updateCouponLimit() {
    const couponType = document.getElementById('coupon_type').value;
    const couponLimitText = document.getElementById('coupon_limit');
    if (couponType === 'percentage') {
        couponLimitText.textContent = `Max limit: ${percentageLimit}%`;
        document.getElementById('coupon_value').setAttribute('max', percentageLimit);
    } else {
        couponLimitText.textContent = `Max limit: $${fixedLimit}`;
        document.getElementById('coupon_value').setAttribute('max', fixedLimit);
    }
}

var getAdsInvitation = $("#getAdsInvitation").DataTable({
    dom: '<"top">rt<"bottom"lp><"clear">',
    drawCallback: function () {
        $("html, body").animate({ scrollTop: 0 }, "fast");
    },
    language: {
        searchPlaceholder: "Enter your search term...",
        emptyTable: "",
    },
    serverSide: true,
    processing: false,
    ajax: {
        url: "ajax.php?action=getAdsInvitation", // JSON datasource for companies
        type: "post", // method, by default get
        cache: true,
        sorting: true,
        columnDefs: [
            { orderable: false, targets: 0 },
            { orderable: true, targets: "_all" }, // Disable sorting for all other columns
        ],
        order: [[1, "asc"]],
        data: function (data) {},
        beforeSend: function () {
            openScreenLoader("Fetching Process. Do not refresh this page...");
        },
        error: function (jqXHR) {
            try {
                console.log("inner");
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
        complete: function (data) {
            // Show or hide no data message
            if (data.responseJSON && data.responseJSON.recordsTotal === 0) {
                $("#getAdsInvitation_wrapper").hide();
                $("#noDataMessage").show();
            } else {
                $("#getAdsInvitation_wrapper").show();
                $("#noDataMessage").hide();
            }

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Highlight search results
            if (getAdsInvitation.search()) {
                var regex = new RegExp("(" + getAdsInvitation.search() + ")", "gi");
                $(".content").each(function () {
                    var content = $(this);
                    var text = content.text();
                    if (text.match(regex)) {
                        content.parent().addClass("highlight");
                    }
                });
            }

            // Close loader
            closeScreenLoader();
        },
    },
    rowCallback: function (row, data, index) {
        if (index % 2 === 0) {
            $(row).addClass("second-row"); // Even row
        } else {
            $(row).addClass("first-row"); // Odd row
        }
    },
});

$("#getAdsInvitationSearch").on("input", function () {
    var searchTerm = $(this).val();
    getAdsInvitation.search(searchTerm).draw();
});


function copyToClipboard(text) {
    var tempInput = document.createElement("input");
    document.body.appendChild(tempInput);
    tempInput.value = text;
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
    alert("Link copied to clipboard!");
}