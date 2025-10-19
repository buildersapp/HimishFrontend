let selectedCommunities = [];
let locationsArray = [];
let selectedLocations = [];
window.allCommunityLocations = window.allCommunityLocations || [];
window.myCommunityLocations = window.myCommunityLocations || [];
window.myCommunityPricing = window.myCommunityPricing || [];
window.currentCommunityCost = 0;
let receivedOTP = '';
$(document).ready(function () {
    $("#expire_date").datepicker({
        format: "mm/dd/yyyy",
        autoclose: true,
        todayHighlight: true,
        startDate: "0d", // prevent selecting past dates (optional)
    });

    // Initialize Select2
    $('.js-example-basic-single').select2();

    $('.js-example-basic-single').on('change select2:select', function () {
        const connectToExisting = $('#connectToExisting').val();
        const val = $(this).val();
        const selectedOption = $(this).find('option:selected');
        const companyName = selectedOption.data('name') || "";
        const OwnerId = selectedOption.data('owner-id') || 0;
        const reqSent = selectedOption.data('req-sent') || 0;
        const branchesComp = selectedOption.data('branches') || [];
        if (branchesComp.length > 0) {
            branchesComp.forEach(branch => {
                let newLocation = {
                    address: branch.address || '',
                    latitude: branch.latitude || '',
                    longitude: branch.longitude || '',
                    state: branch.state || '',
                    city: branch.city || '',
                    country_code: branch.country_code || '',
                    community_id: branch.id || 0
                };

                // Check if city + state already exists in locationsArray
                let exists = locationsArray.some(
                    loc => loc.city === newLocation.city && loc.state === newLocation.state
                );

                if (!exists) {
                    locationsArray.push(newLocation);
                }
            });

            // Optional: render after all branches are processed
            renderLocationCheckboxes();
        }

        $("#display-company-name").val(val ? companyName : "");
        $("#company_name_prv").text(val ? companyName : "");

        $("#display-company-name-hd").val(val ? companyName : "");
        if (connectToExisting === '0') {
            $('#adTypeButtons').toggle(!!val);
        }else{
            $('#msgExistingErr').text("");
            $('#msgExistingIn').text("");
            if(OwnerId === 0){
                $('#associationReq').hide();
                if(reqSent > 0){
                    $('#msgExistingErr').text("You have already submitted a request to claim this company. Please wait for approval.");
                    $('#claimBusinessReq').hide();
                }else{
                    $('#msgExistingIn').text("No one has claimed this company yet. You can be the first to request ownership!");
                    $('#msgExistingErr').text("");
                    $('#claimBusinessReq').show();
                }
            }else{
                $('#msgExistingErr').text("This company has already been claimed by another user. However, you can still submit an association request to connect with this business.");
                $('#associationReq').show();
                $('#claimBusinessReq').hide();
            }
        }
    });

    // send association request
    $('#associationReq').on('click', function () {
        const companyId = $('.js-example-basic-single').val();
        showConfirmationModal({
            text: "Do you want to send association request ?",
            confirmText: "Yes",
            cancelText: "Cancel",
            onConfirm: () => {
                $.ajax({
                    url: 'ajax.php?action=send_association_request',
                    method: 'POST',
                    data: { company_id: companyId, role: 'Partner' },
                    success: function (response) {
                        try {
                        var jsonResponse = JSON.parse(response);
                        if (jsonResponse.success) {
                            $.toastr.success(jsonResponse.message, {position: 'top-center',time: 5000});
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        }else{
                            $.toastr.error(jsonResponse.message, {position: 'top-center',time: 5000});
                        }
                        } catch (e) {
                        console.error("Invalid JSON response:", response);
                        }
                    },
                    error: function () {
                        $.toastr.error('Error occurred while sending association request.', { position: 'top-center', time: 5000 });
                    }
                });
            },
            onCancel: () => {
                console.log("Cancelled.");
            }
        });
    });

    // Handle community selection
    $(".community-radio-ch").change(function () {
        let parentBox = $(this).closest(".single-com-box");
        let communityId = $(this).val();

        // Extract data from HTML attributes
        let communityData = {
            id: communityId,
            address: $(this).data("address"),
            latitude: $(this).data("latitude"),
            country_code: $(this).data("country-code"),
            longitude: $(this).data("longitude"),
            name: $(this).data("name"),
            description: $(this).data("description"),
            state: $(this).data("state"),
            city: $(this).data("city"),
            price: $(this).data("price"),
        };

        if ($(this).is(":checked")) {
            selectedCommunities.push(communityData);
            parentBox.css("background", "linear-gradient(90deg, rgba(70, 20, 202, 1) 0%, rgba(149, 77, 225, 1) 100%)");
            parentBox.find(".com-title, .com-text").css("color", "#fff");

            // Add to locations if it has valid coordinates
            if (communityData.latitude && communityData.longitude) {
                locationsArray.push({
                    address: communityData.address,
                    latitude: communityData.latitude,
                    longitude: communityData.longitude,
                    country_code: communityData.country_code,
                    state: communityData.state,
                    city: communityData.city,
                    community_id: communityData.id,
                });
                renderLocationCheckboxes();
            }
        } else {
            // Remove community from selected list
            selectedCommunities = selectedCommunities.filter((comm) => comm.id !== communityId);
            parentBox.css("background", "#f1f5f9");
            parentBox.find(".com-title").css("color", "#222");
            parentBox.find(".com-text").css("color", "#484848");

            // Remove related location
            locationsArray = locationsArray.filter((loc) => loc.latitude !== communityData.latitude || loc.longitude !== communityData.longitude);
        }

        updateSelectionCount();
    });

    // Select All / Unselect All functionality
    $(".btn-unselect-all").click(function () {
        let allCheckboxes = $(".community-radio-ch");

        if (selectedCommunities.length === allCheckboxes.length) {
            selectedCommunities = [];
            locationsArray = [];
            allCheckboxes.prop("checked", false).each(function () {
                let parentBox = $(this).closest(".single-com-box");
                parentBox.css("background", "#f1f5f9");
                parentBox.find(".com-title").css("color", "#222");
                parentBox.find(".com-text").css("color", "#484848");
            });
            $(this).html("Select All");
        } else {
            selectedCommunities = [];
            locationsArray = [];
            allCheckboxes.prop("checked", true).each(function () {
                let communityId = $(this).val();
                let communityData = {
                    id: communityId,
                    address: $(this).data("address"),
                    latitude: $(this).data("latitude"),
                    longitude: $(this).data("longitude"),
                    country_code: $(this).data("country-code"),
                    name: $(this).data("name"),
                    description: $(this).data("description"),
                    state: $(this).data("state"),
                    city: $(this).data("city"),
                    price: $(this).data("price"),
                };

                selectedCommunities.push(communityData);
                if (communityData.latitude && communityData.longitude) {
                    locationsArray.push({
                        address: communityData.address,
                        latitude: communityData.latitude,
                        longitude: communityData.longitude,
                        country_code: communityData.country_code,
                        state: communityData.state,
                        city: communityData.city,
                        community_id: communityData.id,
                    });
                    renderLocationCheckboxes();
                }

                let parentBox = $(this).closest(".single-com-box");
                parentBox.css("background", "linear-gradient(90deg, rgba(70, 20, 202, 1) 0%, rgba(149, 77, 225, 1) 100%)");
                parentBox.find(".com-title, .com-text").css("color", "#fff");
            });
            $(this).html("Unselect All");
        }

        updateSelectionCount();
    });

    // Search functionality
    $(".comunity-search-input input").on("input", function () {
        let searchValue = $(this).val().toLowerCase();
        $(".single-com-box").each(function () {
            let title = $(this).find(".com-title").text().toLowerCase();
            $(this).toggle(title.includes(searchValue));
        });
    });

    // Save location from input field
    $("#address").on("blur", function () {
        setTimeout(function () {
            let userEnteredAddress = $("#address").val() || 0;
            let latitude = $("#latitude").val() || 0;
            let longitude = $("#longitude").val() || 0;
            let city = $("#city").val() || "";
            let state = $("#state").val() || "";
            let country_code = $("#country_code").val() || "US";
            if (userEnteredAddress && city && state) {
                let newLocation = {
                    address: userEnteredAddress,
                    latitude: latitude, // Manually enter or fetch lat/lng from API
                    longitude: longitude,
                    state: state, // Manually enter or fetch from API
                    city: city,
                    country_code: country_code,
                    community_id : 0
                };

                locationsArray = locationsArray.filter(loc => !(loc.community_id === 0 ));
                
                // Check if the city and state combination is already in the locationsArray
                if (!locationsArray.some(loc => loc.city === newLocation.city && loc.state === newLocation.state)) {
                    // Only push the new location if the city and state are not already in the array
                    locationsArray.push(newLocation);
                    //renderLocationCheckboxes();
                }
            }
        }, 1000);
    });

    // Function to update selected locations in hidden input
    $(document).on("change", ".location-checkbox", function () {

        $(".location-checkbox:checked").each(function () {
            let index = $(this).val();
            selectedLocations.push(locationsArray[index]);
        });

        $("#selectedLocationsInput").val(JSON.stringify(selectedLocations));
        //console.log("Updated Selected Locations:", selectedLocations);

        // Show only the first selected location in preview
        if (selectedLocations.length > 0) {
            const firstLocation = selectedLocations[0];
            $("#location_name_prv").text(`${firstLocation.city}, ${firstLocation.state}`);
        } else {
            $("#location_name_prv").text("No location selected");
        }
    });

    // Update selected count and log data
    function updateSelectionCount() {
        $(".stat-item-2 h4").text(selectedCommunities.length);
        $("#totalSelectedComm").text(selectedCommunities.length);

        $("#selectedCommunitiesInput").val(JSON.stringify(selectedCommunities));
        $("#selectedLocationsInput").val(JSON.stringify(locationsArray));

        let freeLimit = parseInt($("#numberOfFreeCommunity").val()) || 0;

        if (selectedCommunities.length > 0) {
            let totalPrice = 0;
            let freeCommunities = [];
            let paidGroups = {}; // { price: [names] }

            // Sort by price so free first
            let sortedCommunities = [...selectedCommunities].sort((a, b) => parseFloat(a.price) - parseFloat(b.price));

            sortedCommunities.forEach((comm, index) => {
                let price = parseFloat(comm.price) || 0;
                if (index < freeLimit) {
                    freeCommunities.push(comm.name);
                } else {
                    totalPrice += price;
                    if (!paidGroups[price]) {
                        paidGroups[price] = [];
                    }
                    paidGroups[price].push(comm.name);
                }
            });

            let chargesListHtml = `
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="set-f-18-700-bold">Charges Summary</h3>
                    <h3 class="set-f-18-700-bold">$${totalPrice.toFixed(2)}</h3>
                </div>
            `;

            if (freeCommunities.length > 0) {
                chargesListHtml += `
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="set-payment-para-left mt-1">
                            ${freeCommunities.length} Communit${freeCommunities.length > 1 ? 'ies' : 'y'}
                            <span class="set-f-12-near-black">(${freeCommunities.join(", ")})</span>
                        </p>
                        <h4 class="set-payment-para-left fw-semibold">$0.00</h4>
                    </div>
                `;
            }

            // Add paid groups grouped by price
            for (let price in paidGroups) {
                let names = paidGroups[price];
                chargesListHtml += `
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="set-payment-para-left mt-1">
                            ${names.length} Communit${names.length > 1 ? 'ies' : 'y'}
                            <span class="set-f-12-near-black">(${names.join(", ")})</span>
                        </p>
                        <h4 class="set-payment-para-left fw-semibold">$${parseFloat(price).toFixed(2)} / day</h4>
                    </div>
                `;
            }

            $("#chargesList").html(chargesListHtml);
            $("#chargesSummaryCard").show();

            // Keep preview updated
            renderCommunityPreview(selectedCommunities);

        } else {
            $("#chargesSummaryCard").hide();
        }
    }

    $(".community-radio").on("click", function () {
        let selectedValue = $("input[name='community_type']:checked").val();

        const start = parseStartDate($("#hiddenStartDate").val());
        const end = parseEndDate($("#hiddenEndDate").val());
        if (isNaN(start.getTime()) && isNaN(end.getTime())) {
            $.toastr.error('Please select Start - End Date.', {position: 'top-center', time: 5000});
            return false;
        }

        if (selectedValue === "2") {
            $("#selecComunityDetailsModal").modal("show");
        }
    });

    $(".community-radio").on("change", function () {

        const start = parseStartDate($("#hiddenStartDate").val());
        const end = parseEndDate($("#hiddenEndDate").val());
        if (isNaN(start.getTime()) && isNaN(end.getTime())) {
            $.toastr.error('Please select Start - End Date.', {position: 'top-center', time: 5000});
            return false;
        }else{

            let selectedValue = $(this).val().trim(); // Get selected radio value

            // Clear only locations with community_id > 0
            locationsArray = locationsArray.filter(loc => loc.community_id <= 0);

            if (selectedValue.toLowerCase() === "2") {
                text = 'Choose';
                $("#selecComunityDetailsModal").modal('show');
            }else if (selectedValue.toLowerCase() === "1") {
                text = 'My';
                //console.log(window.myCommunityLocations);
                locationsArray = locationsArray.filter(loc => loc.community_id <= 0).concat(window.myCommunityLocations);
            }else{
                text = 'All';
                //console.log(locationsArray);
                locationsArray = locationsArray;
                window.currentCommunityCost = $('#defaultAllCommunityPrice').val();
                triggerCalcCost();
            }

            setDefaultLocationAddressField();
            
            $('#totalSelectedComm').text(text);

            // Remove selected class from all items
            $(".communites-contentsx-item").removeClass("selected");
            
            // Add selected class to the clicked item
            $(this).parent().addClass("selected");

            renderLocationCheckboxes();
            $("#communitiesWrapper").show();
            $("#communitySummaryRows").show();
            $("#expandedCommunityList").show();

            // Show/hide community preview
            if (selectedValue === "1") {
                renderCommunityPreview(window.myCommunityPricing,1); // pass your updated array
            } else if (selectedValue === "2") {
                renderCommunityPreview(selectedCommunities); // pass your updated array
            } else {
                $("#communitySummaryRows").hide();
                $("#expandedCommunityList").hide();
                $("#totalSelectedCommCount").html('All Communities <br/> <small class="text-muted">Package deal $'+parseFloat(window.currentCommunityCost)+' per day</small>');
            }
        }
    });
});

