let selectedCommunities = [];
let locationsArray = [];
let selectedLocations = [];
window.allCommunityLocations = window.allCommunityLocations || [];
window.myCommunityLocations = window.myCommunityLocations || [];
$(document).ready(function () {

    // Save location from input field
    $("#address").on("blur", function () {
        setTimeout(function () {
            let userEnteredAddress = $("#address").val() || 0;
            let latitude = $("#latitude").val() || 0;
            let longitude = $("#longitude").val() || 0;
            let city = $("#city").val() || "Unknown";
            let state = $("#state").val() || "Unknown";
            let country_code = $("#country_code").val() || "Unknown";
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
    });
});

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

function validateStep1() {
    const imageLogo = document.getElementById('imageUploadInput'); // company logo
    const coverLogo1 = document.getElementById('imageUploadInput2'); // cover image 1
    const coverLogo2 = document.getElementById('imageUploadInput3'); // cover image 2
    const listingLogo1 = document.getElementById('imageUploadInput4'); // listing image 1
    const listingLogo2 = document.getElementById('imageUploadInput5'); // listing image 2
    const listingLogo3 = document.getElementById('imageUploadInput6'); // listing image 3

    return true;

    // Check if company logo is uploaded
    if (!imageLogo.files || imageLogo.files.length === 0) {
        $.toastr.error('Please upload company logo before proceeding.', {position: 'top-center', time: 5000});
        return false;
    }

    // Check if at least one cover image is uploaded
    if (
        (!coverLogo1.files || coverLogo1.files.length === 0) &&
        (!coverLogo2.files || coverLogo2.files.length === 0)
    ) {
        $.toastr.error('Please upload at least one cover image (Image 1 or Image 2) before proceeding.', {position: 'top-center', time: 5000});
        return false;
    }

    // Check if at least one listing image is uploaded
    if (
        (!listingLogo1.files || listingLogo1.files.length === 0) &&
        (!listingLogo2.files || listingLogo2.files.length === 0) &&
        (!listingLogo3.files || listingLogo3.files.length === 0)
    ) {
        $.toastr.error('Please upload at least one listing image (Image 1 or Image 2) before proceeding.', {position: 'top-center', time: 5000});
        return false;
    }

    return true;
}

function validateForm() {

    console.log('validateForm Process...');

    // Open the loading spinner
    HoldOn.open({
        theme: "sk-bounce",
        message: "Creating your company ! Please wait..."
    });

    const company_name = document.getElementById("company_name").value.trim();
    const company_email = document.getElementById("company_email").value.trim();
    const company_url = document.getElementById("company_url").value.trim();
    const company_phone = document.getElementById("phone-input-1").value.trim();
    const business_type = document.getElementById("business_type").value.trim();
    const info = document.getElementById("info").value.trim();
    const roleSelect = document.getElementById("role-select").value.trim();

    // Validate Company Name
    if (company_name === "") {
        $.toastr.error('Please enter a company name.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Validate Company Email
    if (company_email === "") {
        $.toastr.error('Please enter a company email.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Validate Company Url
    if (company_url === "") {
        $.toastr.error('Please enter a company url.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Validate Company Phone
    if (company_phone === "") {
        $.toastr.error('Please enter a company phone.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Validate Company Business Type
    if (business_type === "") {
        $.toastr.error('Please enter a company business type.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Validate Company Description
    if (info === "") {
        $.toastr.error('Please enter a company description.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Validate Company roleSelect
    if (roleSelect === "") {
        $.toastr.error('Please select association with company.', {position: 'top-center', time: 5000});
        HoldOn.close();  // Close the loader if validation fails
        return false;
    }

    // Close the loader if all validations pass
    HoldOn.close();
    
    // If all validations pass, return true to submit the form
    return true;
}

function nextStep(step) {
    if (step === 2) {
        window.scrollTo({ top: 20, behavior: "smooth" });
    }

    if (step === 2 && !validateStep1()) return;

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
    const isNationwide = nationwideCheckbox.checked;

    const selectedCount = [isMobile, isWorldwide, isNationwide].filter(Boolean).length;

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
    } else if (isNationwide) {
        //worldwideLocationFields();
        initAutoCompleteGoogle();
        $("#business_location_type").val('Nationwide');
        $("#is_worldwide").val(2);
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
        const country_code = addressInput?.dataset.country_code || "";

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
                country_code
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
                country_code
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
        url: 'ajax.php?action=search_company_fnc',
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
                input.dataset.country_code = country;

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

// Company Type Selection
document.querySelectorAll('#companyTypeGroup button').forEach(button => {
    button.addEventListener('click', function () {
        // Remove "active" from all
        document.querySelectorAll('#companyTypeGroup button').forEach(btn => btn.classList.remove('active'));

        // Add "active" to clicked one
        this.classList.add('active');

        // Set hidden input value
        document.getElementById('companyType').value = this.getAttribute('data-value');
    });
});


// Keywords Tags Functionality

const maxTags = 5;
let tags = [];

// Add a tag if valid
function addTag(tag) {
    const cleanTag = tag.trim();
    if (cleanTag && tags.length < maxTags && !tags.includes(cleanTag.toLowerCase())) {
    tags.push(cleanTag);
    renderTags();
    $('#search-tags').val('');
    }
}
window.addTag = addTag; // Expose function globally for use in other scripts

// Function to render tags
function renderTags() {
    const container = $(".communites-contentsx-tag-keywords");
    container.empty();
    tags.forEach(tag => {
    const tagHtml = `
        <div class="communites-contentsx-tag-item d-flex align-items-center justify-content-center">
        <span>${tag}</span>
        <button class="communites-contentsx-tag-item-close d-flex align-items-center justify-content-center" data-tag="${tag}">
            <img src="assets/img/delete-tags.svg" alt="delete">
        </button>
        </div>`;
    container.append(tagHtml);
    });

    // Update hidden field
    $('#hidden-keywords').val(tags.join(','));
}
window.renderTags = renderTags; // Expose function globally for use in other scripts

$(document).ready(function () {

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
      console.log('Removing tag:', tagToRemove);
      tags = tags.filter(tag => tag !== tagToRemove);
      renderTags();
    });
});

// Keyword Suggestions Dropdown
(function () {
    const input = document.getElementById("search-tags");
    const dropdown = document.getElementById("keyword-suggestions-dropdown");
    const hiddenKeywordsInput = document.getElementById("hidden-all-keywords");

    input?.addEventListener("input", function () {
        const keyword = this.value.trim().toLowerCase();

        if (!keyword || !hiddenKeywordsInput) {
            dropdown.style.display = "none";
            return;
        }

        const allKeywords = hiddenKeywordsInput.value
            .split(',')
            .map(k => k.trim())
            .filter(Boolean);

        const filtered = allKeywords.filter(k => k.toLowerCase().includes(keyword));

        if (!filtered.length) {
            dropdown.style.display = "none";
            return;
        }

        dropdown.innerHTML = filtered.map(k => `
            <div class="px-3 py-2 keyword-suggestion-item" style="cursor:pointer;">${k}</div>
        `).join("");

        dropdown.style.display = "block";

        dropdown.querySelectorAll(".keyword-suggestion-item").forEach(item => {
            item.addEventListener("click", () => {
                input.value = item.textContent.trim();
                dropdown.style.display = "none";
            });
        });
    });

    document.addEventListener("click", (e) => {
        if (!e.target.closest("#search-tags") && !e.target.closest("#keyword-suggestions-dropdown")) {
            dropdown.style.display = "none";
        }
    });
})();

// Services & Products Modal

let selectedProductIds = [];
let selectedServiceIds = [];
let currentType = "1";

window.selectedProductIds = selectedProductIds;
window.selectedServiceIds = selectedServiceIds;

function fetchItems(type, selectedIds = []) {
    const formData = new FormData();
    formData.append("type", type);
    //formData.append("selectedIds", selectedIds.join(","));
    formData.append("selectedIds", "");
    formData.append("show_in_filter", 1);

    fetch("ajax.php?action=get_services_products", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        const container = document.getElementById("dynamic-service-accordion");
        if (!container) return;

        container.innerHTML = `<div class="accordion" id="myAccordion2">${buildAccordion(data, selectedIds, 0, "myAccordion2", type)}</div>`;
        attachCheckboxEvents(selectedIds, type);
        setupSearch(data, type);
    })
    .catch(err => console.error("AJAX error", err));
}
window.fetchItems = fetchItems;

function renderSelectedTags(type) {
    const selectedIds = type === "1" ? selectedServiceIds : selectedProductIds;
    const hiddenField = type === "1"
        ? document.getElementById("selectedServiceIdsHidden")
        : document.getElementById("selectedProductIdsHidden");
    const container = type === "1"
        ? document.querySelector(".services-selected-divs")
        : document.querySelector(".product-selected-divs");

    hiddenField.value = selectedIds.join(",");

    const allChecked = document.querySelectorAll(`.item-checkbox[data-type="${type}"]:checked`);
    const html = Array.from(allChecked).map(cb => {
        const label = cb.closest("label");
        const title = label.querySelector(".plumbing-category")?.innerText || "Unknown";
        const childTags = label.querySelector(".plumbing-details")?.innerText || "";
        return `
            <div class="communites-contentsx-tag-item d-flex align-items-center justify-content-center ct-card-${cb.value}">
                <span>${title}${childTags ? ` <small class="text-muted">(${childTags})</small>` : ''}</span>
                <button type="button" class="communites-contentsx-tag-item-close d-flex align-items-center justify-content-center remove-selected-ps" data-id="${cb.value}" data-type="${type}">
                    <img src="assets/img/delete-tags.svg" alt="delete">
                </button>
            </div>
        `;
    }).join("");

    container.innerHTML = html;

    container.querySelectorAll(".remove-selected-ps").forEach(btn => {
        btn.addEventListener("click", function () {
            const id = this.getAttribute("data-id");
            const type = this.getAttribute("data-type");

            // 1. Remove the tag/card visually
            const card = this.closest(".ct-card-" + id);
            if (card) card.remove();

            // 2. Uncheck the checkbox
            const checkbox = document.querySelector(`.item-checkbox[data-type="${type}"][value="${id}"]`);
            if (checkbox) checkbox.checked = false;

            // 3. Update the hidden input
            const hiddenInput = document.querySelector(
                type === "1" ? "#selectedServiceIdsHidden" : "#selectedProductIdsHidden"
            );

            if (hiddenInput) {
                const currentValues = hiddenInput.value.split(',').filter(v => v !== id && v !== "");
                hiddenInput.value = currentValues.join(',');
            }
        });
    });
}
window.renderSelectedTags = renderSelectedTags;

function getSelectedIds(type) {
    return type === "1" ? selectedServiceIds : selectedProductIds;
}
window.getSelectedIds = getSelectedIds;

function buildAccordion(data, selectedIds, level, parentId, currentType) {
    return data.map((item, index) => {
        const itemId = `item-${level}-${index}`;
        const collapseId = `collapse-${level}-${index}`;
        const children = item.children || [];

        if (level === 2) {
            const tags = children.length > 0
                ? `<span class="plumbing-details small text-muted">${children.map(c => c.name).join(', ')}</span>`
                : '';
            const checked = selectedIds.includes(item.id.toString()) ? "checked" : "";

            // ✅ Get keywords2 from all children (flattened and joined)
            const childKeywords = children
            .flatMap(child => {
                if (Array.isArray(child.keywords2)) {
                    return child.keywords2;
                } else if (typeof child.keywords2 === "string") {
                    return child.keywords2.split(',').map(k => k.trim());
                }
                return [];
            })
            .filter(Boolean)
            .join(',');

            return `
                <div class="plumbing-options">
                    <div class="private-check-wrapper">
                        <label class="private-custom-checkbox d-flex align-items-center justify-content-between">
                            <span class="plumbing-item">
                                <span class="plumbing-category fw-semibold">${item.name}</span>
                                ${tags}
                            </span>
                            <input type="checkbox" class="private-checkbox-input item-checkbox" data-type="${currentType}" data-keywords="${childKeywords}" value="${item.id}" ${checked}>
                            <span class="private-checkbox-custom"></span>
                        </label>
                    </div>
                </div>
            `;
        }

        return `
            <div class="accordion-item">
                <h2 class="accordion-header" id="${itemId}">
                    <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse" data-bs-target="#${collapseId}">
                        ${item.name}
                    </button>
                </h2>
                <div id="${collapseId}" class="accordion-collapse collapse"
                        data-bs-parent="#${parentId}">
                    <div class="accordion-body">
                        ${buildAccordion(children, selectedIds, level + 1, collapseId, currentType)}
                    </div>
                </div>
            </div>
        `;
    }).join('');
}
window.buildAccordion = buildAccordion;

function attachCheckboxEvents(selectedIds, type) {
    setTimeout(() => {
        const keywordSet = new Set(); // ✅ Collect unique keywords here

        document.querySelectorAll(`.item-checkbox[data-type="${type}"]`).forEach(checkbox => {
            // If already checked, collect keywords initially
            if (checkbox.checked) {
                const kw = checkbox.getAttribute("data-keywords");
                if (kw) {
                    kw.split(',').map(k => k.trim()).filter(Boolean).forEach(k => keywordSet.add(k));
                }
            }

            checkbox.addEventListener('change', () => {
                const val = checkbox.value;
                let targetArray = type === "1" ? selectedServiceIds : selectedProductIds;

                if (checkbox.checked) {
                    if (!targetArray.includes(val)) targetArray.push(val);
                } else {
                    targetArray = targetArray.filter(id => id !== val);
                }

                if (type === "1") {
                    selectedServiceIds = targetArray;
                } else {
                    selectedProductIds = targetArray;
                }

                // Rebuild keywords on change
                updateSelectedKeywordsArray(type);

                renderSelectedTags(type);
            });
        });

        updateSelectedKeywordsArray(type); // ✅ Initial run
        renderSelectedTags(type);
    }, 100);
}
window.attachCheckboxEvents = attachCheckboxEvents;

function setupSearch(originalData, type) {
    const input = document.getElementById("search-services-products");
    input.addEventListener("input", function () {
        const keyword = this.value.trim().toLowerCase();
        const filteredData = filterTree(originalData, keyword);

        const container = document.getElementById("dynamic-service-accordion");
        container.innerHTML = `<div class="accordion" id="myAccordion2">${buildAccordion(filteredData, getSelectedIds(type), 0, "myAccordion2", type)}</div>`;

        attachCheckboxEvents(getSelectedIds(type), type);

        if (keyword) {
            setTimeout(() => {
                document.querySelectorAll(".accordion-collapse").forEach(c => c.classList.add("show"));
                document.querySelectorAll(".accordion-button").forEach(btn => btn.classList.remove("collapsed"));
            }, 50);
        }
    });
}
window.setupSearch = setupSearch;

function updateSelectedKeywordsArray(type) {
    const keywordSet = new Set();

    // Loop through all checked checkboxes of the current type
    document.querySelectorAll(`.item-checkbox[data-type="${type}"]:checked`).forEach(checkbox => {
        const kw = checkbox.getAttribute("data-keywords");
        if (kw) {
            kw.split(',')
            .map(k => k.trim())
            .filter(Boolean)
            .forEach(k => keywordSet.add(k));
        }
    });

    // Update the global array
    selectedKeywordsFromCheckboxes = Array.from(keywordSet);

    // ✅ Update the hidden input
    const hiddenInput = document.getElementById("hidden-all-keywords");
    if (hiddenInput) {
        hiddenInput.value = selectedKeywordsFromCheckboxes.join(",");
    }
}
window.updateSelectedKeywordsArray = updateSelectedKeywordsArray;

function filterTree(data, keyword) {
    if (!keyword) return data;

    const match = (item) => item.name.toLowerCase().includes(keyword);

    function deepFilter(items) {
        return items.map(item => {
            const children = deepFilter(item.children || []);
            if (match(item) || children.length) {
                return { ...item, children };
            }
            return null;
        }).filter(Boolean);
    }

    return deepFilter(data);
}
window.filterTree = filterTree;

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".open-modal-btn").forEach(btn => {
        btn.addEventListener("click", function () {
            const type = this.getAttribute("data-type");
            currentType = type;
            document.getElementById("modalType").value = type;
            document.getElementById("modalTypeTitle").innerHTML = (type == '1') ? 'Services' : 'Products';

            const hiddenField = type === "1"
                ? document.getElementById("selectedServiceIdsHidden")
                : document.getElementById("selectedProductIdsHidden");

            const hiddenValue = hiddenField.value;
            const selectedIds = hiddenValue ? hiddenValue.split(",") : [];

            fetchItems(type, selectedIds);
        });
    });
});

const roleSelect = document.getElementById('role-select');
const verifySection = document.getElementById('owner-verification-section');
const inputField = document.getElementById('verify-input');

// Show/hide section on role change
roleSelect.addEventListener('change', () => {
    if (roleSelect.value === 'Owner') {
        verifySection.style.display = 'block';
    } else {
        verifySection.style.display = 'none';
    }
});

// Change input placeholder based on selected radio
document.querySelectorAll('input[name="verify-method"]').forEach(radio => {
    radio.addEventListener('change', function () {
        inputField.placeholder = this.value === 'email' ? 'Enter company email' : 'Enter mobile number';
    });
});