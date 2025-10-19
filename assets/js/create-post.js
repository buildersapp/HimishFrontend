let selectedCommunities = [];
let locationsArray = [];
let selectedLocations = [];
window.allCommunityLocations = window.allCommunityLocations || [];
window.myCommunityLocations = window.myCommunityLocations || [];
$(document).ready(function () {
    $("#expire_date").datepicker({
        format: "mm/dd/yyyy",
        autoclose: true,
        todayHighlight: true,
        startDate: "0d", // prevent selecting past dates (optional)
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
            longitude: $(this).data("longitude"),
            country_code: $(this).data("country-code"),
            name: $(this).data("name"),
            description: $(this).data("description"),
            state: $(this).data("state"),
            city: $(this).data("city"),
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
            let country_code = $("#country_code").val() || "";
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
                    renderLocationCheckboxes();
                }
            }
        }, 1000);
    });

    // Function to update selected locations in hidden input
    $(document).on("change", ".location-checkbox", function () {

        $(".location-checkbox:checked").each(function () {
            let index = $(this).val();
            let newLocation = locationsArray[index];

            // Check if city and state combination already exists
            let alreadyExists = selectedLocations.some(loc =>
                loc.city === newLocation.city && loc.state === newLocation.state
            );

            if (!alreadyExists) {
                selectedLocations.push(newLocation);
            }
        });

        $("#selectedLocationsInput").val(JSON.stringify(selectedLocations));
        //console.log("Updated Selected Locations:", selectedLocations);
    });

    // Update selected count and log data
    function updateSelectionCount() {
        $(".stat-item-2 h4").text(selectedCommunities.length);
        $("#totalSelectedComm").text(selectedCommunities.length);
        // Save JSON data in hidden fields
        $("#selectedCommunitiesInput").val(JSON.stringify(selectedCommunities));
        $("#selectedLocationsInput").val(JSON.stringify(locationsArray));
    }

    $(".community-radio").on("click", function () {
        let selectedValue = $("input[name='community_type']:checked").val();
        if (selectedValue === "2") {
            $("#selecComunityDetailsModal").modal("show");
        }
    });

    $(".community-radio").on("change", function () {
        let selectedValue = $(this).val().trim(); // Get selected radio value

        // Clear only locations with community_id > 0
        locationsArray = locationsArray.filter(loc => loc.community_id <= 0);

        if (selectedValue.toLowerCase() === "2") {
            text = 'Choose';
            $("#selecComunityDetailsModal").modal('show');
        }else if (selectedValue.toLowerCase() === "1") {
            text = 'My';
            //console.log(window.myCommunityLocations);
            locationsArray = locationsArray.filter(loc => loc.community_id <= 0)
                                       .concat(window.myCommunityLocations);
        }else{
            text = 'All';
            //console.log(locationsArray);
            locationsArray = locationsArray;
            // locationsArray = locationsArray.filter(loc => loc.community_id <= 0)
            //                            .concat(window.allCommunityLocations || []);
        }

        setDefaultLocationAddressField();
        
        $('#totalSelectedComm').text(text);

        // Remove selected class from all items
        $(".communites-contentsx-item").removeClass("selected");
        
        // Add selected class to the clicked item
        $(this).parent().addClass("selected");

        renderLocationCheckboxes();
    });
});