function renderCommunityPreview(communities, type = 2) {
    let freeLimit = parseInt($("#numberOfFreeCommunity").val()) || 0;
    window.currentCommunityCost = 0;

    if(type !== 2){
        freeLimit = 0;
    }

    // Sort by price (cheapest first)
    let sorted = [...communities].sort((a, b) => parseFloat(a.price) - parseFloat(b.price));

    let expandedHtml = "";
    let summaryGroups = {};

    sorted.forEach((comm, index) => {
        let originalPrice = parseFloat(comm.price) || 0;
        let isFree = index < freeLimit; // First X communities are free
        let displayPrice = isFree ? 0 : originalPrice;

        window.currentCommunityCost += displayPrice;

        let label = displayPrice === 0 ? "Free" : `$${displayPrice.toFixed(2)}`;

        expandedHtml += `
            <li class="d-flex justify-content-between mb-2">
                <span>${comm.name}</span>
                <span class="${displayPrice === 0 ? 'text-muted' : ''}">${label}</span>
            </li>
        `;

        if (!summaryGroups[displayPrice]) {
            summaryGroups[displayPrice] = { count: 0, total: 0, price: displayPrice };
        }
        summaryGroups[displayPrice].count++;
        summaryGroups[displayPrice].total += displayPrice;
    });

    // Build summary rows
    let summaryHtml = "";
    Object.values(summaryGroups).forEach(group => {
        if (group.price === 0) {
            summaryHtml += `
                <div class="d-flex justify-content-between mb-1">
                    <span>${group.count} @ $0.00 <span class="fs-12 text-muted">(Included free)</span></span>
                    <span>$0.00</span>
                </div>
            `;
        } else {
            summaryHtml += `
                <div class="d-flex justify-content-between mb-1">
                    <span>${group.count} @ $${group.price.toFixed(2)} ea.</span>
                    <span>$${group.total.toFixed(2)}</span>
                </div>
            `;
        }
    });

    // Update DOM
    $("#expandedCommunityList").html(expandedHtml);
    $("#communitySummaryRows").html(summaryHtml);
    $("#totalSelectedCommCount").text(communities.length);

    // 🔹 Trigger total recalculation with updated community cost
    triggerCalcCost();
}

function triggerCalcCost(){
    // 🔹 Trigger total recalculation with updated community cost
    const start = parseStartDate($("#hiddenStartDate").val());
    const end = parseEndDate($("#hiddenEndDate").val());
    if (!isNaN(start.getTime()) && !isNaN(end.getTime())) {
        calculateAdCost(start, end, window.currentCommunityCost);
    }
}

// Function to render checkboxes for locations
function renderLocationCheckboxes() {
    $("#listingLoc").show();
    let container = $("#locationCheckboxes");
    container.empty(); // Clear previous checkboxes

    const seen = new Set();

    locationsArray.forEach((location, index) => {
        const key = `${location.city}-${location.state}`.toLowerCase();

        if (!seen.has(key)) {
            seen.add(key);
            let checkboxHtml = `
                <div class="form-check">
                    <input class="form-check-input location-checkbox" type="checkbox" value="${index}" id="loc-${index}">
                    <label class="form-check-label" for="loc-${index}">
                        ${location.city}, ${location.state}
                    </label>
                </div>
            `;
            container.append(checkboxHtml);
        }
    });
}

function validateStep1() {
    const company_id = $('.js-example-basic-single').val();
    const selected = $('input[name="ad_type"]:checked').val();
    
    if (!company_id || company_id === "0") {
        $.toastr.error('Please select your company.', { position: 'top-center', time: 5000 });
        return false;
    }

    if (!selected) {
        $.toastr.error('Please select Ad Type (Video or Image).', { position: 'top-center', time: 5000 });
        return false;
    }

    return true;
}