// Function to render checkboxes for locations
function renderLocationCheckboxes() {
    $("#listingLoc").show();
    let container = $("#locationCheckboxes");
    container.empty(); // Clear previous checkboxes

    const seen = new Set();

    locationsArray.forEach((location, index) => {

        // Skip if city or state is missing/blank
        if (!location.city || !location.state || location.city.trim() === "" || location.state.trim() === "") {
            return; // skip this iteration
        }

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

function previewImage(event, imgId, textId) {
    var img = event.target.files[0];
    if (
        !pixelarity.open(
            img,
            false,
            function (res, faces) {
                //console.log("Faces detected:", faces);

                // Set cropped image preview
                var previewImg = document.getElementById(imgId);
                previewImg.src = res;
                previewImg.style.height = "220px";
                previewImg.style.objectFit = "cover";
                previewImg.classList.remove("img-fluid");
                $(".uploadIconComunity").css("width", "unset");

                // Remove upload text
                document.getElementById(textId).innerHTML = "";
                document.getElementById("croppedImage").value = res;

                // Remove previous face rectangles
                document.querySelectorAll(".face").forEach((el) => el.remove());

                scanImage(res);

                // Draw face detection boxes (if any)
                faces.forEach((face) => {
                    let faceBox = document.createElement("div");
                    faceBox.className = "face";
                    faceBox.style.position = "absolute";
                    //faceBox.style.border = '2px solid red';
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

function base64ToFile(base64, filename) {
    const arr = base64.split(',');
    const mime = arr[0].match(/:(.*?);/)[1];
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    
    while (n--) {
        u8arr[n] = bstr.charCodeAt(n);
    }

    return new File([u8arr], filename, { type: mime });
}

function scanImage(file) {
    localStorage.setItem("scanStatusWeb", "0");
    let formData = new FormData();
    const fileA = base64ToFile(file, "uploaded-image.png");
    formData.append("image", fileA);
    let index = 0;
    $.ajax({
        url: localStorage.getItem("scanApiUrl"),
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        beforeSend: function () {
            window.ajaxScanCompleted = false;
            index = 0;

            const messages = [
                "Reading Company info",
                "Getting Company Details",
                "We got it..",
                "Finalizing..."
            ];
            
            // Open loader once
            openScreenLoaderScanCP(messages[index], true);

            // Update message only, no blinking
            window.scanTextInterval = setInterval(function () {
                if (!window.ajaxScanCompleted) {
                    index = (index + 1) % messages.length;
                    openScreenLoaderScanCP(messages[index]); // Just update message
                }
            }, 3000);
        },
        success: function (response) {
            // response =JSON.parse(response);
            console.log(response,"==========response")
            if (response) {
                //console.log(response);
                // Check if scanStatus is "1", then don't append values
                if (localStorage.getItem("scanStatusWeb") !== "1") {
                    if (response.locations.length > 0) {

                        const locations = response.locations;

                        // Clear existing dynamic location fields
                        dynamicContainer.innerHTML = "";

                        if (locations.length === 1) {
                            // Single location: populate static fields
                            const loc = locations[0];
                            if(loc.city && loc.state){
                                $("#address").val(loc.address);
                                $("#latitude").val(loc.latitude);
                                $("#longitude").val(loc.longitude);
                                $("#city").val(loc.city);
                                $("#state").val(loc.state);
                                $("#country_code").val(loc.country_code);
                            }

                            if (loc.phone_numbers.length > 0) {
                                $("#phone").val(loc.phone_numbers[0]);
                            }
                            if (loc.email_addresses.length > 0) {
                                $("#email").val(loc.email_addresses[0]);
                            }

                            if(loc.state && loc.city){
                                locationsArray.push({
                                    address: loc.address,
                                    latitude: loc.latitude,
                                    longitude: loc.longitude,
                                    state: loc.state,
                                    city: loc.location_name,
                                    country_code: loc.country_code,
                                    community_id: 0,
                                });
                            }

                        } else {

                            // hide default address
                            $("#defaultAddrDf").hide();
                            $("#locationSelGroup").hide();
                            $("#locationSelGroupBtn").show();

                            // Multiple locations: render dynamic fields
                            locations.forEach((loc, index) => {
                                const wrapper = createInputField(index, "default");

                                // Fill the fields with location data
                                const addressInput = wrapper.querySelector(".address-autocomplete");
                                if (addressInput && loc.city && loc.state) {
                                    addressInput.value = loc.address;
                                    addressInput.dataset.lat = loc.latitude;
                                    addressInput.dataset.lng = loc.longitude;
                                    addressInput.dataset.city = loc.city;
                                    addressInput.dataset.state = loc.state;
                                    addressInput.dataset.zipcode = ""; // If you have it
                                }

                                if(loc.city && loc.state){
                                    locationsArray.push({
                                        address: loc.address,
                                        latitude: loc.latitude,
                                        longitude: loc.longitude,
                                        state: loc.state,
                                        city: loc.city,
                                        country_code: loc.country_code,
                                        community_id: 0,
                                    });
                                }

                                const nameInput = wrapper.querySelector('input[placeholder="Enter Location Name"]');
                                if (nameInput) nameInput.value = loc.location_name;

                                const phoneInput = wrapper.querySelector('input[placeholder="Enter Phone Number"]');
                                if (phoneInput && loc.phone_numbers.length > 0) {
                                    phoneInput.value = loc.phone_numbers[0];

                                    const phoneInputById = document.getElementById('phone-input-1');
                                    if (phoneInputById && phoneInputById.value.trim() === '' && loc.phone_numbers.length > 0) {
                                        phoneInputById.value = loc.phone_numbers[0];
                                    }
                                }

                                dynamicContainer.appendChild(wrapper);
                            });

                            updateHiddenField("company_branches_array");
                            initAutoCompleteGoogle();
                        }

                        setDefaultLocationAddressField();
                        renderLocationCheckboxes();

                        if (response.locations[0].phone_numbers.length > 0) {
                            $("#phone-input-1").val(response.locations[0].phone_numbers[0]);
                        }
                        if (response.locations[0].email_addresses.length > 0) {
                            $("#email").val(response.locations[0].email_addresses[0]);
                        }
                    } else {
                        if (response.phone_numbers.length > 0) {
                        //console.log(response.phone_numbers[0]);
                            $("#phone-input-1").val(response.phone_numbers[0]);
                        }
                        if (response.email_addresses.length > 0) {
                            $("#email").val(response.email_addresses[0]);
                        }
                    }
                    $("#title").val(response.title);
                    $("#company_name").val(response.company_name);
                    $("#service").val(response.keywords.toString());
                    $("#keywords").val(response.keywords.toString());
                    $("#scanResults").val(JSON.stringify(response));

                    // Dynamically generate keyword checkboxes
                    renderKeywords(response.keywords || []);
                }
                nextStep(2);
                window.scrollTo({ top: 600, behavior: "smooth" });
            }
            //console.log("Image uploaded successfully:", response);
            // alert('Image Scanned successfully!');
        },
        complete: function (data) {
            window.ajaxScanCompleted = true;
            clearInterval(window.scanTextInterval);
            closeScreenLoader();
        },
        error: function (error) {
            closeScreenLoader();
            console.error("Error uploading image:", error);
            alert("Failed to upload image!");
        },
    });
}

// openScreenLoaderScanCP
function openScreenLoaderScanCP(msg, isFirst = false) {
    if (isFirst) {
        HoldOn.open({
            theme: "sk-bounce",
            message: `<div id="scanLoaderContent" style="text-align: center;">
                        <img src="assets/img/load_image_loader.gif" width="80" />
                        <p id="scanLoaderMsg">${msg}</p>
                        <button id="abortScanBtn" onclick="abortScanCP()" 
                            style="padding: 8px 12px; background: #8256ff; color: white; border: none; border-radius: 5px; cursor: pointer; display: none;">
                            Abort Scan & Fill Manually
                        </button>
                      </div>`
        });

        // Show the button after 10 seconds
        setTimeout(function () {
            const btn = document.getElementById("abortScanBtn");
            if (btn) btn.style.display = "inline-block";
        }, 12000); // 10 seconds
    } else {
        const msgEl = document.getElementById("scanLoaderMsg");
        if (msgEl) msgEl.textContent = msg;
    }
}

function abortScanCP() {
    // Close the loader
    HoldOn.close();

    // Store stop status in localStorage
    localStorage.setItem("scanStatusWeb", 1);

    nextStep(2);

}

function validateStep1() {
    const imageInput = document.getElementById('imageUploadInput');
    // if (!imageInput.files || imageInput.files.length === 0) {
    //     alert('Please upload an image before proceeding.');
    //     return false;
    // }
    return true;
}

function validateStep2() {
    const companyName = document.getElementById('company_name').value.trim();
    if (companyName === "") {
        alert('Please enter your company name.');
        return false;
    }

    // const phoneInputs = document.querySelectorAll('.phone-input');
    // let hasPhone = false;
    // phoneInputs.forEach(input => {
    //     if (input.value.trim() !== "") {
    //         hasPhone = true;
    //     }
    // });
    // if (!hasPhone) {
    //     alert('Please enter at least one phone number.');
    //     return false;
    // }

    // const worldwideChecked = document.getElementById('worldwide-checkbox').checked;
    // const mobileChecked = document.getElementById('mobile-checkbox').checked;
    // const address = document.getElementById('address').value.trim();

    // if (!worldwideChecked && !mobileChecked && address === "") {
    //     alert('Please enter an address or select Mobile/Worldwide business.');
    //     return false;
    // }

    return true;
}

function validateForm() {
    // Open the loading spinner
    HoldOn.open({
        theme: "sk-bounce",
        message: "Creating your post ! Please wait..."
    });

    const title = document.getElementById("title").value.trim();
    const communityRadios = document.querySelectorAll("input[name='community_type']");
    const selectedCommunities = document.getElementById("selectedCommunitiesInput").value.trim();
    const userType = document.querySelector("input[name='user_type']:checked");

    // Validate Post Title
    if (title === "") {
        $.toastr.error('Please enter a post title.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Validate Community Type Selection
    let selectedCommunityType = null;
    communityRadios.forEach(radio => {
        if (radio.checked) selectedCommunityType = radio.value;
    });

    // if (selectedCommunityType === null) {
    //     $.toastr.error('Please select community type.', {position: 'top-center', time: 5000});
    //     HoldOn.close();  // Close the loader if validation fails
    //     return false;
    // }

    // If 'Choose Communities' is selected, validate selected communities
    // if (selectedCommunityType === "2" && selectedCommunities === "") {
    //     $.toastr.error('Please select at least one community.', {position: 'top-center', time: 5000});
    //     HoldOn.close();  // Close the loader if validation fails
    //     return false;
    // }

    // Validate Location Selection
    if (selectedLocations.length === 0) {
        $.toastr.error('Please select at least one location.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Validate Post As (Myself or Anonymous)
    if (!userType) {
        $.toastr.error('Please select how you want to post (Myself or Anonymous).', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Close the loader if all validations pass
    HoldOn.close();
    
    // If all validations pass, return true to submit the form
    return true;
}

function nextStep(step) {
    if (step === 3) {
        window.scrollTo({ top: 20, behavior: "smooth" });
    }

    if (step === 2 && !validateStep1()) return;
    if (step === 3 && !validateStep2()) return;

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

// Renders the keyword buttons (unchecked by default)
function renderKeywords(keywords = []) {
    const uniqueKeywords = [...new Set(keywords)];
    const keywordContainer = $(".keywords-wraper-post-details");
    keywordContainer.empty();

    uniqueKeywords.forEach((keyword) => {
        const keywordBtn = `
            <label class="keyword-btn">
                <input type="checkbox" value="${keyword}" />
                ${keyword}
            </label>
        `;
        keywordContainer.append(keywordBtn);
    });

    handleKeywordSelection(); // Set up click handlers
    updateSelectedCount();
}

// Handles click to select/unselect keywords
function handleKeywordSelection() {
    const keywordContainer = $(".keywords-wraper-post-details");

    keywordContainer.off("click", ".keyword-btn"); // Remove old listeners
    keywordContainer.on("click", ".keyword-btn", function () {
        const checkbox = $(this).find("input[type='checkbox']");

        // Toggle checkbox state manually
        checkbox.prop("checked", !checkbox.prop("checked"));

        // Update visual active class
        $(this).toggleClass("active", checkbox.prop("checked"));

        updateSelectedCount();
    });
}

// Updates the "Selected" count display
function updateSelectedCount() {
    const selectedKeywords = $(".keywords-wraper-post-details input[type='checkbox']:checked")
        .map(function () {
            return $(this).val();
        })
        .get();

    // Update selected count
    $("#selectedCount").text(selectedKeywords.length);

    // Update hidden input
    $("#service").val(selectedKeywords.join(", "));
}

/****
 *
 * PHONE NUMBER FIELD
 *
 */

let phoneCount = 1;

// Function to format phone number
function formatPhoneNumber(value) {
    const cleaned = value.replace(/\D/g, "").substring(0, 10); // Only digits, max 10
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{0,4})$/);
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    return cleaned;
}

// Add event delegation to format phone number on input
document.addEventListener("input", function (e) {
    if (e.target.classList.contains("phone-input")) {
        e.target.value = formatPhoneNumber(e.target.value);
    }
});

// Add new phone input
document.getElementById("add-phone-button").addEventListener("click", function () {
    phoneCount++;
    const phoneGroup = document.createElement("div");
    phoneGroup.className = "post-something-form-group location-group phone-field";
    phoneGroup.innerHTML = `
        <div class="input-left">
            <label for="phone-input-${phoneCount}" class="post-something-form-label">Phone No.</label>
        </div>
        <div class="input-right location-wraper">
            <div class="input-wrapper">
                <input type="text" placeholder="Phone Number" id="phone-input-${phoneCount}" class="form-input phone-input" />
                <label for="phone-input-${phoneCount}" class="input-icon">
                    <img src="assets/img/call-calling.png" alt="Call Icon" />
                </label>
                <label for="phone-input-${phoneCount}" class="postdetails-form-bar"></label>
            </div>
            <div class="addPhone-button-wrapper">
                <button type="button" class="remove-phone-button">
                    <img src="assets/img/delete.png" alt="Delete Phone" />
                </button>
            </div>
        </div>
    `;
    document.getElementById("phone-number-fields").appendChild(phoneGroup);
});

// Remove phone input on delete click
document.getElementById("phone-number-fields").addEventListener("click", function (e) {
    if (e.target.closest(".remove-phone-button")) {
        const phoneField = e.target.closest(".phone-field");
        if (phoneField) phoneField.remove();
    }
});

/***
 *
 * COMPANY BRANCHES & LOCATIONS
 *
 */

const dynamicContainer = document.getElementById("dynamicLocationFields");
const mobileCheckbox = document.getElementById("mobile-checkbox");
const worldwideCheckbox = document.getElementById("worldwide-checkbox");
const nationwideCheckbox = document.getElementById("nationwide-checkbox");

mobileCheckbox.addEventListener("change", (e) => renderLocationFields(e));
worldwideCheckbox.addEventListener("change", (e) => renderLocationFields(e));
nationwideCheckbox.addEventListener("change", (e) => renderLocationFields(e));

function renderLocationFields(event) {
    dynamicContainer.innerHTML = "";

    const isMobile = mobileCheckbox.checked;
    const isWorldwide = worldwideCheckbox.checked;
    //const isNationwide = nationwideCheckbox.checked;

    const selectedCount = [isMobile, isWorldwide].filter(Boolean).length;

    if (selectedCount >= 2) {
        event.target.checked = false;
        alert("Select either 'Mobile Business' or 'Worldwide' or 'Nationwide', or leave unchecked.");
        return false;
    } else if (isMobile) {
        mobileBusinessLocationFields();
        initAutoCompleteGoogle();
        document.getElementById("snTLocBtn").setAttribute("onclick", "addField('mobile')");
        $("#business_location_type").val('Local');
        $("#is_worldwide").val(0);
    } else if (isWorldwide) {
        //worldwideLocationFields();
        initAutoCompleteGoogle();
        $("#business_location_type").val('Worldwide');
        $("#is_worldwide").val(1);
        //document.getElementById("snTLocBtn").setAttribute("onclick", "addField('worldwide')");
    } else {
        noSelectionLocationFields();
        initAutoCompleteGoogle();
        document.getElementById("snTLocBtn").setAttribute("onclick", "addField('default')");
        $("#is_worldwide").val(0);
        $("#business_location_type").val('Local');
    }
}

function createInputField(index, type) {
    const wrapper = document.createElement("div");
    if (type === "default") {
        wrapper.className = "post-something-form-group-2 location-group-1 mb-3";
    }else{
        wrapper.className = "post-something-form-group location-group";
    }

    let html = "";

    if (type === "default") {
        html += `<div class="input-right location-wraper">
            <div class="input-wrapper">
            <input type="text" class="form-input" placeholder="Enter Location Name" autocomplete="off" />
            
            </div>

            <div class="input-wrapper">
            <input type="text" class="form-input phone-input" placeholder="Enter Phone Number" autocomplete="off" />
            
            </div>
        </div>`;
    }

    html += `<div class="input-right location-wraper mt-3">
      <div class="input-wrapper">
        <input type="text" class="form-input address-autocomplete" placeholder="City, State, or Zip Code" onfocus="this.setAttribute('autocomplete', 'new-password');" />
        
      </div>`;

    if (type === "mobile") {
        html += `<div class="input-right location-wraper">
        <div class="input-wrapper">
          <input type="number" class="form-input" placeholder="Enter Miles" />
          
        </div>`;
    }

    // if (index === 0) {
    //     html += `<div class="addPhone-button-wrapper">
    //     <button type="button" onclick="addField('${type}')">
    //       <img src="assets/img/plusblue.png" alt="Add" />
    //     </button>
    //   </div>`;
    // } else {
    //     html += `<div class="addPhone-button-wrapper">
    //     <button type="button" onclick="removeField(this)">
    //       <img src="assets/img/minus1.png" alt="Remove" />
    //     </button>
    //   </div>`;
    // }

    html += `<div class="addPhone-button-wrapper">
        <button type="button" onclick="removeField(this, '`+type+`')">
          <img src="assets/img/delete.png" alt="Remove" />
        </button>
    </div>`;

    html += `</div>`;

    wrapper.innerHTML = html;
    return wrapper;
}

function noSelectionLocationFields() {
    const newField = createInputField(0, "default");
    dynamicContainer.appendChild(newField);
    const fieldId = "company_branches_array";
    const inputs = newField.querySelectorAll("input");
    inputs.forEach((input) => {
        input.addEventListener("input", () => updateHiddenField(fieldId));
        input.addEventListener("focus", () => updateHiddenField(fieldId));
    });
}

function worldwideLocationFields() {
    const newField = createInputField(0, "worldwide");
    dynamicContainer.appendChild(newField);
    const fieldId = "company_branches_array";
    const addressInput = newField.querySelector(".address-autocomplete");
    if (addressInput) {
        addressInput.addEventListener("input", () => updateHiddenField(fieldId));
        addressInput.addEventListener("focus", () => updateHiddenField(fieldId));
        addressInput.addEventListener("blur", () => updateHiddenField(fieldId));
    }
}

function triggerFocusBlur(inputElement, delay = 100) {
    if (!inputElement) return;
    setTimeout(() => {
        inputElement.focus();
        setTimeout(() => {
            inputElement.blur();
        }, delay); // Time before blur after focus
    }, 50); // Initial delay before focus
}

function mobileBusinessLocationFields() {
    const newField = createInputField(0, "mobile");
    dynamicContainer.appendChild(newField);
    const fieldId = "company_service_areas_array";
    const inputs = newField.querySelectorAll("input");
    inputs.forEach((input) => {
        input.addEventListener("input", () => updateHiddenField(fieldId));
        input.addEventListener("focus", () => updateHiddenField(fieldId));
    });
}

function addField(type) {
    const currentFields = dynamicContainer.children.length;
    const newField = createInputField(currentFields, type);
    dynamicContainer.appendChild(newField);
    initAutoCompleteGoogle();

    // Attach event listeners to inputs inside the new field
    const fieldId = type === "mobile" ? "company_service_areas_array" : "company_branches_array";
    const inputs = newField.querySelectorAll("input");

    inputs.forEach((input) => {
        input.addEventListener("input", () => updateHiddenField(fieldId));
        input.addEventListener("focus", () => updateHiddenField(fieldId));
    });
}

function removeField(el, type) {
    const wrapper = el.closest(type === 'default' ? ".post-something-form-group-2   " : ".post-something-form-group");
    if (wrapper) wrapper.remove();
}

function updateHiddenField(fieldId) {
    const dataArray = [];

    const fieldGroups = Array.from(dynamicContainer.querySelectorAll(".post-something-form-group.location-group"));

    fieldGroups.forEach((group) => {
        const addressInput = group.querySelector(".address-autocomplete");
        const address = addressInput?.value || "";
        const lat = addressInput?.dataset.lat || "";
        const lng = addressInput?.dataset.lng || "";
        const state = addressInput?.dataset.state || "";
        const city = addressInput?.dataset.city || "";
        const zipcode = addressInput?.dataset.zipcode || "";

        if (fieldId === "company_branches_array") {
            const name = group.querySelector('input[placeholder="Enter Location Name"]')?.value || "";
            const phone = group.querySelector('input[placeholder="Enter Phone Number"]')?.value || "";
            dataArray.push({
                name,
                phone_numbers: phone,
                address,
                state,
                city,
                zipcode,
                latitude: lat,
                longitude: lng,
            });
        }

        if (fieldId === "company_service_areas_array") {
            const miles = group.querySelector('input[placeholder="Enter Miles"]')?.value || "";
            dataArray.push({
                miles,
                address,
                state,
                city,
                zipcode,
                latitude: lat,
                longitude: lng,
            });
        }
    });

    document.getElementById(fieldId).value = JSON.stringify(dataArray);
}

// Initialize default state
document.addEventListener("DOMContentLoaded", () => {
    //renderLocationFields();
    initAutoCompleteGoogle();
    //setDefaultLocationAddressField();
});

// function setDefaultLocationAddressField(){
//     $("#latitude").val(localStorage.getItem('userLatitude'));
//     $("#longitude").val(localStorage.getItem('userLongitude'));
//     $("#state").val(localStorage.getItem('userState'));
//     $("#city").val(localStorage.getItem('userCity'));
//     $("#address").val(localStorage.getItem('userAddress'));
//     locationsArray.push({
//         address: localStorage.getItem('userAddress'),
//         latitude: localStorage.getItem('userLatitude'),
//         longitude: localStorage.getItem('userLongitude'),
//         state: localStorage.getItem('userState'),
//         city: localStorage.getItem('userCity'),
//         country_code: localStorage.getItem('userCountryCode'),
//         community_id: 0,
//     });
// }

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

    console.log(locationsArray);
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

    // Optionally hide the dropdown
    $(".global-search-dropdown-cmp").hide();
});


function initAutoCompleteGoogle() {
    const addressInputs = document.querySelectorAll(".address-autocomplete");
    addressInputs.forEach((input) => {
        const autocomplete = new google.maps.places.Autocomplete(input, {
            types: [],
        });

        google.maps.event.addListener(autocomplete, "place_changed", function () {
            const place = autocomplete.getPlace();
            if (place.geometry) {
                const address = place.formatted_address;
                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();

                var city = '';
                var zipcode= '';
                var state = '';
                var country = '';
                var fullAddress = place.formatted_address;

                place.address_components.forEach((component) => {
                    const types = component.types;

                    if (types.includes('locality')) {
                        city = component.long_name;
                    } else if (types.includes('sublocality_level_1')) {
                        city = component.long_name;
                    } else if (types.includes('sublocality')) {
                        city = component.long_name;
                    }

                    if (types.includes("administrative_area_level_1")) {
                        state = component.short_name;
                    }

                    if (types.includes("postal_code")) {
                        zipcode = component.long_name;
                    }

                    // Get country (country)
                    if (types.indexOf('country') !== -1) {
                        country = component.short_name;
                    }
                });

                // Remove country name from formatted_address if the country is USA
                if (country.toLowerCase() === 'united states' || country.toLowerCase() === 'usa' || country.toLowerCase() === 'us') {
                    fullAddress = fullAddress.substring(0, fullAddress.lastIndexOf(',')).trim();
                }

                input.dataset.lat = lat;
                input.dataset.lng = lng;
                input.dataset.city = city;
                input.dataset.state = state;
                input.dataset.zipcode = zipcode;

                locationsArray.push({
                    address: fullAddress,
                    latitude: lat,
                    longitude: lng,
                    state: state,
                    city: city,
                    country_code: country,
                    community_id: 0,
                });

                renderLocationCheckboxes();

                triggerFocusBlur(input);
            }
        });
    });
}

/****
 * 
 * DROPZONE
 */

const dropZone = document.getElementById("globalDropZone");
const fileInputDrop = document.getElementById("imageUploadInput");

// Show drop zone when dragging over page
window.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropZone.style.display = "block";
});

// Hide drop zone when not dragging
window.addEventListener("dragleave", (e) => {
    if (e.target === dropZone) {
        dropZone.style.display = "none";
    }
});

// Handle drop
window.addEventListener("drop", (e) => {
    e.preventDefault();
    dropZone.style.display = "none";

    if (e.dataTransfer.files.length > 0) {
        const file = e.dataTransfer.files[0];

        // Set the dropped file to the file input (simulate selection)
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInputDrop.files = dataTransfer.files;

        // Trigger onchange manually
        const event = new Event("change", { bubbles: true });
        fileInputDrop.dispatchEvent(event);
    }
});