function validateStep2() {

    const adType = $('input[name="ad_type"]:checked').val();
    if( adType === "video") {
        const videoFile = document.getElementById('imageUploadInput69').files[0];
        if (!videoFile) {
            $.toastr.error('Please upload a video file.', { position: 'top-center', time: 5000 });
            return false;
        }
    }else if (adType === "image") {

        const formMode = document.getElementById('ad_id').value.trim();
        const formMode2 = document.getElementById('bp_post_id').value.trim();
        const listingLogo1 = document.getElementById('imageUploadInput4'); // listing image 1
        const listingLogo2 = document.getElementById('imageUploadInput5'); // listing image 2
        const listingLogo3 = document.getElementById('imageUploadInput6'); // listing image 3

        if(formMode == 0 && formMode2 == 0){
            // Check if at least one listing image is uploaded
            if (
                (!listingLogo1.files || listingLogo1.files.length === 0) &&
                (!listingLogo2.files || listingLogo2.files.length === 0) &&
                (!listingLogo3.files || listingLogo3.files.length === 0)
            ) {
                $.toastr.error('Please upload at least one ad image before proceeding.', {position: 'top-center', time: 5000});
                return false;
            }
        }
    }

    const title = document.getElementById('title').value.trim();
    if (title === "") {
        $.toastr.error('Please enter title.', {position: 'top-center', time: 5000});
        return false;
    }

    const hiddenStartDate = document.getElementById('hiddenStartDate').value.trim();
    const hiddenEndDate = document.getElementById('hiddenEndDate').value.trim();
    if (hiddenStartDate === "" || hiddenEndDate === "") {
        $.toastr.error('Please select Start - End Date.', {position: 'top-center', time: 5000});
        return false;
    }

    // const hiddenKeywords = document.getElementById('hidden-keywords').value.trim();
    // if (hiddenKeywords === "") {
    //     $.toastr.error('Please select keywords.', {position: 'top-center', time: 5000});
    //     return false;
    // }

    const selectedLocationsInput = document.getElementById('selectedLocationsInput').value.trim();
    if (selectedLocationsInput === "") {
        $.toastr.error('Please select community & Ad location.', {position: 'top-center', time: 5000});
        return false;
    }

    return true;
}

function validateStep3() {

    const button_type_val = document.getElementById('button_type_val').value.trim();

    // Helper regex functions
    const isValidEmail = (email) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    const isValidURL = (url) => {
        try {
            // If the user omitted the protocol, assume http://
            const normalized = url.match(/^[a-z]+:\/\//i) ? url : `http://${url}`;
            new URL(normalized);
            return true;
        } catch (e) {
            return false;
        }
    };

    if (button_type_val === "link_info") {
        const link = document.getElementById('info_link').value.trim();
        if (link === "") {
            $.toastr.error('Please enter link.', { position: 'top-center', time: 5000 });
            return false;
        }
        if (!isValidURL(link)) {
            $.toastr.error('Please enter a valid link.', { position: 'top-center', time: 5000 });
            return false;
        }
    } else if (button_type_val === "whatsapp") {
        const whatsapp_phone = document.getElementById('whatsapp_phone').value.trim();
        if (whatsapp_phone === "") {
            $.toastr.error('Please enter WhatsApp number.', { position: 'top-center', time: 5000 });
            return false;
        }

        const whatsapp_message = document.getElementById('whatsapp_message').value.trim();
        if (whatsapp_message === "") {
            $.toastr.error('Please enter message.', { position: 'top-center', time: 5000 });
            return false;
        }
    } else if (button_type_val === "quote") {
        const quote_link = document.getElementById('quote_link').value.trim();
        if (quote_link === "") {
            $.toastr.error('Please enter quote link.', { position: 'top-center', time: 5000 });
            return false;
        }
        if (!isValidURL(quote_link)) {
            $.toastr.error('Please enter a valid quote link.', { position: 'top-center', time: 5000 });
            return false;
        }
    } else if (button_type_val === "emailSec") {
        const email = document.getElementById('email_sec').value.trim();
        const subject = document.getElementById('subject_sec').value.trim();
        const message = document.getElementById('message_sec').value.trim();

        if (email === "") {
            $.toastr.error('Please enter email.', { position: 'top-center', time: 5000 });
            return false;
        }
        if (!isValidEmail(email)) {
            $.toastr.error('Please enter a valid email.', { position: 'top-center', time: 5000 });
            return false;
        }

        if (subject === "") {
            $.toastr.error('Please enter subject.', { position: 'top-center', time: 5000 });
            return false;
        }

        if (message === "") {
            $.toastr.error('Please enter message.', { position: 'top-center', time: 5000 });
            return false;
        }
    }

    return true;
}

function validateForm() {

    HoldOn.open({
        theme: "sk-bounce",
        message: "Your ad is being created..."
    });

    return true;
}


function nextStep(step, setField = null) {
    if (step === 3) {
        window.scrollTo({ top: 20, behavior: "smooth" });
    }

    if (step === 2 && !validateStep1()) return;
    if (step === 3 && !validateStep2()) return;
    if (step === 4 && !validateStep3()) return;

    if(setField){
        $('#ad_type').val(setField);
    }

    document.querySelectorAll(".form-step").forEach((el) => (el.style.display = "none"));
    document.getElementById("step" + step).style.display = "block";
    updateStepsUI(step);
}

function prevStep(step) {
    nextStep(step);
}

function updateStepsUI(activeStep) {
    document.querySelectorAll(".single-step").forEach((step, index) => {
        if (index + 1 <= activeStep) step.classList.add("step-active");
        else step.classList.remove("step-active");
    });
}

function handleCTAChange(activeId) {
    // Set hidden value
    $("#button_type_val").val(activeId);

    // Show/hide related sections
    const ids = ['link_info', 'whatsapp', 'quote', 'emailSec'];
    ids.forEach(id => {
        console.log(id);
        document.getElementById(id).style.display = (id === activeId) ? 'block' : 'none';
    });

    // Find the radio button with value == activeId
    const matchingRadio = $(`input[name="button_type"]`).filter(function () {
        return $(this).closest('.form-check').attr('onclick')?.includes(activeId);
    });

    // Get the closest <li> and find the button inside it
    const selectedButton = matchingRadio.closest('li').find('button').first();

    if (selectedButton.length) {
        // Clone the button and show it in preview
        $("#ad_button_prv").html(selectedButton.clone());
    } else {
        // Fallback: show capitalized text
        $("#ad_button_prv").text(activeId.charAt(0).toUpperCase() + activeId.slice(1));
    }
}


function previewImage(event, imgId, textId, crpId) {
    const img = event.target.files[0];
    if (!img) return;

    if (
        !pixelarity.open(
            img,
            false,
            function (res, faces) {
                const previewImg = document.getElementById(imgId);
                const previewWrapper = previewImg.closest(".comunity-image-upload-preview");
                const wrapper = previewImg.closest(".comunity-image-upload-wrapper");
                const label = wrapper.querySelector(".comunity-image-upload-label");

                previewImg.src = res;
                previewImg.style.height = "130px";
                previewImg.style.objectFit = "cover";
                previewImg.classList.remove("img-fluid");

                if (label) label.style.display = "none";
                if (previewWrapper) {
                    previewWrapper.style.display = "flex";
                    previewWrapper.style.justifyContent = "center";
                    previewWrapper.style.alignItems = "center";
                    previewWrapper.style.border = "1px dashed #5a5a5a";
                }

                if(imgId === "previewImg4"){
                    const wrapper = $('#adMediaPreviewWrapper');
                    wrapper.find('img, video').remove();
                    const imageElement = $('<img>', {
                        src: res,
                        class: 'img-fluid',
                        alt: 'ads preview',
                        id: 'image_ad_prv'
                    });
                    wrapper.prepend(imageElement);
                }

                // Optional: hidden input for cropped image
                const hiddenInput = document.getElementById(crpId);
                if (hiddenInput) hiddenInput.value = res;

                document.querySelectorAll(".face").forEach(el => el.remove());

                faces.forEach(face => {
                    let faceBox = document.createElement("div");
                    faceBox.className = "face";
                    faceBox.style.position = "absolute";
                    faceBox.style.height = face.height + "px";
                    faceBox.style.width = face.width + "px";
                    faceBox.style.top = previewImg.getBoundingClientRect().top + face.y + "px";
                    faceBox.style.left = previewImg.getBoundingClientRect().left + face.x + "px";
                    document.body.appendChild(faceBox);
                });
            },
            "jpg",
            0.7,
            true
        )
    ) {
        alert("Whoops! That is not an image!");
    }
}

// Remove image logic
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".remove-img").forEach(button => {
        button.addEventListener("click", function (e) {
            e.stopPropagation();
            const wrapper = this.closest(".comunity-image-upload-wrapper");
            const previewWrapper = wrapper.querySelector(".comunity-image-upload-preview");
            const previewImg = wrapper.querySelector("img.previewImage");
            const label = wrapper.querySelector(".comunity-image-upload-label");
            const input = wrapper.querySelector("input[type='file']");
            const text = wrapper.querySelector(".uploadTextcomunity");

            if (previewImg) previewImg.src = "";
            if (previewWrapper) previewWrapper.style.display = "none";
            if (label) label.style.display = "flex";
            if (text) text.innerHTML = label.textContent.trim();
            if (input) input.value = "";
        });
    });
});

function setDefaultLocationAddressField() {
    // Get user values from localStorage
    const userLatitude = localStorage.getItem('userLatitude');
    const userLongitude = localStorage.getItem('userLongitude');
    const userState = localStorage.getItem('userState');
    const userCity = localStorage.getItem('userCity');
    const userAddress = localStorage.getItem('userAddress');
    const userCountryCode = localStorage.getItem('userCountryCode');

    // Get fallback current values from localStorage
    const currentLatitude = localStorage.getItem('currentLatitude');
    const currentLongitude = localStorage.getItem('currentLongitude');
    const currentState = localStorage.getItem('currentState');
    const currentCity = localStorage.getItem('currentCity');
    const currentAddress = localStorage.getItem('currentAddress');
    const currentCountryCode = localStorage.getItem('currentCountryCode');

    // Use fallback if user values are empty or null
    const finalLatitude = userLatitude && userLatitude.trim() !== '' ? userLatitude : currentLatitude;
    const finalLongitude = userLongitude && userLongitude.trim() !== '' ? userLongitude : currentLongitude;
    const finalState = userState && userState.trim() !== '' ? userState : currentState;
    const finalCity = userCity && userCity.trim() !== '' ? userCity : currentCity;
    const finalAddress = userAddress && userAddress.trim() !== '' ? userAddress : currentAddress;
    const finalCountryCode = userCountryCode && userCountryCode.trim() !== '' ? userCountryCode : currentCountryCode;

    // Set values to fields
    $("#latitude").val(finalLatitude);
    $("#longitude").val(finalLongitude);
    $("#state").val(finalState);
    $("#city").val(finalCity);
    $("#address").val(finalAddress);

    // Push to locationsArray
    locationsArray.push({
        address: finalAddress,
        latitude: finalLatitude,
        longitude: finalLongitude,
        state: finalState,
        city: finalCity,
        country_code: finalCountryCode,
        community_id: 0,
    });
}

function clearDefaultLocationAddressField(){
    $("#latitude").val('');
    $("#longitude").val('');
    $("#state").val('');
    $("#city").val('');
    $("#address").val('');
}

const userTypeRadios = document.querySelectorAll('[name="user_type"]');
userTypeRadios.forEach(el => {
    console.log(el.id, 'Visible:', el.offsetParent !== null);
});

/**
 * Get Company Suggestions
*/
$(document).on("keyup", ".company-suggestion-search", function () {
    var searchValue = $(this).val();
  
    if (searchValue.length < 2) {
      $(".global-search-dropdown-cmp").hide();
      return;
    }
  
    $.ajax({
        url: 'ajax.php?action=get_company_suggestion',
        type: 'POST',
        data: { search: searchValue },
        success: function (response) {
            try {
                var jsonResponse = JSON.parse(response);
                var dropdown = $(".global-search-dropdown-cmp");
                dropdown.empty();
    
                jsonResponse.forEach(category => {
                    var categoryTitle = `<div class="global-search-category-cmp" data-id="${category.id}" data-company-name="${category.name}">${category.name}</div>`;
                    dropdown.append(categoryTitle);
                });                
  
                if (jsonResponse.length > 0) {
                    dropdown.show();
                } else {
                    dropdown.hide();
                }
            } catch (e) {
                console.error("Invalid JSON response:", response);
            }
        },
        error: function () {
            console.log("Failed to fetch company suggestions.");
        }
    });
});

$(document).on("click", ".global-search-category-cmp", function () {
    const companyId = $(this).data("id");
    const companyName = $(this).data("company-name");

    // Set the values
    $("#selected_company_id").val(companyId);
    $("#company_name").val(companyName);
    $("#company_name_dp").val(companyName);

    // Optionally hide the dropdown
    $(".global-search-dropdown-cmp").hide();
});

// Keywords Tags Functionality
$(document).ready(function () {
    const maxTags = 5;
    let tags = [];

    // Function to render tags
    function renderTags() {
        const container = $(".communites-contentsx-tag-keywords");
        const previewContainer = $("#ad_tags_preview");

        container.empty();
        previewContainer.empty(); // Clear previous tags in preview
        tags.forEach(tag => {
            const tagHtml = `
            <div class="communites-contentsx-tag-item d-flex align-items-center justify-content-center">
                <span>${tag}</span>
                <button class="communites-contentsx-tag-item-close d-flex align-items-center justify-content-center" data-tag="${tag}">
                <img src="assets/img/delete-tags.svg" alt="delete">
                </button>
            </div>`;
            container.append(tagHtml);

            // Ads Preview Display
            const tagBtn = $("<button>")
                .addClass("ads-preview-tag")
                .text(tag);
                previewContainer.append(tagBtn);
        });

        // Update hidden field
        $('#hidden-keywords').val(tags.join(','));
    }

    // Add a tag if valid
    function addTag(tag) {
      const cleanTag = tag.trim();
      if (cleanTag && tags.length < maxTags && !tags.includes(cleanTag.toLowerCase())) {
        tags.push(cleanTag);
        renderTags();
        $('#search-tags').val('');
      }
    }

    window.addTag = addTag;
    window.renderTags = renderTags;

    // Add tag on Enter or comma
    $('#search-tags').on('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addTag($(this).val());
      }
    });

    // Add tag on tick button click
    $('#add-tag-manually').on('click', function () {
      addTag($('#search-tags').val());
    });

    // Remove tag
    $(document).on('click', '.communites-contentsx-tag-item-close', function () {
      const tagToRemove = $(this).data('tag');
      tags = tags.filter(tag => tag !== tagToRemove);
      renderTags();
    });
});

function updateAdTitle(input) {
    const value = $(input).val().trim();
    $('#ad_title_prv').text(value !== '' ? value : 'Ad title here');
}

function deleteMedia(id) {
    if (confirm("Are you sure you want to delete this media?")) {
        // Send AJAX request to delete, or hide the element directly
        console.log("Deleting media with ID:", id);
        // Example: document.getElementById('media-block-' + id).remove();

        $.ajax({
            url: 'ajax.php?action=delete_ads_image',
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
    }else{
        location.reload();
    }
}

function updateAdMediaPreview(input, isVideo = false) {
    const wrapper = $('#adMediaPreviewWrapper');
    wrapper.find('img, video').remove();

    // If hidden input has a value, show it as the preview
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            if (isVideo) {
                const videoElement = $('<video>', {
                    src: e.target.result,
                    controls: true,
                    autoplay: true,
                    muted: true,
                    class: 'img-fluid',
                });
                wrapper.prepend(videoElement);
            }
        };

        reader.readAsDataURL(file);
    }
}

// For video
$('#imageUploadInput69').on('change', function () {
    updateAdMediaPreview(this, true);
});

$('input[name="ad_type"]').on('change', function () {
    const formMode = document.getElementById('ad_id').value.trim();
    const formMode2 = document.getElementById('bp_post_id').value.trim();
    if(formMode == 0 && formMode2 == 0){
        $('#adMediaPreviewWrapper').find('img, video').remove();
        $('#adMediaPreviewWrapper').prepend(
            `<img src="assets/img/create-ads-preview.png" alt="ads preview" id="image_ad_prv" class="img-fluid" />`
        );
    }
});
